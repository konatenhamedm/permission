<?php

namespace App\Repository;

use App\Entity\Demande;
use App\Entity\Employe;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Employe>
 *
 * @method Employe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employe[]    findAll()
 * @method Employe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeRepository extends ServiceEntityRepository
{
    use TableInfoTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

    public function add(Employe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Employe $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    /**
     * @return mixed
     */
    public function withoutAccount($entreprise)
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('e')
            ->leftJoin('e.entreprise', 'en')
            ->leftJoin('e.utilisateur', 'u2')
            ->andWhere('en =:entreprise')
            ->andWhere('u2.employe IS NULL')
            ->setParameter('entreprise', $entreprise);

        return $qb;
    }


    public function nombreEmploye($entreprise)
    {
        return $this->createQueryBuilder('e')
            ->select("count(e.id)")
            ->join('e.entreprise', 'en')
            ->andWhere('en.denomination =:entreprise')
            ->setParameter('entreprise', $entreprise)
            ->getQuery()
            ->getSingleScalarResult();
    }




    public function getNombreDemandeParMois($date, $employe, $etat)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        if ($etat == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(date_debut,'%M') as mois
           FROM {$tableDemande} d
           JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
           JOIN {$tableEmploye} e ON e.id = u.employe_id
          
          
           /*  WHERE YEAR(date_debut) in (:date)  */ 
           SQL;
        } else {

            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(date_debut,'%D') as jour,d.nbre_jour
           FROM {$tableDemande} d
           JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
           JOIN {$tableEmploye} e ON e.id = u.employe_id
          
          
           /*  WHERE YEAR(date_debut) in (:date)  */ 
           SQL;
        }



        //$params['statut'] = $statut;
        $params['date'] = $date;
        $params['employe'] = $employe;
        $params['etat'] = $etat;

        $ands = [];



        if ($ands) {
            $sql .= ' AND ';
        }


        $sql .=  implode(' AND ', $ands);


        if ($etat == null) {
            $sql .= ' WHERE e.id =:employe and YEAR(date_debut) in (:date)';
            $sql .= ' GROUP BY mois';
        } else {

            $sql .= " WHERE e.id =:employe and DATE_FORMAT(d.date_debut,'%M') =:date";
            $sql .= ' GROUP BY d.nbre_jour,jour';
        }





        //$sql .= ' GROUP BY motif,genre';
        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }



    //    /**
    //     * @return Employe[] Returns an array of Employe objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Employe
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
