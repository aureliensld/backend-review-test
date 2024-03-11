<?php

namespace App\Util;

class DateTimeRangeParser
{
    private const REGEX_PATTERN = '/^(?P<date>\d{4}-\b(?:(?:0?[1-9])|1[0-2])\b-\b(?:(?:0?[1-9])|1[0-9]|2[0-9]|3[0-1])\b)(?:(?>-)(?:(?P<hour>\b(?:[0-9]|1[0-9]|2[0-3])\b)|\{(?P<hour_interval>(?P<start_hour>\b(?:[0-9]|1[0-9]|2[0-3])\b)\.\.(?P<end_hour>\b(?:[1-9]|1[0-9]|2[0-3])\b))\}))?$/';

    /**
     * Parse date time range and returns list of \DateTimeInterface.
     *
     * @param string $dateTimeRange Allowed date formats are: Y-m-d | Y-m-d-G | Y-m-d-{G..G}. When only date is provided, hour range will be set to {0..23}
     *
     * @return \DateTimeInterface[] List of \DateTimeInterface matching the given range
     */
    public function parse(string $dateTimeRange): array
    {
        if (1 !== preg_match(self::REGEX_PATTERN, $dateTimeRange, $matches)) {
            throw new \InvalidArgumentException(sprintf('Invalid date time range: "%s".', $dateTimeRange));
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $matches['date']);
        if (false === $date) {
            throw new \InvalidArgumentException(sprintf('Invalid date time range: "%s".', $dateTimeRange));
        }

        if (isset($matches['hour']) && '' !== $matches['hour']) {
            return [$date->setTime((int) $matches['hour'], 0)];
        }

        if (!isset($matches['hour_interval'])) {
            $matches['start_hour'] = 0;
            $matches['end_hour'] = 23;
        }

        $range = [];
        $startHour = (int) $matches['start_hour'];
        $endHour = (int) $matches['end_hour'];
        if ($startHour > $endHour) {
            throw new \InvalidArgumentException(sprintf('Invalid date time range: "%s".', $dateTimeRange));
        }

        for ($i = $startHour; $i <= $endHour; ++$i) {
            $range[] = $date->setTime($i, 0);
        }

        return $range;
    }
}
