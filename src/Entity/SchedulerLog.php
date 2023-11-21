<?php

/**
 * Copyright (c) 2023 Greg LAMY <greg@bytespin.net>
 *
 * This project is hosted on GitHub at:
 *  https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git
 *
 * This bundle was originally developed as part of an ETL project.
 *
 * ByteSpin/ConsoleCommandSchedulerBundle is a Symfony 6.3 simple bundle that allows you to schedule console commands easily:
 * - Use the latest messenger/scheduler Symfony 6.3+ components,
 * - Log all console commands data (last execution time, duration, return code) in database and log file,
 * - An admin interface is available with the help of EasyCorp/EasyAdmin bundle
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
