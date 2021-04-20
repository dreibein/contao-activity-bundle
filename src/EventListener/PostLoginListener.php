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
    /**
     * Hook when a user has logged in.
     * Create the last times to the ActiveTimesModel.
     *
     * @param User $user
     */
    public function __invoke(User $user): void
    {
        // check if user is a backend user
        if (!$user instanceof BackendUser) {
            return;
        }

        // find all active users
        $allUsers = UserModel::findByDisable(0);
        if (null === $allUsers) {
            return;
        }

        $userTimesArray = $this->getTimesPerUser($allUsers);
        ActiveTimesModel::insertTimes($userTimesArray);
    }

    /**
     * Get all logs for each user.
     *
     * @param Collection $users
     *
     * @return array
     */
    private function getTimesPerUser(Collection $users): array
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

    /**
     * Evaluates the active times per user.
     *
     * @param Collection $logCollection
     *
     * @return array
     */
    private function evaluateTimes(Collection $logCollection): array
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
        $logCount = \count($logCollection);
        // Loop logentries
        /** @var LogModel $entry */
        foreach ($logCollection as $index => $entry) {
            // Skip auto logout; causes bloated active times

            if ('ACCESS' === $entry->action && false !== strpos($entry->text, 'logged out automatically') || false !== strpos($entry->text, 'Invalid password submitted') || false !== strpos($entry->text, 'locked')) {
                continue;
            }

            if ('ACCESS' === $entry->action && false !== strpos($entry->text, 'logged in')) {
                // If last entry was not a login --> time can be saved
                if (0 === $wasLogin) {
                    $this->addTimesToArray($finalArray, $savedTimes, $arrayIndex);
                }
                $savedTimes['startTime'] = $entry->tstamp;
                $wasLogin = 1;
            } elseif ('ACCESS' !== $entry->action || ('ACCESS' === $entry->action && false !== strpos($entry->text, 'logged out'))) {
                // Kicks in if first log entry was not a login
                if (0 === $arrayIndex) {
                    $savedTimes['startTime'] = $entry->tstamp;
                }

                if (($index + 1) === $logCount) {
                    $this->addTimesToArray($finalArray, $savedTimes, $arrayIndex);
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

    private function addTimesToArray(array &$finalArray, array $savedTimes, $arrayIndex): void
    {
        $finalArray[$arrayIndex]['length'] = $savedTimes['endTime'] - $savedTimes['startTime'];
        $finalArray[$arrayIndex]['month'] = date('n', (int) $savedTimes['endTime']);
        $finalArray[$arrayIndex]['year'] = date('Y', (int) $savedTimes['endTime']);
    }
}
