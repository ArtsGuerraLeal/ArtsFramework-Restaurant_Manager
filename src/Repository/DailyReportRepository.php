<?php

namespace App\Repository;

use App\Entity\DailyReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method DailyReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyReport[]    findAll()
 * @method DailyReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailyReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyReport::class);
    }

    /**
     * @return DailyReport[] Returns an array of DailyReport objects
     */

    public function findByCompany($companyId)
    {
        return $this->createQueryBuilder('daily_report')
            ->andWhere('daily_report.company = :val')
            ->setParameter('val', $companyId)
            ->orderBy('daily_report.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $companyId
     * @param $id
     * @return DailyReport
     * @throws NonUniqueResultException
     */
    public function findOneByCompanyID($companyId,$id)
    {
        return $this->createQueryBuilder('daily_report')
            ->andWhere('daily_report.company = :company')
            ->andWhere('daily_report.id = :id')
            ->setParameter('company', $companyId)
            ->setParameter('id', $id)
            ->orderBy('daily_report.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return DailyReport[] Returns an array of DailyReport objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DailyReport
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
