<?php

function validateDate(string $date): bool {
    $dateBlocks = explode("-", $date);

    if (count($dateBlocks) !== 3) {
        return false;
    }

    [$day, $month, $year] = $dateBlocks;

    if (!ctype_digit($day) || !ctype_digit($month) || !ctype_digit($year)) {
        return false;
    }

    $day = (int)$day;
    $month = (int)$month;
    $year = (int)$year;

    if ($year > (int)date('Y')) {
        return false;
    }

    if (!checkdate($month, $day, $year)) {
        return false;
    }

    return true;
}
