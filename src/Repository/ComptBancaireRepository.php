<?php

namespace App\Repository;

use App\Entity\ComptBancaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ComptBancaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method ComptBancaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method ComptBancaire[]    findAll()
 * @method ComptBancaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ComptBancaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ComptBancaire::class);
    }

    // /**
    //  * @return ComptBancaire[] Returns an array of ComptBancaire objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ComptBancaire
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
