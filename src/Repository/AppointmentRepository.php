<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method Appointment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appointment[]    findAll()
 * @method Appointment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    /**
     * @param $companyId
     * @return Appointment[] Returns an array of Appointment objects
     */

    public function findByCompany($companyId)
    {
        return $this->createQueryBuilder('appointment')
            ->andWhere('appointment.company = :val')
            ->setParameter('val', $companyId)
            ->orderBy('appointment.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $companyId
     * @param $id
     * @return Appointment
     * @throws NonUniqueResultException
     */
    public function findOneByCompanyID($companyId,$id)
    {
        return $this->createQueryBuilder('appointment')
            ->andWhere('appointment.company = :company')
            ->andWhere('appointment.id = :id')
            ->setParameter('company', $companyId)
            ->setParameter('id', $id)
            ->orderBy('appointment.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }


    // /**
    //  * @return Appointment[] Returns an array of Appointment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Appointment
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
