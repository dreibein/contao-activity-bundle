<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

namespace Contao\ActivityBundle\Converter;

use Contao\ActivityBundle\Entity\ActiveTimes;
use Doctrine\ORM\EntityManagerInterface;

class ActiveTimesConverter
{
    private EntityManagerInterface $entityManager;

    /**
     * ActiveTimesConverter constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function insertTimes(?array $userTimes): void
    {
        if (null === $userTimes) {
            return;
        }

        // Loop over the array and create new database entries
        foreach ($userTimes as $user => $times) {
            foreach ($times as $time) {
                if ($time['length'] <= 0) {
                    continue;
                }

                $activeTimes = new ActiveTimes();
                $activeTimes->setUsername((string) $user);
                $activeTimes->setLength((int) $time['length']);
                $activeTimes->setMonth((int) $time['month']);
                $activeTimes->setYear((int) $time['year']);

                $this->entityManager->persist($activeTimes);
            }
        }

        // Write the new entries to the database
        $this->entityManager->flush();
    }
}
