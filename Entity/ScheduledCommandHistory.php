<?php

namespace Flashmer\CommandSchedulerBundle\Entity;

use App\Repository\CommandSchedulerHistoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * https://www.doctrine-project.org/2021/05/24/orm2.9.html
 * @author  Julien Guyon <julienguyon@hotmail.com>
 */
#[ORM\Entity(repositoryClass: ScheduledCommandHistoryRepository::class)]
#[ORM\Table(name: "scheduled_command_history")]
class ScheduledCommandHistory
{
    #[ORM\Id, ORM\Column(type: Types::INTEGER), ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;
    #[ORM\Version]
    #[ORM\Column(type: Types::INTEGER)]
    private int $version = 0;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 150, unique: true, nullable: false)]
    private string $name;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 200, nullable: false)]
    private string $command;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $arguments = null;

    #[Assert\Type(DateTime::class)]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $date = null;

    #[Assert\Type(DateTime::class)]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $duration = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $returnCode = null;

    /**
     * Init new ScheduledCommand.
     */
    public function __construct()
    {
    }

    public function __toString(): string
    {
        return $this->getName();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): ScheduledCommandHistory
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): ScheduledCommandHistory
    {
        $this->name = $name;

        return $this;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): ScheduledCommandHistory
    {
        $this->command = $command;

        return $this;
    }

    public function getArguments(): ?string
    {
        return $this->arguments;
    }

    public function setArguments(?string $arguments): ScheduledCommandHistory
    {
        $this->arguments = $arguments;

        return $this;
    }



    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): ScheduledCommandHistory
    {
        $this->date = $date;

        return $this;
    }


    public function getReturnCode(): ?int
    {
        return $this->returnCode;
    }

    public function setReturnCode(?int $returnCode): ScheduledCommandHistory
    {
        $this->returnCode = $returnCode;

        return $this;
    }

    public function getDuration(): ?string
    {
        return $this->duration;
    }

    public function setDuration(string $duration): ScheduledCommandHistory
    {
        $this->duration = $duration;

        return $this;
    }
}
