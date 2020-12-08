<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

namespace Contao\ActivityBundle\EventListener;

use Contao\ActivityBundle\Model\ActiveTimesModel;
use Contao\ActivityBundle\Model\LogModel;
use Contao\BackendUser;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Model\Collection;
use Contao\User;
use Contao\UserModel;

/**
 * @Hook("postLogin")
 */
class PostLoginListener
{
    public function __invoke(User $user): void
    {
        if (!$user instanceof BackendUser) {
            return;
        }
        $allUsers = UserModel::findByDisable(0);
        if (null === $allUsers) {
            return;
        }
        $userTimesArray = $this->getTimesPerUser($allUsers);
        ActiveTimesModel::insertTimes($userTimesArray);
    }

    // Get all logs for each user
    public function getTimesPerUser(Collection $users)
    {
        $activeTimes = [];

        /** @var UserModel $user */
        foreach ($users as $user) {
            $logCollection = LogModel::findBy(['inStatistic = ?', 'username = ?'], [0, $user->username], ['order' => 'tstamp ASC']);

            if (null === $logCollection) {
                continue;
            }

            $timesArray = $this->evaluateTimes($logCollection);

            if ($timesArray) {
                $activeTimes[$user->username] = $timesArray;
            }
        }

        return $activeTimes;
    }

    // Evaluates the active times per user
    public function evaluateTimes(Collection $logCollection)
    {
        // Setting a time array initially
        $savedTimes = [
            'startTime' => 0,
            'endTime' => 0,
        ];

        // Is 1 if last entry was a login
        $wasLogin = 1;

        // Variable for final active times
        $finalArray = [];

        // Array index originally for debugging purposes
        $arrayIndex = 0;

        // Loop logentries
        /** @var LogModel $entry */
        foreach ($logCollection as $entry) {
            // Skip auto logout; causes bloated active times
            if ('ACCESS' === $entry->action && false !== strpos($entry->text, 'logged out automatically')) {
                continue;
            }

            if ('ACCESS' === $entry->action && false !== strpos($entry->text, 'logged in')) {
                // If last entry was not a login --> time can be saved
                if (0 === $wasLogin) {
                    $finalArray[$arrayIndex]['length'] = $savedTimes['endTime'] - $savedTimes['startTime'];
                    $finalArray[$arrayIndex]['month'] = date('n', (int) $savedTimes['endTime']);
                    $finalArray[$arrayIndex]['year'] = date('Y', (int) $savedTimes['endTime']);
                }
                $savedTimes['startTime'] = $entry->tstamp;
                $wasLogin = 1;
            } else {
                // Kicks in if first log entry was not a login
                if (0 === $arrayIndex) {
                    $savedTimes['startTime'] = $entry->tstamp;
                }

                $savedTimes['endTime'] = $entry->tstamp;
                $wasLogin = 0;
            }

            ++$arrayIndex;
            $entry->inStatistic = 1;
            $entry->save();
        }

        return $finalArray;
    }
}
