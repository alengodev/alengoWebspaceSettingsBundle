<?php

declare(strict_types=1);

namespace Alengo\Bundle\AlengoWebspaceSettingsBundle\Repository;

use Alengo\Bundle\AlengoWebspaceSettingsBundle\Entity\WebspaceSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findByListQuery($param): array
    {
        $queryTotal = $this->createQueryBuilder('w')
            ->select('COUNT(w.id)')
            ->andWhere('w.webspaceKey = :val')
            ->setParameter('val', $param['webspaceKey'])
            ->getQuery()
            ->getSingleScalarResult();

        $query = $this->createQueryBuilder('w')
            ->andWhere('w.webspaceKey = :val')
            ->setParameter('val', $param['webspaceKey']);

        if (null !== $param['sortBy'] && null !== $param['sortOrder']) {
            $query->orderBy('w.' . $param['sortBy'], $param['sortOrder']);
        } else {
            $query->orderBy('w.id', 'DESC');
        }

        if (null !== $param['search'] && null !== $param['fields']) {
            $searchFields = \explode(',', (string) $param['fields']);
            $orX = $query->expr()->orX();
            foreach ($searchFields as $field) {
                $orX->add($query->expr()->like('w.' . $field, ':search'));
            }
            $query->andWhere($orX)
                ->setParameter('search', '%' . $param['search'] . '%');
        }

        if (null !== $param['limit']) {
            $query->setFirstResult(($param['page'] - 1) * $param['limit']);
            $query->setMaxResults($param['limit']);
        }

        $result = $query->getQuery()->getResult();

        return [
            'result' => $result,
            'page' => $param['page'] ?? 1,
            'limit' => $param['limit'] ?? 10,
            'total' => $queryTotal,
        ];
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
