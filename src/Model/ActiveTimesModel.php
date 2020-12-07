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
 * @property int    $id
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
        $entryArray[$currentYear] = [];
        $entryArray[$lastYear] = [];

        $currentYearArray = self::findBy('year', $currentYear);
        if (null !== $currentYearArray) {
            $currentYearArray = $currentYearArray->fetchAll();
        }

        $lastYearArray = static::findBy(['year = ?', 'month > ?'], [$lastYear, $currentMonth]);

        if (null !== $lastYearArray) {
            $lastYearArray = $lastYearArray->fetchAll();
        }

        if (null !== $lastYearArray && null !== $currentYearArray) {
            return array_merge($currentYearArray, $lastYearArray);
        }
        if (null === $lastYearArray && null !== $currentYearArray) {
            return $currentYearArray;
        }
        if (null !== $lastYearArray && null === $currentYearArray) {
            return $lastYearArray;
        }
    }
}
