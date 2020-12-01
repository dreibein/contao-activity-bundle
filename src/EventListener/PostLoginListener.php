<?php


namespace Contao\ActivityBundle\EventListener;

use Contao\ActivityBundle\Model\ActiveTimesModel;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Database;
use Contao\User;

/**
 * @Hook("postLogin")
 */
class PostLoginListener
{
    public function __invoke(User $user): void
    {
        if ($user instanceof \Contao\BackendUser) {
            $objDatabase = Database::getInstance();

            $allUsers = $this->getActiveUsers($objDatabase);
            $userTimesArray = $this->getTimesPerUser($allUsers, $objDatabase);
            ActiveTimesModel::insertTimes($userTimesArray);
        }
    }

    // GET ALL USERES WHICH ARE NOT DISABLED
    public function getActiveUsers ($objDatabase) {
        $sqlGetActiveUsers = "SELECT username,currentLogin FROM tl_user WHERE disable != 1";
        $objResult = $objDatabase->prepare($sqlGetActiveUsers)->execute();
        return $objResult->fetchAllAssoc();
    }

    // GET ALL LOGS FOR EACH USER
    public function getTimesPerUser (array $users, Object $objDatabase) {
        foreach ($users as $user) {
            $sqlGetUncheckedLogEntries = "SELECT username,action,tstamp,text FROM tl_log WHERE inStatistic = 0 AND username = ? ORDER BY tstamp ASC";
            $objResult = $objDatabase->prepare($sqlGetUncheckedLogEntries)->execute([$user['username']]);
            $objResult = $objResult->fetchAllAssoc();
            $timesArray = $this->evaluateTimes($objResult);

            if ($timesArray) {
                $activeTimes[$user['username']] = $timesArray;
            }
        }
        $sqlInStatistic = "UPDATE tl_log SET inStatistic = 1";
        $objDatabase->prepare($sqlInStatistic)->execute();

        return $activeTimes;
    }

    // EVALUATES THE ACTIVE TIMES PER USER
    public function evaluateTimes ($logArray) {
        // SETTING A TIME ARRAY INITIALLY
        $savedTimes = [
            'startTime' => 0,
            'endTime' => 0,
        ];
        // IS 1 IF LAST ENTRY WAS A LOGIN
        $wasLogin = 1;

        // VARIABLE FOR FINAL ACTIVE TIMES
        $finalArray = [];

        // ARRAY INDEX ORIGINALLY FOR DEBUGGING PURPOSES
        $arrayIndex = 0;

        // LOOP LOGENTRIES
        foreach ($logArray as $entry) {

            // SKIP AUTO LOG OUT; CAUSES BLOATED ACTIVE TIMES
            if ($entry['action'] === 'ACCESS' && strpos($entry['text'],'logged out automatically')) {
                continue;
            }

            if ($entry['action'] === 'ACCESS' && strpos($entry['text'],'logged in')) {

                // IF LAST ENTRY WAS NOT A LOGIN --> TIME CAN BE SAVED
                if ($wasLogin === 0) {
                    $finalArray[$arrayIndex]['length'] = $savedTimes['endTime'] - $savedTimes['startTime'];
                    $finalArray[$arrayIndex]['month'] = date('n', $savedTimes['endTime']);
                    $finalArray[$arrayIndex]['year'] = date('Y', $savedTimes['endTime']);
                }
                $savedTimes['startTime'] = $entry['tstamp'];
                $wasLogin = 1;
            } else {
                // KICKS IN IF FIRST LOG ENTRY WAS NOT A LOGIN (WON'T BE MOST OF THE TIME)
                if ($arrayIndex === 0) {
                    $savedTimes['startTime'] = $entry['tstamp'];
                }

                $savedTimes['endTime'] = $entry['tstamp'];
                $wasLogin = 0;
            }

            $arrayIndex++;
        }

        return $finalArray;
    }
}
