<?php

namespace App\Repository;

use App\Entity\Motif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Motif>
 *
 * @method Motif|null find($id, $lockMode = null, $lockVersion = null)
 * @method Motif|null findOneBy(array $criteria, array $orderBy = null)
 * @method Motif[]    findAll()
 * @method Motif[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MotifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Motif::class);
    }

    public function save(Motif $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Motif $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getDocumentCourrier($demande){
        return $this->createQueryBuilder('d')
            ->select('f.path','f.alt','f.id')
            ->join('d.fichier','f')
            ->andWhere('d.demande =:demande')
            ->setParameter('demande', $demande)
            ->getQuery()
            ->getResult(); }

//    /**
//     * @return Motif[] Returns an array of Motif objects
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

//    public function findOneBySomeField($value): ?Motif
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    public function findOneBySomeFields($demande)
    {
        return $this->createQueryBuilder('m')
            ->select('f.alt')
            ->join('m.fichier','f')
            ->andWhere('m.demande = :val')
            ->setParameter('val', $demande)
            ->getQuery()
            ->getResult()
            ;
    }
    public function findOneBySomeField($demande)
    {
        return $this->createQueryBuilder('m')
                ->select('e.id')
            ->join('m.element','e')
            ->andWhere('m.demande = :val')
            ->setParameter('val', $demande)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
