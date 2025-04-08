<?php

namespace App\Repository;

use App\Entity\CarCount;
use App\Entity\Tatbestand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CarCount>
 */
class CarCountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CarCount::class);
    }

    public function findAllStreetData(): array
    {
        return $this->createQueryBuilder('cc')
            ->select('cc.id', 'cc.latitude', 'cc.longitude', 'cc.street_name')
            ->getQuery()
            ->getArrayResult(); // Rückgabe als Array
    }

    public function findAllWithFilters(
        ?string $streetName = null,
        ?\DateTimeInterface $dateStart = null,
        ?\DateTimeInterface $dateEnd = null,
        ?Tatbestand $tatbestand = null
    ): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select(
                'c.street_name AS streetName',
                'c.street_details AS streetDetails',
                'c.latitude AS latitude',
                'c.longitude AS longitude',
                'c.createAt AS createAt',
                't.count AS count',
                'tb.text AS tatbestandText'
            )
            ->join('c.tatbestandCounts', 't') // Verknüpfung mit TatbestandCount
            ->join('t.tatbestand', 'tb')     // Verknüpfung mit Tatbestand für den Namen
            ->orderBy('c.street_name', 'ASC')
            ->addOrderBy('c.latitude', 'ASC')
            ->addOrderBy('c.longitude', 'ASC')
            ->addOrderBy('c.createAt', 'ASC');

                if ($streetName) {
                    $qb->andWhere('c.street_name = :streetName')
                       ->setParameter('streetName', $streetName);
                }

                if ($dateStart) {
                    $qb->andWhere('c.createAt >= :dateStart')
                       ->setParameter('dateStart', $dateStart->setTime(0, 0, 0));
                }

                if ($dateEnd) {
                    $qb->andWhere('c.createAt <= :dateEnd')
                       ->setParameter('dateEnd', $dateEnd->setTime(23, 59, 59));
                }

                if ($tatbestand) {
                    $qb->andWhere('t.tatbestand = :tatbestand')
                       ->setParameter('tatbestand', $tatbestand);
                }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return CarCount[] Returns an array of CarCount objects
     */
    public function findAllWithFiltersCopy(
        ?string $streetName = null,
        ?\DateTimeInterface $dateStart = null,
        ?\DateTimeInterface $dateEnd = null,
        ?Tatbestand $tatbestand = null
    ): array
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.tatbestandCounts', 'tc')
            ->leftJoin('tc.tatbestand', 't')
            ->addSelect('tc', 't');

        if ($streetName) {
            $query->andWhere('c.street_name = :streetName')
               ->setParameter('streetName', $streetName);
        }

        if ($dateStart) {
            $query->andWhere('c.createAt >= :dateStart')
               ->setParameter('dateStart', $dateStart->setTime(0, 0, 0));
        }

        if ($dateEnd) {
            $query->andWhere('c.createAt <= :dateEnd')
               ->setParameter('dateEnd', $dateEnd->setTime(23, 59, 59));
        }

        if ($tatbestand) {
            $query->andWhere('tc.tatbestand = :tatbestand')
               ->setParameter('tatbestand', $tatbestand);
        }

        return $query->getQuery()->getResult();
    }

    public function findFilteredCarCounts(?string $streetName, ?\DateTimeInterface $date, ?Tatbestand $tatbestand): array
    {
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.tatbestandCounts', 't')
            ->addSelect('t');

        // Filter für Straßenname
        if ($streetName) {
            $qb->andWhere('c.street_name = :streetName')
               ->setParameter('streetName', $streetName);
        }

        // Filter für Datum
        if ($date) {
            $qb->andWhere('c.createAt >= :dateStart')
               ->andWhere('c.createAt < :dateEnd')
               ->setParameter('dateStart', $date->setTime(0, 0, 0))
               ->setParameter('dateEnd', (clone $date)->setTime(23, 59, 59));
        }

        // Filter für Tatbestand
        if ($tatbestand) {
            $qb->andWhere('t.tatbestand = :tatbestand')
               ->setParameter('tatbestand', $tatbestand);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function findDistinctStreets(): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('DISTINCT c.street_name')
            ->orderBy('c.street_name', 'ASC');

        $result =  $qb->getQuery()->getResult();

        return array_column($result, 'street_name', 'street_name');
    }

    public function getStreetNameCount(string $streetName): int
    {
        $qb = $this->createQueryBuilder('cc')
        ->select('COUNT(cc.id)')
        ->where('cc.street_name = :streetName')
        ->setParameter('streetName', $streetName);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    public function findWithTatbestandCountsByStreet(string $streetName): array
    {
        // QueryBuilder für komplexere JOIN-Abfragen
        $qb = $this->createQueryBuilder('cc')
            ->select(
                'cc.street_name',
                'cc.latitude',
                'cc.longitude',
                'tc.count AS tatbestand_count',
                't.text AS tatbestand_text',
                'cc.createAt',
                'cc.id AS car_count_id'
            )
            ->leftJoin('App\Entity\TatbestandCount', 'tc', 'WITH', 'tc.car_count_id = cc.id')
            ->leftJoin('App\Entity\Tatbestand', 't', 'WITH', 'tc.tatbestand_id = t.id')
            ->where('cc.street_name = :streetName')
            ->setParameter('streetName', $streetName)
            ->orderBy('cc.id', 'ASC'); // Optional: Sortierung nach ID

        // Hol alle Ergebnisse als Array
        $results = $qb->getQuery()->getArrayResult();

        if (empty($results)) {
            return [];
        }

        // Strukturieren der Ergebnisse
        $finalResult = [];
        foreach ($results as $row) {
            if (!isset($finalResult['street_name'])) {
                $finalResult = [
                    'street_name' => $row['street_name'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'tatbestand_counts' => []
                ];
            }

            // Tatbestand hinzufügen
            $finalResult['tatbestand_counts'][] = [
                'count' => $row['tatbestand_count'],
                'text' => $row['tatbestand_text'],
                'createAt' => $row['createAt'],
                'id' => $row['car_count_id']
            ];
        }

        return $finalResult;
    }
    //    /**
    //     * @return CarCount[] Returns an array of CarCount objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CarCount
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
