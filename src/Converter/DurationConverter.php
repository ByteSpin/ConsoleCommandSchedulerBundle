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

namespace ByteSpin\ConsoleCommandSchedulerBundle\Converter;

class DurationConverter
{
    public function convert(int $seconds): string
    {
        $s = ($seconds < 1) ? 1 : $seconds;
        $h = intdiv($s, 3600);
        $m = intdiv($s % 3600, 60);
        $rs = $s % 60;

        $result = [];
        if ($h > 0) {
            $result[] = "$h h";
        }
        if ($m > 0) {
            $result[] = "$m min.";
        }
        if ($rs > 0 || count($result) == 0) {
            $result[] = "$rs sec.";
        }

        return implode(' ', $result);
    }
}
