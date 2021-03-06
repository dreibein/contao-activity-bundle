<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

namespace Contao\ActivityBundle\EventListener;

use Contao\ActivityBundle\Converter\ActiveTimesConverter;
use Contao\ActivityBundle\Model\LogModel;
use Contao\BackendUser;
use Contao\Model\Collection;
use Contao\User;
use Contao\UserModel;

class PostLoginListener
{
    private ActiveTimesConverter $activeTimesConverter;

    /**
     * PostLoginListener constructor.
     *
     * @param ActiveTimesConverter $activeTimesConverter
     */
    public function __construct(ActiveTimesConverter $activeTimesConverter)
    {
        $this->activeTimesConverter = $activeTimesConverter;
    }

    /**
     * Hook when a user has logged in.
     * Create the last times to the ActiveTimesModel.
     *
     * @param User $user
     */
    public function onPostLogin(User $user): void
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
        $this->activeTimesConverter->insertTimes($userTimesArray);
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
            // Find all log entries for each user
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
        // Set the initial values for the time-array
        $savedTimes = [
            'startTime' => 0,
            'endTime' => 0,
        ];

        // Is 1 if last entry was a login
        $wasLogin = true;
        $finalArray = [];

        // Array index originally for debugging purposes
        $arrayIndex = 0;
        $logCount = \count($logCollection);

        // Loop log entries
        /** @var LogModel $entry */
        foreach ($logCollection as $index => $entry) {
            // Skip some log actions
            if ('ACCESS' === $entry->action
                && (
                    false !== strpos($entry->text, 'logged out automatically')
                    || false !== strpos($entry->text, 'Invalid password submitted')
                    || false !== strpos($entry->text, 'locked')
                )) {
                continue;
            }

            if ('ACCESS' === $entry->action && false !== strpos($entry->text, 'logged in')) {
                // If last entry was not a login --> time can be saved
                if (false === $wasLogin) {
                    $this->addTimesToArray($finalArray, $savedTimes, $arrayIndex);
                }
                $savedTimes['startTime'] = $entry->tstamp;
                $wasLogin = true;
            } elseif ('ACCESS' !== $entry->action || ('ACCESS' === $entry->action && false !== strpos($entry->text, 'logged out'))) {
                // Kicks in if first log entry was not a login
                if (0 === $arrayIndex) {
                    $savedTimes['startTime'] = $entry->tstamp;
                }

                if (($index + 1) === $logCount) {
                    $this->addTimesToArray($finalArray, $savedTimes, $arrayIndex);
                }

                $savedTimes['endTime'] = $entry->tstamp;
                $wasLogin = false;
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
