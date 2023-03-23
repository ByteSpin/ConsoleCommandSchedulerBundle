<?php

namespace Flashmer\CommandSchedulerBundle\Controller;

use Flashmer\CommandSchedulerBundle\Entity\ScheduledCommand;
use Flashmer\CommandSchedulerBundle\Form\Type\ScheduledCommandType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DetailController.
 *
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
class DetailController extends AbstractBaseController
{
    /**
     * Handle display of new/existing ScheduledCommand object.
     */
    public function edit(Request $request, $id = null): Response
    {
        $scheduledCommand = $id ? $this->getDoctrineManager()->getRepository(ScheduledCommand::class)->find($id) : null;
        if (!$scheduledCommand) {
            $scheduledCommand = new ScheduledCommand();
        }

        $form = $this->createForm(ScheduledCommandType::class, $scheduledCommand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // check if we have an xml-read error for commands
            if ('error' === $scheduledCommand->getCommand()) {
                $this->addFlash('error', 'ERROR: please check php bin/console list --format=xml');

                return $this->redirectToRoute('flashmer_command_scheduler_list');
            }

            $em = $this->getDoctrineManager();
            $em->persist($scheduledCommand);
            $em->flush();

            // Add a flash message and do a redirect to the list
            $this->addFlash('success', $this->translator->trans('flash.success', [], 'FlashmerCommandScheduler'));

            return $this->redirectToRoute('flashmer_command_scheduler_list');
        }

        return $this->render(
            '@FlashmerCommandScheduler/Detail/index.html.twig',
            ['scheduledCommandForm' => $form->createView()]
        );
    }
}
