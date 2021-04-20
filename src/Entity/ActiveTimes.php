<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

namespace Contao\ActivityBundle\Entity;

use Contao\ActivityBundle\Repository\ActiveTimesRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ActiveTimes.
 *
 * @ORM\Entity(repositoryClass=ActiveTimesRepository::class)
 * @ORM\Table(name="tl_active_times")
 */
class ActiveTimes
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"default":""})
     */
    private string $username;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     */
    private int $length;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     */
    private int $month;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"default":0})
     */
    private int $year;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength(int $length): void
    {
        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getMonth(): int
    {
        return $this->month;
    }

    /**
     * @param int $month
     */
    public function setMonth(int $month): void
    {
        $this->month = $month;
    }

    /**
     * @return int
     */
    public function getYear(): int
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear(int $year): void
    {
        $this->year = $year;
    }
}
