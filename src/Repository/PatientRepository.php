<?php

namespace App\Repository;

use App\Entity\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;

class PatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    /**
     * @param $companyId
     * @return Patient[] Returns an array of Patient objects
     */

    public function findByCompany($companyId)
    {
        return $this->createQueryBuilder('patient')
            ->andWhere('patient.company = :val')
            ->setParameter('val', $companyId)
            ->orderBy('patient.id', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param $companyId
     * @param $id
     * @return Patient
     * @throws NonUniqueResultException
     */
    public function findOneByCompanyID($companyId,$id)
    {
        return $this->createQueryBuilder('patient')
            ->andWhere('patient.company = :company')
            ->andWhere('patient.id = :id')
            ->setParameter('company', $companyId)
            ->setParameter('id', $id)
            ->orderBy('patient.id', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
