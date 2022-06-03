<?php

namespace BanSystem\utils;

class Utils {

    public static function convertStringToDateFormat(string $format, ?\DateTime $time = null, string $type = "add"): ?\DateTime {
        if ($format == "") return null;
        $result = ($time === null ? new \DateTime("now") : $time);
        $parts = str_split($format);
        $timeUnits = ["y" => "year", "m" => "month", "w" => "week", "d" => "day", "h" => "hour", "M" => "minute", "i" => "minute", "s" => "second"];
        $i = -1;
        $changes = false;

        foreach ($parts as $part) {
            ++$i;
            if (!isset($timeUnits[$part])) continue;
            $number = implode("", array_slice($parts, 0, $i));
            if (is_numeric($number)) {
                $result->modify(($type == "add" ? "+" : "-") . intval($number) . $timeUnits[$part]);
                array_splice($parts, 0, $i + 1);
                $i = -1;
                $changes = true;
            }
        }

        if ($changes == false) return null;

        return $result;
    }

    public static function diffString(\DateTime $target, \DateTime $object): string {
        $diff = $target->diff($object);
        $result = [];
        if ($diff->y > 0) $result[] = $diff->y . " Year(s)";
        if ($diff->m > 0) $result[] = $diff->m . " Month(s)";
        if ($diff->d > 0) $result[] = $diff->d . " Day(s)";
        if ($diff->h > 0) $result[] = $diff->h . " Hour(s)";
        if ($diff->i > 0) $result[] = $diff->i . " Minute(s)";
        if ($diff->s > 0) $result[] = $diff->s . " Second(s)";
        if (count($result) > 0) {
            return implode(", ", $result);
        } else {
            return $diff->s . " Second(s)";
        }
    }
}