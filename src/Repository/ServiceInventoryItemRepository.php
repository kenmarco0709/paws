<?php

namespace App\Repository;

use App\Entity\ServiceInventoryItemEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceInventoryItemEntity>
 *
 * @method ServiceInventoryItemEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceInventoryItemEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceInventoryItemEntity[]    findAll()
 * @method ServiceInventoryItemEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceInventoryItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceInventoryItemEntity::class);
    }

    public function add(ServiceInventoryItemEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ServiceInventoryItemEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ServiceInventoryItemEntity[] Returns an array of ServiceInventoryItemEntity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ServiceInventoryItemEntity
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
