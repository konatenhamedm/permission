<?php

namespace App\Repository;

use App\Entity\Civilite;
use App\Entity\Demande;
use App\Entity\ElementMotif;
use App\Entity\Employe;
use App\Entity\Entreprise;
use App\Entity\Motif;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Demande>
 *
 * @method Demande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Demande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Demande[]    findAll()
 * @method Demande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeRepository extends ServiceEntityRepository
{
    use TableInfoTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demande::class);
    }

    public function save(Demande $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Demande $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAnnee()
    {
        return $this->createQueryBuilder('d')
            ->select('YEAR(d.dateDebut) as dateDebut')
            ->groupBy('dateDebut')
            ->getQuery()
            ->getResult();
    }
    public function getMois()
    {
        return $this->createQueryBuilder('d')
            ->select("DATE_FORMAT(d.dateDebut,'%M') as mois")
            ->groupBy('mois')
            ->getQuery()
            ->getResult();
    }

    public function getAnneeFin()
    {
        return $this->createQueryBuilder('d')
            ->select('YEAR(d.dateFin) as dateFin')
            ->groupBy('dateFin')
            ->getQuery()
            ->getResult();
    }

    public function nombreDemande($etat, $entreprise)
    {
        return $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->join('d.utilisateur', 'u')
            ->join('u.employe', 'e')
            ->join('e.entreprise', 'en')
            ->orderBy('d.dateCreation', 'ASC')
            ->join('e.fonction', 'f')
            ->andWhere('en.denomination =:entreprise')
            ->andWhere("d.etat =:etat")
            ->setParameter('etat', $etat)
            ->setParameter('entreprise', $entreprise)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function nombreDemandeByUser($etat, $utilisateur)
    {
        return $this->createQueryBuilder('d')
            ->select('count(d.id)')
            ->join('d.utilisateur', 'u')
            ->join('u.employe', 'e')
            ->join('e.entreprise', 'en')
            ->orderBy('d.dateCreation', 'ASC')
            ->join('e.fonction', 'f')
            ->andWhere('u =:utilisateur')
            ->andWhere("d.etat =:etat")
            ->setParameter('etat', $etat)
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getSingleScalarResult();
    }
    public function countAll(array $filters = [])
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableCivilite = $this->getTableName(Civilite::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);

        $sql = <<<SQL
SELECT COUNT(*) AS total
FROM {$tableDemande} d
JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
JOIN {$tableEmploye} e ON e.id = u.employe_id
JOIN {$tableCivilite} c ON c.id = e.civilite_id
WHERE 1 = 1

SQL;
        $params = [];
        $ands = [];

        foreach ($filters as $field => $value) {
            $field = snake_case($field);
            $fieldName = "e.{$field}_id";
            if (!in_array($field, ['mois', 'tranche', 'annee', 'anciennete']) && $value) {
                $ands[] = " {$fieldName} = :{$field}";
                $params[$field] = $value;
            }
        }

        if ($ands) {
            $sql .= ' AND ';
        }

        $sql .=  implode(' AND ', $ands);

        $ages = explode('_', $filters['tranche'] ?? '');
        $anciennetes = explode('_', $filters['anciennete'] ?? '');


        if ($ages && count($ages) >= 2) {


            $min = $ages[0];
            $max = $ages[1];
            $sql .= ' AND TIMESTAMPDIFF(YEAR, e.date_naissance, CURRENT_DATE) BETWEEN :age_1 AND :age_2 ';
            $params['age_1'] = $min;
            $params['age_2'] = $max;
        }


        if ($anciennetes && count($anciennetes) >= 2) {

            $min = $anciennetes[0];
            $max = $anciennetes[1];
            $sql .= ' AND TIMESTAMPDIFF(YEAR, e.date_service, CURRENT_DATE) BETWEEN :anc_1 AND :anc_2 ';
            $params['anc_1'] = $min;
            $params['anc_2'] = $max;
        }



        $stmt = $connection->executeQuery($sql, $params);
        return intval($stmt->fetchOne());
    }


    public function getSexeData(array $filters = [])
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableCivilite = $this->getTableName(Civilite::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);

        $sql = <<<SQL
SELECT COUNT(*) AS total, c.libelle
FROM {$tableDemande} d
JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
JOIN {$tableEmploye} e ON e.id = u.employe_id
JOIN {$tableCivilite} c ON c.id = e.civilite_id
WHERE 1 = 1

SQL;
        $params = [];


        $ands = [];

        foreach ($filters as $field => $value) {
            $field = snake_case($field);
            $fieldName = "e.{$field}_id";
            if (!in_array($field, ['mois', 'tranche', 'annee', 'anciennete']) && $value) {
                $ands[] = " {$fieldName} = :{$field}";
                $params[$field] = $value;
            }
        }

        if ($ands) {
            $sql .= ' AND ';
        }


        $sql .=  implode(' AND ', $ands);

        $ages = explode('_', $filters['tranche'] ?? '');
        $anciennetes = explode('_', $filters['anciennete'] ?? '');


        if ($ages && count($ages) >= 2) {

            $min = $ages[0];
            $max = $ages[1];
            $sql .= ' AND TIMESTAMPDIFF(YEAR, e.date_naissance, CURRENT_DATE) BETWEEN :age_1 AND :age_2 ';
            $params['age_1'] = $min;
            $params['age_2'] = $max;
        }


        if ($anciennetes && count($anciennetes) >= 2) {

            $min = $anciennetes[0];
            $max = $anciennetes[1];
            $sql .= ' AND TIMESTAMPDIFF(YEAR, e.date_service, CURRENT_DATE) BETWEEN :anc_1 AND :anc_2 ';
            $params['anc_1'] = $min;
            $params['anc_2'] = $max;
        }
        $sql .= ' GROUP BY c.libelle';
        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }





    public function getNiveauHierarchiqueSexe(array $filters = [])
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableCivilite = $this->getTableName(Civilite::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableEntreprise = $this->getTableName(Entreprise::class, $em);

        $sql = <<<SQL
SELECT COUNT(*) AS _total, c.libelle AS _genre, th.denomination AS _niveau_h, th.id AS _niveau_id
FROM {$tableDemande} d
JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
JOIN {$tableEmploye} e ON e.id = u.employe_id
JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
JOIN {$tableCivilite} c ON c.id = e.civilite_id
WHERE 1 = 1

SQL;
        $params = [];
        $ands = [];

        foreach ($filters as $field => $value) {
            $field = snake_case($field);
            $fieldName = "e.{$field}_id";
            if (!in_array($field, ['mois', 'tranche', 'annee', 'anciennete']) && $value) {
                $ands[] = " {$fieldName} = :{$field}";
                $params[$field] = $value;
            }
        }

        if ($ands) {
            $sql .= ' AND ';
        }





        $sql .=  implode(' AND ', $ands);



        $ages = explode('_', $filters['tranche'] ?? '');
        $anciennetes = explode('_', $filters['anciennete'] ?? '');


        if ($ages && count($ages) >= 2) {

            $min = $ages[0];
            $max = $ages[1];
            $sql .= ' AND TIMESTAMPDIFF(YEAR, e.date_naissance, CURRENT_DATE) BETWEEN :age_1 AND :age_2 ';
            $params['age_1'] = $min;
            $params['age_2'] = $max;
        }


        if ($anciennetes && count($anciennetes) >= 2) {

            $min = $anciennetes[0];
            $max = $anciennetes[1];
            $sql .= ' AND TIMESTAMPDIFF(YEAR, e.date_service, CURRENT_DATE) BETWEEN :anc_1 AND :anc_2 ';
            $params['anc_1'] = $min;
            $params['anc_2'] = $max;
        }

        $sql .= ' GROUP BY c.id, th.id';
        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }

    public function getAnneeRangeContrat($entreprise)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableCivilite = $this->getTableName(Civilite::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableEntreprise = $this->getTableName(Entreprise::class, $em);
        $sql = <<<SQL
SELECT MIN(YEAR(date_debut)) AS min_year, MAX(YEAR(date_debut)) AS max_year
FROM {$tableDemande} d
JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
JOIN {$tableEmploye} e ON e.id = u.employe_id
JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
WHERE e.entreprise_id = :entreprise
SQL;
        $params['entreprise'] = $entreprise;


        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAssociative();
    }



    public function getDataTypeContrat($entreprise)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableCivilite = $this->getTableName(Civilite::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableEntreprise = $this->getTableName(Entreprise::class, $em);
        $sql = <<<SQL
SELECT COUNT(*) AS _total, YEAR(date_debut)
FROM {$tableDemande} d
JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
JOIN {$tableEmploye} e ON e.id = u.employe_id
JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
WHERE e.entreprise_id = :entreprise
GROUP BY YEAR(date_debut)
ORDER BY YEAR(date_debut) ASC
SQL;
        $params['entreprise'] = $entreprise;


        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }

    public function getDemandeByEntreprise($dateDebut, $dateFin)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableCivilite = $this->getTableName(Civilite::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableEntreprise = $this->getTableName(Entreprise::class, $em);
        //dd($dateDebut,$dateFin);

        if ($dateDebut != null && $dateFin != null) {
            $sql = <<<SQL
SELECT COUNT(*) AS _total, th.id AS _niveau_id,th.denomination
FROM {$tableDemande} d
JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
JOIN {$tableEmploye} e ON e.id = u.employe_id
JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
WHERE YEAR(date_debut) BETWEEN :dateDebut AND :dateFin
GROUP BY th.id
SQL;
        } elseif ($dateDebut == null && $dateFin != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, th.id AS _niveau_id,th.denomination
            FROM {$tableDemande} d
            JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
            JOIN {$tableEmploye} e ON e.id = u.employe_id
            JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
            WHERE YEAR(date_debut) =:dateFin
            GROUP BY th.id
            SQL;
        } elseif ($dateDebut != null && $dateFin == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, th.id AS _niveau_id,th.denomination
            FROM {$tableDemande} d
            JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
            JOIN {$tableEmploye} e ON e.id = u.employe_id
            JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
            WHERE YEAR(date_debut) =:dateDebut
            GROUP BY th.id
            SQL;
        }

        $params['dateDebut'] = $dateDebut;
        $params['dateFin'] = $dateFin;


        $stmt = $connection->executeQuery($sql, $params);
        return $stmt->fetchAllAssociative();
    }



    public function getDemandeByMonthByEntreprise($date, $entreprise)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableCivilite = $this->getTableName(Civilite::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableEntreprise = $this->getTableName(Entreprise::class, $em);
        //dd($dateDebut,$dateFin);

        if ($date != null && $entreprise != null) {
            $sql = <<<SQL
SELECT COUNT(*) AS _total, DATE_FORMAT(date_debut,'%M') as mois
FROM {$tableDemande} d
JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
JOIN {$tableEmploye} e ON e.id = u.employe_id
JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
WHERE YEAR(date_debut) in (:date)  AND e.entreprise_id =:entreprise
GROUP BY mois
SQL;
        } elseif ($date == null && $entreprise != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(date_debut,'%M') as mois
            FROM {$tableDemande} d
            JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
            JOIN {$tableEmploye} e ON e.id = u.employe_id
            JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
            WHERE e.entreprise_id =:entreprise
            GROUP BY mois
            SQL;
        } elseif ($date != null && $entreprise == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, DATE_FORMAT(date_debut,'%M') as mois
            FROM {$tableDemande} d
            JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
            JOIN {$tableEmploye} e ON e.id = u.employe_id
            JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
            WHERE YEAR(date_debut) in (:date)
            GROUP BY mois
            SQL;
        }


        $params['date'] = $date;
        $params['entreprise'] = $entreprise;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function getDemandeByMotifByAnneeByEntreprise($date, $entreprise)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableMotif = $this->getTableName(Motif::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableElement = $this->getTableName(ElementMotif::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableEntreprise = $this->getTableName(Entreprise::class, $em);
        //dd($dateDebut,$dateFin);

        if ($date != null && $entreprise != null) {
            $sql = <<<SQL
SELECT COUNT(*) AS _total, em.libelle
FROM {$tableDemande} d
JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
JOIN {$tableEmploye} e ON e.id = u.employe_id
JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
JOIN {$tableMotif} m ON m.demande_id = d.id
JOIN {$tableElement} em ON em.id = m.element_id
WHERE YEAR(date_debut) in (:date)  AND e.entreprise_id =:entreprise
GROUP BY em.libelle
SQL;
        } elseif ($date == null && $entreprise != null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, em.libelle
            FROM {$tableDemande} d
            JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
            JOIN {$tableEmploye} e ON e.id = u.employe_id
            JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
            JOIN {$tableMotif} m ON m.demande_id = d.id
            JOIN {$tableElement} em ON em.id = m.element_id
            WHERE e.entreprise_id =:entreprise
            GROUP BY em.libelle
            SQL;
        } elseif ($date != null && $entreprise == null) {
            $sql = <<<SQL
            SELECT COUNT(*) AS _total, em.libelle
            FROM {$tableDemande} d
            JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
            JOIN {$tableEmploye} e ON e.id = u.employe_id
            JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
            JOIN {$tableMotif} m ON m.demande_id = d.id
            JOIN {$tableElement} em ON em.id = m.element_id
            WHERE YEAR(date_debut) in (:date)
            GROUP BY em.libelle
            SQL;
        }


        $params['date'] = $date;
        $params['entreprise'] = $entreprise;


        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }


    public function getDemandeByMotifByAnnee($date)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableMotif = $this->getTableName(Motif::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableElement = $this->getTableName(ElementMotif::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableEntreprise = $this->getTableName(Entreprise::class, $em);
        //dd($dateDebut,$dateFin);


        $sql = <<<SQL
    SELECT COUNT(*) AS _total, em.libelle,th.denomination
    FROM {$tableDemande} d
    JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
    JOIN {$tableEmploye} e ON e.id = u.employe_id
    JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
    JOIN {$tableMotif} m ON m.demande_id = d.id
    JOIN {$tableElement} em ON em.id = m.element_id
     WHERE YEAR(date_debut) in (:date)  
    SQL;


        $params['date'] = $date;

        $ands = [];



        if ($ands) {
            $sql .= ' AND ';
        }





        $sql .=  implode(' AND ', $ands);


        $sql .= ' GROUP BY em.libelle,th.denomination';
        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }

    public function getDemandeParEntreprise()
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableMotif = $this->getTableName(Motif::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableElement = $this->getTableName(ElementMotif::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableEntreprise = $this->getTableName(Entreprise::class, $em);
        //dd($dateDebut,$dateFin);


        $sql = <<<SQL
    SELECT COUNT(*) AS _total, em.libelle
    FROM {$tableDemande} d
    JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
    JOIN {$tableEmploye} e ON e.id = u.employe_id
    JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
    JOIN {$tableMotif} m ON m.demande_id = d.id
    JOIN {$tableElement} em ON em.id = m.element_id
     WHERE th.denomination ="APPATAM"
    SQL;


        $ands = [];



        if ($ands) {
            $sql .= ' AND ';
        }





        $sql .=  implode(' AND ', $ands);


        $sql .= ' GROUP BY em.libelle';
        $stmt = $connection->executeQuery($sql);

        return $stmt->fetchAllAssociative();
    }


    public function getNombreDemandeParSexe($statut, $date)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableCivilite = $this->getTableName(Civilite::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);

        $sql = <<<SQL
    SELECT COUNT(*) AS _total,c.code
    FROM {$tableDemande} d
    JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
    JOIN {$tableEmploye} e ON e.id = u.employe_id
    JOIN {$tableCivilite} c ON c.id = e.civilite_id
    /* WHERE c.code = :sexe   */
    SQL;



        $params['statut'] = $statut;
        $params['date'] = $date;

        $ands = [];



        if ($ands) {
            $sql .= ' AND ';
        }





        $sql .=  implode(' AND ', $ands);

        if ($statut != null) {

            $sql .= ' WHERE d.etat =:statut AND YEAR(date_debut) in (:date)';
        }
        $sql .= ' GROUP BY c.code';
        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }


    public function getNombreDemandeMotifParSexe($date)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableMotif = $this->getTableName(Motif::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableElement = $this->getTableName(ElementMotif::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableEntreprise = $this->getTableName(Entreprise::class, $em);
        $tableCivilite = $this->getTableName(Civilite::class, $em);

        $sql = <<<SQL
     SELECT COUNT(*) AS _total, c.code as genre,em.code as motif
    FROM {$tableDemande} d
    JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
    JOIN {$tableEmploye} e ON e.id = u.employe_id
    JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
    JOIN {$tableMotif} m ON m.demande_id = d.id
    JOIN {$tableElement} em ON em.id = m.element_id
    JOIN {$tableCivilite} c ON c.id = e.civilite_id
    /*  WHERE YEAR(date_debut) in (:date)  */ 
    SQL;



        //$params['statut'] = $statut;
        $params['date'] = $date;

        $ands = [];



        if ($ands) {
            $sql .= ' AND ';
        }





        $sql .=  implode(' AND ', $ands);



        $sql .= ' WHERE em.code in ("MOT1","MOT3") AND YEAR(date_debut) in (:date)';

        $sql .= ' GROUP BY motif,genre';
        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }


    public function getDemandeParNombre($entreprise)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableCivilite = $this->getTableName(Civilite::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableEntreprise = $this->getTableName(Entreprise::class, $em);

        $sql = <<<SQL
    SELECT COUNT(*) AS _total, d.nbre_jour
    FROM {$tableDemande} d
    JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
    JOIN {$tableEmploye} e ON e.id = u.employe_id
    JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
    JOIN {$tableCivilite} c ON c.id = e.civilite_id
    /* WHERE c.code = :sexe   */
    SQL;



        $params['entreprise'] = $entreprise;

        $ands = [];



        if ($ands) {
            $sql .= ' AND ';
        }





        $sql .=  implode(' AND ', $ands);



        $sql .= ' WHERE th.id =:entreprise';

        $sql .= ' GROUP BY d.nbre_jour';
        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchAllAssociative();
    }


    public function nombreDemandeStat($etat, $user)
    {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        $tableEmploye = $this->getTableName(Employe::class, $em);
        $tableMotif = $this->getTableName(Motif::class, $em);
        $tableUtilisateur = $this->getTableName(Utilisateur::class, $em);
        $tableElement = $this->getTableName(ElementMotif::class, $em);
        $tableDemande = $this->getTableName(Demande::class, $em);
        $tableEntreprise = $this->getTableName(Entreprise::class, $em);
        $tableCivilite = $this->getTableName(Civilite::class, $em);

        $sql = <<<SQL
     SELECT COUNT(*) AS _total
    FROM {$tableDemande} d
    JOIN {$tableUtilisateur} u ON u.id = d.utilisateur_id
    JOIN {$tableEmploye} e ON e.id = u.employe_id
    JOIN {$tableEntreprise} th ON th.id = e.entreprise_id
   
   
    /*  WHERE YEAR(date_debut) in (:date)  */ 
    SQL;



        //$params['statut'] = $statut;
        $params['etat'] = $etat;
        $params['user'] = $user;

        $ands = [];



        if ($ands) {
            $sql .= ' AND ';
        }





        $sql .=  implode(' AND ', $ands);


        if ($user != null && $etat == null) {

            $sql .= ' WHERE u.id =:user';
        } elseif ($etat != null && $user == null) {
            $sql .= ' WHERE d.etat =:etat';
        } elseif ($user != null && $etat != null) {
            $sql .= ' WHERE d.etat =:etat AND u.id =:user';
        }




        //$sql .= ' GROUP BY motif,genre';
        $stmt = $connection->executeQuery($sql, $params);

        return $stmt->fetchOne();
    }
    //    /**
    //     * @return Demande[] Returns an array of Demande objects
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

    //    public function findOneBySomeField($value): ?Demande
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
