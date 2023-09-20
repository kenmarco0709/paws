<?php

namespace App\Repository;

use App\Entity\MedicalRecordPrescriptionInventoryItemEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MedicalRecordPrescriptionInventoryItemEntity>
 *
 * @method MedicalRecordPrescriptionInventoryItemEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method MedicalRecordPrescriptionInventoryItemEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method MedicalRecordPrescriptionInventoryItemEntity[]    findAll()
 * @method MedicalRecordPrescriptionInventoryItemEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MedicalRecordPrescriptionInventoryItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicalRecordPrescriptionInventoryItemEntity::class);
    }

    public function add(MedicalRecordPrescriptionInventoryItemEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MedicalRecordPrescriptionInventoryItemEntity $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return MedicalRecordPrescriptionInventoryItemEntity[] Returns an array of MedicalRecordPrescriptionInventoryItemEntity objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?MedicalRecordPrescriptionInventoryItemEntity
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
