<?php

namespace Contao\ActivityBundle\Model;

use Contao\Database;
use Contao\Model;

/**
 * @property integer $id
 * @property string  $username
 * @property integer $length
 * @property string  $month
 */
class ActiveTimesModel extends Model
{
    protected static $strTable = 'tl_active_times';

    // INSERTS THE GENERATED ACTIVE TIMES INTO DATABASE
    public static function insertTimes ($userTimesArray) {
        if ($userTimesArray) {
            foreach ($userTimesArray as $user => $times) {
                foreach ($times as $time) {
                    if ($time['length'] <= 0) {
                        continue;
                    }
                    $objDatabase = Database::getInstance();
                    $sql = "INSERT INTO tl_active_times (username, length, month, year) VALUES (?, ?, ?, ?)";
                    $objResult = $objDatabase->prepare($sql)->execute([$user, $time['length'], (int)$time['month'], $time['year']]);

                }
            }
        }
    }

    // GET ALL ACTIVE TIMES SORTED BY USERNAME ASCENDING
    public static function getAllEntries () {
        $objDatabase = Database::getInstance();
        $currentYear = date('Y');
        $lastYear = $currentYear - 1;
        $currentMonth = date('n');
        $entryArray[$currentYear] = [];
        $entryArray[$lastYear] = [];

        $sqlCurrentYear = "SELECT * FROM tl_active_times WHERE year = ? ORDER BY username ASC";
        $resultCurrentYear = $objDatabase->prepare($sqlCurrentYear)->execute([$currentYear]);

        $sqlLastYear = "SELECT * FROM tl_active_times WHERE year = ? AND month > ? ORDER BY username ASC";
        $resultLastYear = $objDatabase->prepare($sqlLastYear)->execute([$lastYear,$currentMonth]);

        $currentYearArray = $resultCurrentYear->fetchAllAssoc();
        $lastYearArray = $resultLastYear->fetchAllAssoc();
        $entryArray = array_merge($currentYearArray,$lastYearArray);

        return $entryArray;
    }
}
