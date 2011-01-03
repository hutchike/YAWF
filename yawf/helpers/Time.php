<?
// Copyright (c) 2011 Guanoo, Inc.
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// as published by the Free Software Foundation; either version 3
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.

class Time
{
    /**
     * Return the number of seconds in a period e.g. "day", "week", "month"
     *
     * @return Integer the number of seconds in a period, or zero if confused
     */
    public static function secs_in($period)
    {
        if ($period == Symbol::YEAR) return 365*self::secs_in(Symbol::DAY);
        $secs = 1;
        switch ($period)
        {
            case Symbol::WEEK: $secs *= 7;
            case Symbol::DAY: $secs *= 24;
            case Symbol::HOUR: $secs *= 60;
            case Symbol::MINUTE: $secs *= 60;
        }
        return $secs > 1 ? $secs : 0;
    }
}

// End of Time.php
