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

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ByteSpin\ConsoleCommandSchedulerBundle\Repository\SchedulerLogRepository;

#[ORM\Entity(repositoryClass: SchedulerLogRepository::class)]
class SchedulerLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $command = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $arguments = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $return_code = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $duration = null;


    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id):void
    {
        $this->id = $id;
    }
    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }
    public function setDate(?DateTimeInterface $date):void
    {
        $this->date = $date;
    }
    public function getCommand(): ?string
    {
        return $this->command;
    }
    public function setCommand(?string $command):void
    {
        $this->command = $command;
    }
    public function getArguments(): ?string
    {
        return $this->arguments;
    }
    public function setArguments(?string $arguments):void
    {
        $this->arguments = $arguments;
    }
    public function getReturnCode(): ?string
    {
        return $this->return_code;
    }
    public function setReturnCode(?string $return_code):void
    {
        $this->return_code = $return_code;
    }
    public function getDuration(): ?string
    {
        return $this->duration;
    }
    public function setDuration(?string $duration):void
    {
        $this->duration = $duration;
    }
}
