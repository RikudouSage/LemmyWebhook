<?php

namespace App\Repository;

use App\Entity\WebhookResponse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WebhookResponse>
 *
 * @method WebhookResponse|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebhookResponse|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebhookResponse[]    findAll()
 * @method WebhookResponse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebhookResponseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebhookResponse::class);
    }

//    /**
//     * @return WebhookResponse[] Returns an array of WebhookResponse objects
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

//    public function findOneBySomeField($value): ?WebhookResponse
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
