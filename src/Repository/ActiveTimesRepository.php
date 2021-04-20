<?php

declare(strict_types=1);

/*
 * This file is part of Dreibein Activity Bundle.
 *
 * (c) Werbeagentur Dreibein GmbH
 */

namespace Contao\ActivityBundle\Repository;

use Contao\ActivityBundle\Entity\ActiveTimes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Safe\DateTimeImmutable;
use Safe\Exceptions\DatetimeException;

/**
 * @method object|null find($id, $lockMode = null, $lockVersion = null)
 * @method object|null findOneBy(array $criteria, array $orderBy = null)
 * @method object[]    findAll()
 * @method object[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActiveTimesRepository extends ServiceEntityRepository
{
    /**
     * ActiveTimesRepository constructor.
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActiveTimes::class);
    }

    public function findById(int $id): ?ActiveTimes
    {
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @throws DatetimeException
     *
     * @return ActiveTimes[]
     */
    public function findAllForCurrentYear(): array
    {
        $date = new DateTimeImmutable();
        $year = (int) $date->format('Y');
        $month = $date->format('n');
        $lastYear = $year - 1;

        return $this->createQueryBuilder('at')
            ->andWhere('at.year = :year')
            ->orWhere('at.year = :last AND :month > :month')
            ->setParameter('year', $year)
            ->setParameter('last', $lastYear)
            ->setParameter('month', $month)
            ->getQuery()
            ->getResult()
        ;
    }
}
