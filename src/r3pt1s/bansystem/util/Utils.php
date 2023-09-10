<?php

namespace r3pt1s\bansystem\util;

class Utils {

    public static function convertStringToDateFormat(string $format, ?\DateTime $time = null, string $type = "add"): ?\DateTime {
        if ($format == "") return null;
        $result = ($time === null ? new \DateTime("now") : $time);
        $parts = str_split($format);
        $timeUnits = ["y" => "year", "M" => "month", "w" => "week", "d" => "day", "h" => "hour", "m" => "minute", "i" => "minute", "s" => "second"];
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

        if (!$changes) return null;

        return $result;
    }

    public static function diffString(\DateTime $target, \DateTime $object): string {
        $diff = $target->diff($object);
        $result = [];
        if ($diff->y > 0) $result[] = $diff->y . " year" . ($diff->y == 1 ? "" : "s");

        if ($diff->m > 0) $result[] = $diff->m . " month" . ($diff->m == 1 ? "" : "s");
        else if ($diff->y > 0) $result[] = "0 months";

        if ($diff->d > 0) $result[] = $diff->d . " day" . ($diff->d == 1 ? "" : "s");
        else if ($diff->y > 0) $result[] = "0 days";
        else if ($diff->m > 0) $result[] = "0 days";

        if ($diff->h > 0) $result[] = $diff->h . " hour" . ($diff->h == 1 ? "" : "s");
        else if ($diff->y > 0) $result[] = "0 hours";
        else if ($diff->m > 0) $result[] = "0 hours";
        else if ($diff->d > 0) $result[] = "0 hours";

        if ($diff->i > 0) $result[] = $diff->i . " minute" . ($diff->i == 1 ? "" : "s");
        else if ($diff->y > 0) $result[] = "0 minutes";
        else if ($diff->m > 0) $result[] = "0 minutes";
        else if ($diff->d > 0) $result[] = "0 minutes";
        else if ($diff->h > 0) $result[] = "0 minutes";

        if ($diff->s > 0) $result[] = $diff->s . " second" . ($diff->s == 1 ? "" : "s");
        else if ($diff->y > 0) $result[] = "0 seconds";
        else if ($diff->m > 0) $result[] = "0 seconds";
        else if ($diff->d > 0) $result[] = "0 seconds";
        else if ($diff->h > 0) $result[] = "0 seconds";
        else if ($diff->i > 0) $result[] = "0 seconds";

        if (count($result) > 0) {
            return implode(", ", $result);
        } else {
            return $diff->s . " second" . ($diff->s == 1 ? "" : "s");
        }
    }
}