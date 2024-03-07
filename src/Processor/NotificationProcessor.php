<?php

/**
 * This file is part of the ByteSpin/ConsoleCommandSchedulerBundle project.
 * The project is hosted on GitHub at:
 *  https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git
 *
 * Copyright (c) Greg LAMY <greg@bytespin.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ByteSpin\ConsoleCommandSchedulerBundle\Processor;

use ByteSpin\ConsoleCommandSchedulerBundle\Event\ScheduledConsoleCommandGenericEvent;
use ByteSpin\ConsoleCommandSchedulerBundle\Job\JobOutputCollector;
use ByteSpin\ConsoleCommandSchedulerBundle\Repository\SchedulerRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

readonly class NotificationProcessor
{
    public function __construct(
        private MailerInterface $mailer,
        private JobOutputCollector $outputCollector,
        #[Autowire(env:'BYTESPIN_FROM_EMAIL')]
        private string $mailFrom,
        private SchedulerRepository $schedulerRepository,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendNotification(ScheduledConsoleCommandGenericEvent $consoleCommand): void
    {
        $outputs = $this->outputCollector->getOutputs($consoleCommand->id);
        $jobConfigData = $this->schedulerRepository->find($consoleCommand->id);

        if ($jobConfigData->getSendEmail() && !empty($jobConfigData->getEmail())) {
            $email = (new TemplatedEmail())
                ->from($this->mailFrom)
                ->to($jobConfigData->getEmail())
                ->subject(
                    '[' .
                    match ($consoleCommand->returnCode) {
                        0 => 'SUCCESS',
                        default => 'FAILURE'
                    } .
                    '] ByteSpin Scheduled Console Command:' .
                    match ($jobConfigData->getJobTitle()) {
                        null => $consoleCommand->command,
                        default => $jobConfigData->getJobTitle()
                    }
                )
                ->htmlTemplate('@ByteSpinConsoleCommandSchedulerBundle/email/notification.html.twig')
                ->context([
                    'dateTime' => $consoleCommand->start->format('d/m/Y'),
                    'commandName' => $consoleCommand->command,
                    'commandArguments' => $consoleCommand->commandArguments,
                    'duration' => $consoleCommand->duration,
                    'returnCode' => $consoleCommand->returnCode,
                    'outputs' => $outputs,
                ])
            ;

            $email->getHeaders()
                ->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply')
            ;

            $this->mailer->send($email);
        }
    }
}
