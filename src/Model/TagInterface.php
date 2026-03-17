<?php

/**
 * This file is part of the ByteSpin/ConsoleCommandSchedulerBundle project.
 * The project is hosted on GitHub at:
 *  https://github.com/ByteSpin/ConsoleCommandSchedulerBundle.git.
 *
 * Copyright (c) Greg LAMY <greg@bytespin.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ByteSpin\ConsoleCommandSchedulerBundle\Model;

/**
 * Interface that Tag entities must implement to be used with the scheduler.
 */
interface TagInterface
{
    public function getId(): ?int;

    public function getName(): ?string;

    public function getLabel(): ?string;

    public function getColor(): ?string;

    public function __toString(): string;
}
