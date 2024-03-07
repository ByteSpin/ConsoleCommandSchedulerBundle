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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Entity;

use ByteSpin\ConsoleCommandSchedulerBundle\Repository\SchedulerRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SchedulerRepository::class)]
class Scheduler
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $command = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $arguments = null;

    #[ORM\Column(length: 255)]
    private ?string $execution_type = null;

    #[ORM\Column(length: 255)]
    private ?string $frequency = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $execution_from_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $execution_from_time = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $execution_until_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $execution_until_time = null;

    #[ORM\Column(nullable: true)]
    private ?bool $disabled = false;

    #[ORM\Column(nullable: true)]
    private ?bool $noDbLog = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $log_file = null;

    #[ORM\Column(nullable: true)]
    private ?bool $send_email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $job_title = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): static
    {
        $this->command = $command;

        return $this;
    }

    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    public function setArguments(string $arguments): static
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function getExecutionType(): ?string
    {
        return $this->execution_type;
    }

    public function setExecutionType(string $execution_type): static
    {
        $this->execution_type = $execution_type;

        return $this;
    }

    public function getFrequency(): ?string
    {
        return $this->frequency;
    }

    public function setFrequency(?string $frequency): static
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getExecutionFromDate(): ?DateTimeInterface
    {
        if (empty($this->execution_from_date)) {
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $this->execution_from_date);
        return $date ?: null;
    }

    public function setExecutionFromDate($execution_from_date): static
    {
        if ($execution_from_date instanceof DateTimeInterface) {
            $this->execution_from_date = $execution_from_date->format('Y-m-d');
        } else {
            $this->execution_from_date = $execution_from_date;
        }
        return $this;
    }

    public function getExecutionFromTime(): ?DateTimeInterface
    {
        if (empty($this->execution_from_time)) {
            return null;
        }

        $time = DateTime::createFromFormat('H:i', $this->execution_from_time);
        return $time ?: null;
    }

    public function setExecutionFromTime($execution_from_time): static
    {
        if ($execution_from_time instanceof DateTimeInterface) {
            $this->execution_from_time = $execution_from_time->format('H:i');
        } else {
            $this->execution_from_time = $execution_from_time;
        }

        return $this;
    }

    public function getExecutionUntilDate(): ?DateTimeInterface
    {
        if (empty($this->execution_until_date)) {
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $this->execution_until_date);
        return $date ?: null;
    }

    public function setExecutionUntilDate($execution_until_date): static
    {
        if ($execution_until_date instanceof DateTimeInterface) {
            $this->execution_until_date = $execution_until_date->format('Y-m-d');
        } else {
            $this->execution_until_date = $execution_until_date;
        }
        return $this;
    }

    public function getExecutionUntilTime(): ?DateTimeInterface
    {
        if (empty($this->execution_until_time)) {
            return null;
        }

        $time = DateTime::createFromFormat('H:i', $this->execution_until_time);
        return $time ?: null;
    }

    public function setExecutionUntilTime($execution_until_time): static
    {
        if ($execution_until_time instanceof DateTimeInterface) {
            $this->execution_until_time = $execution_until_time->format('H:i');
        } else {
            $this->execution_until_time = $execution_until_time;
        }
        return $this;
    }

    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function getDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): static
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function getLogFile(): ?string
    {
        return $this->log_file;
    }

    public function setLogFile(?string $log_file): void
    {
        $this->log_file = $log_file;
    }
    public function getNoDbLog(): ?bool
    {
        return $this->noDbLog;
    }
    public function setNoDbLog(?bool $noDbLog): void
    {
        $this->noDbLog = $noDbLog;
    }
    public function getSendEmail(): ?bool
    {
        return $this->send_email;
    }
    public function setSendEmail(?bool $send_email): void
    {
        $this->send_email = $send_email;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
    public function getJobTitle(): ?bool
    {
        return $this->job_title;
    }
    public function setJobTitle(?bool $job_title): void
    {
        $this->job_title = $job_title;
    }
}
