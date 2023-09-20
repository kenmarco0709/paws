<?php

namespace App\Repository;

use App\Entity\InvoiceAdmissionInventoryItemEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InvoiceAdmissionInventoryItemEntity>
 *
 * @method InvoiceAdmissionInventoryItemEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method InvoiceAdmissionInventoryItemEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method InvoiceAdmissionInventoryItemEntity[]    findAll()
 * @method InvoiceAdmissionInventoryItemEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceAdmissionInventoryItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvoiceAdmissionInventoryItemEntity::class);
    }

    public function add(InvoiceAdmissionInventoryItemEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InvoiceAdmissionInventoryItemEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return InvoiceAdmissionInventoryItemEntity[] Returns an array of InvoiceAdmissionInventoryItemEntity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?InvoiceAdmissionInventoryItemEntity
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
