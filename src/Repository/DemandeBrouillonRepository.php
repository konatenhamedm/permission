<?php

namespace App\Repository;

use App\Entity\DemandeBrouillon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeBrouillon>
 *
 * @method DemandeBrouillon|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandeBrouillon|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandeBrouillon[]    findAll()
 * @method DemandeBrouillon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeBrouillonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeBrouillon::class);
    }

    public function save(DemandeBrouillon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DemandeBrouillon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function nombreBroullionStat($etat,$entreprise){
       if($entreprise != null){
        return $this->createQueryBuilder('d')
        ->select("count(d.id)")
        ->join('d.utilisateur','u')
        ->join('u.employe','e')
        ->join('e.entreprise','en')
        ->andWhere('d.etat =:etat')
        ->andWhere('en.denomination =:entreprise')
        ->setParameter('entreprise',$entreprise)
        ->setParameter('etat',$etat)
        ->getQuery()
          ->getSingleScalarResult();
       }else{
        return $this->createQueryBuilder('d')
        ->select("count(d.id)")
        ->join('d.utilisateur','u')
        ->join('u.employe','e')
        ->join('e.entreprise','en')
        ->andWhere('d.etat =:etat')
        ->setParameter('etat',$etat)
        ->getQuery()
          ->getSingleScalarResult();
       }
    }


//    /**
//     * @return DemandeBrouillon[] Returns an array of DemandeBrouillon objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DemandeBrouillon
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
