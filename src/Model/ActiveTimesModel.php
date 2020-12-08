<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

namespace Contao\ActivityBundle\Model;

use Contao\Model;

/**
 * @property string $username
 * @property int    $length
 * @property string $month
 * @property int    $year
 */
class ActiveTimesModel extends Model
{
    protected static $strTable = 'tl_active_times';

    // Inserts the generated active times into database
    public static function insertTimes($userTimesArray): void
    {
        if ($userTimesArray) {
            foreach ($userTimesArray as $user => $times) {
                foreach ($times as $time) {
                    if ($time['length'] <= 0) {
                        continue;
                    }
                    $activeTimesModel = new self();
                    $activeTimesModel->username = $user;
                    $activeTimesModel->length = $time['length'];
                    $activeTimesModel->month = $time['month'];
                    $activeTimesModel->year = $time['year'];
                    $activeTimesModel->save();
                }
            }
        }
    }

    // Get all active times
    public static function getAllEntries()
    {
        $currentYear = date('Y');
        $lastYear = $currentYear - 1;
        $currentMonth = date('n');

        return self::findBy(['year = ? OR (year = ? AND month > ?)'], [$currentYear, $lastYear, $currentMonth]);
    }
}
