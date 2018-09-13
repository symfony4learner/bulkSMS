<?php

namespace App\Repository;

use App\Entity\SmsIn;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SmsIn|null find($id, $lockMode = null, $lockVersion = null)
 * @method SmsIn|null findOneBy(array $criteria, array $orderBy = null)
 * @method SmsIn[]    findAll()
 * @method SmsIn[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmsInRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SmsIn::class);
    }

//    /**
//     * @return SmsIn[] Returns an array of SmsIn objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SmsIn
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
