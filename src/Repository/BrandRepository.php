<?php

namespace App\Repository;

use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method Brand|null find($id, $lockMode = null, $lockVersion = null)
 * @method Brand|null findOneBy(array $criteria, array $orderBy = null)
 * @method Brand[]    findAll()
 * @method Brand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    /**
     * @return Brand[] Returns an array of Equipment objects
     */

    public function findByCompany($companyId)
    {
        return $this->createQueryBuilder('brand')
            ->andWhere('brand.company = :val')
            ->setParameter('val', $companyId)
            ->orderBy('brand.name', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $companyId
     * @param $id
     * @return Brand
     * @throws NonUniqueResultException
     */
    public function findOneByCompanyID($companyId,$id)
    {
        return $this->createQueryBuilder('brand')
            ->andWhere('brand.company = :company')
            ->andWhere('brand.id = :id')
            ->setParameter('company', $companyId)
            ->setParameter('id', $id)
            ->orderBy('brand.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return Brand[] Returns an array of Brand objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Brand
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
