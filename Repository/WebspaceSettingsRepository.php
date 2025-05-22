<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Repository;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WebspaceSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebspaceSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebspaceSettings[] findAll()
 * @method WebspaceSettings[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

/**
 * @extends ServiceEntityRepository<WebspaceSettings>
 */
class WebspaceSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebspaceSettings::class);
    }

    //    /**
    //     * @return WebspaceSettings[] Returns an array of WebspaceSettings objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?WebspaceSettings
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
