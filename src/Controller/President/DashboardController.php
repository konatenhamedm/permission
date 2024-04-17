<?php

namespace App\Controller\President;

use App\Entity\Entreprise;
use App\Repository\CiviliteRepository;
use App\Repository\DemandeRepository;
use App\Repository\EmployeRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/president/dashboard')]
class DashboardController extends AbstractController
{
    private $security;
    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    #[Route('/', name: 'app_president_dashboard')]
    public function index(): Response
    {
        $groupe = $this->security->getUser()->getGroupe()->getName();

        if ($groupe == "Présidents") {
            $modules = [
                [
                    'label' => 'Evolution demandes par sexe et entreprise',
                    'id' => 'chart_one',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_rh_dashboard_hierarchie_sexe')
                ],
                [
                    'label' => 'Evolution demandes par mois et entreprise',
                    'id' => 'chart_two',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_courbe_demande_entreprise_month_filtre_annee_entreprise')
                ],
                [
                    'label' => 'Evolution demandes par sexe',
                    'id' => 'chart_tree',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_dashboard_demande_by_sexe_by_entreprise')
                ],
                [
                    'label' => 'Evolution demandes années et par entreprise',
                    'id' => 'chart_four',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_dashboard_demande_annee_entreprise')
                ],
                [
                    'label' => 'Evolution demandes par motifs et année et entreprise',
                    'id' => 'chart_py_age',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_dashboard_demande_by_motif_annee_by_entreprise')
                ],
                [
                    'label' => 'Demande par motif et entreprise',
                    'id' => 'chart_py_anc',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_dashboard_demande_by_motif_by_entreprise')
                ],
                [
                    'label' => 'Demandes par entreprise',
                    'id' => 'chart_maitrise',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_demande_entreprise_filtre_annee')
                ],

                [
                    'label' => 'Comparaison homme contre femme',
                    'id' => 'chart_compraison',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_dashboard_demande_woman_man')
                ],
                [
                    'label' => 'Classement par nombre de jours',
                    'id' => 'chart_classement',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_dashboard_classement_par_nombre_entreprise')
                ],
                //

            ];
        } else {
            $modules = [

                [
                    'label' => 'Evolution demandes par mois et entreprise',
                    'id' => 'chart_two',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_courbe_demande_entreprise_month_filtre_annee_entreprise')
                ],
                [
                    'label' => 'Evolution demandes par sexe',
                    'id' => 'chart_tree',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_dashboard_demande_by_sexe_by_entreprise')
                ],
                [
                    'label' => 'Evolution demandes années et par entreprise',
                    'id' => 'chart_four',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_dashboard_demande_annee_entreprise')
                ],
                [
                    'label' => 'Evolution demandes par motifs et année et entreprise',
                    'id' => 'chart_py_age',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_dashboard_demande_by_motif_annee_by_entreprise')
                ],
                [
                    'label' => 'Comparaison homme contre femme',
                    'id' => 'chart_compraison',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_dashboard_demande_woman_man')
                ],
                [
                    'label' => 'Classement par nombre',
                    'id' => 'chart_classement',
                    'icon' => 'bi bi-list',
                    'href' => $this->generateUrl('app_president_dashboard_classement_par_nombre_entreprise')
                ],
                //

            ];
        }

        return $this->render('president/dashboard/index.html.twig', [
            'modules' => $modules,
            'titre' => $groupe,
        ]);
    }


    #[Route('/genre', name: 'app_president_dashboard_demande_by_sexe_by_entreprise')]
    public function indexGenre(Request $request, EmployeRepository $employeRepository)
    {
        return $this->render('president/dashboard/demande_by_sexe_by_entreprise.html.twig');
    }


    #[Route('/data-genre', name: 'app_rh_dashboard_genre_data')]
    public function dataGenre(Request $request, DemandeRepository $employeRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];

        $totalGlobal = $employeRepository->countAll($filters);

        $data = $employeRepository->getSexeData($filters);
        $results = [];
        foreach ($data as $row) {
            $total = ($row['total'] / $totalGlobal) * 100;
            $results[] = [
                'name' => $row['libelle'],
                'y' => round($total),
                'value' => $row['total'],
                'drilldown' => null
            ];
        }
        return $this->json($results);
    }


    #[Route('/hierarchie-sexe', name: 'app_rh_dashboard_hierarchie_sexe')]
    public function indexHierarchiqueSexe(Request $request)
    {
        return $this->render('president/dashboard/entreprise_sexe.html.twig');
    }

    #[Route('/data-hierarchie-sexe', name: 'app_rh_dashboard_hierarchie_sexe_data')]
    public function dataHierarchiqueSexe(Request $request, DemandeRepository $employeRepository, CiviliteRepository $genreRepository, EntrepriseRepository $entrepriseRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];

        $genres = $genreRepository->findAll();
        if (!empty($filters['civilite'])) {
            $genres = $genreRepository->findBy(['id' => $filters['civilite']]);
        }
        $entreprise = $entrepriseRepository->findBy([], ['id' => 'ASC']);
        $xAxis = [];
        $allIds = array_map(function ($entreprise) use (&$xAxis) {
            $xAxis[] = $entreprise->getDenomination();
            return $entreprise->getId();
        }, $entreprise);

        $results = [];
        $data = [];
        foreach ($genres as $genre) {
            $idGenre = $genre->getId();
            $filters['civilite'] = $idGenre;
            $data[$idGenre] = $employeRepository->getNiveauHierarchiqueSexe($filters);
        }


        foreach ($data as $rows) {
            usort($rows, function ($a, $b) {
                return $a['_niveau_id'] <=> $b['_niveau_id'];
            });
        }


        foreach ($data as $idGenre => $rows) {
            $allNiveaux = [];
            foreach ($rows as $_row) {
                $currentNiveau = $_row['_niveau_id'];
                $allNiveaux[$currentNiveau] = $_row['_total'];
            }
            foreach ($allIds as $id) {
                if (!in_array($id, array_keys($allNiveaux))) {
                    $allNiveaux[$id] = 0;
                }
            }

            ksort($allNiveaux);



            $results[$idGenre] = array_values($allNiveaux);
        }

        $getLibelleGenre = function ($idGenre) use ($genreRepository) {
            return $genreRepository->find($idGenre);
        };

        $series = [];

        foreach ($results as $idGenre => $data) {
            $_genre = $getLibelleGenre($idGenre);
            // dd($_genre);
            $colors = ['M' => '#3498db', 'F' => 'rgba(252,185,0,1)', 'A' => '#FF6600'];
            $series[] = ['name' => $_genre->getLibelle(), 'data' => $data, 'color' => $colors[$_genre->getCode()]];
        }

        return $this->json(['series' => $series, 'xAxis' => $xAxis]);
    }

    #[Route('/demande/annee/entreprise1', name: 'app_president_dashboard_demande_annee_entreprise')]
    public function indexTypeContrat(Request $request)
    {
        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_president_dashboard_demande_annee_entreprise'))
            ->setMethod('POST');

        if ($this->security->getUser()->getGroupe()->getName() == "Présidents") {
            $formBuilder->add('typeContrat', EntityType::class, [
                'placeholder' => '---',
                'choice_label' => 'denomination',
                'label' => 'Selectionner une entreprise',
                'attr' => ['class' => 'has-select2'],
                'choice_attr' => function (Entreprise $entreprise) {
                    return ['data-value' => $entreprise->getDenomination()];
                },
                'class' => Entreprise::class,
                'required' => false
            ]);
        } else {
            $formBuilder->add('typeContrat', EntityType::class, [
                'placeholder' => '---',
                'choice_label' => 'denomination',
                'label' => 'Selectionner une entreprise',
                'attr' => ['class' => 'has-select2'],
                'choice_attr' => function (Entreprise $entreprise) {
                    return ['data-value' => $entreprise->getDenomination()];
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->innerJoin('e.employes', 'em')
                        ->innerJoin('em.utilisateur', 'u')
                        ->andWhere('u =:user')
                        ->setParameter('user', $this->security->getUser());
                },
                'class' => Entreprise::class,
                'required' => false
            ]);
        }




        $form = $formBuilder->getForm();

        return $this->renderForm('president/dashboard/demande_by_entreprise_by_year.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/data-type-contrat', name: 'app_rh_dashboard_type_contrat_data', condition: "request.query.has('filters')")]
    public function dataTypeContrat(Request $request, DemandeRepository $employeRepository, EntrepriseRepository $typeContratRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $typeContratId = $filters['entreprise'];
        $dataAnnees = $employeRepository->getAnneeRangeContrat($typeContratId);
        $annees = range($dataAnnees['min_year'], $dataAnnees['max_year']);
        $data = $employeRepository->getDataTypeContrat($typeContratId);

        $typeContrat = $typeContratRepository->find($typeContratId);

        $series = [['name' => $typeContrat->getDenomination(), 'data' => []]];

        foreach ($data as $_row) {
            $series[0]['data'][] = $_row['_total'];
        }

        return $this->json(['series' => $series, 'annees' => $annees]);
    }


    #[Route('/demande/entreprise/filtre/annee', name: 'app_president_demande_entreprise_filtre_annee')]
    public function indexGenregg(Request $request, DemandeRepository $demandeRepository)
    {

        $data = $demandeRepository->getAnnee();
        $dataFin = $demandeRepository->getAnneeFin();
        //dd($data);
        $annees = [];
        $anneesFin = [];
        foreach ($data as $value) {
            $annees[$value['dateDebut']] =  $value['dateDebut'];
        }
        foreach ($dataFin as $value) {
            if ($value['dateFin'] != null) {

                $anneesFin[$value['dateFin']] =  $value['dateFin'];
            }
        }
        // dd($anneesFin);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_president_demande_entreprise_filtre_annee'))
            ->setMethod('POST');

        $formBuilder->add(
            'dateFin',
            ChoiceType::class,
            [
                /*  'placeholder' => '---', */
                'label' => 'Date fin',
                'required'     => false,
                'expanded'     => false,
                'attr' => ['class' => 'has-select2 dateFin'],
                'multiple' => false,
                // 'empty_value' => 'Choose your gender',
                'empty_data'  => null,
                'choices'  => array_flip($anneesFin),
            ]
        )->add(
            'dateDebut',
            ChoiceType::class,
            [
                'placeholder' => '---',
                'label' => 'Date debut',
                'required'     => false,
                'expanded'     => false,
                'attr' => ['class' => 'has-select2 dateDebut'],
                'multiple' => false,
                'choices'  => array_flip($annees),
            ]
        );


        $form = $formBuilder->getForm();

        return $this->renderForm('president/dashboard/demande_by_entreprise_bar_year.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/api/demande/entreprise/filtre/annee', name: 'app_president_dashboard_api_demande_entreprise_filtre_annee', condition: "request.query.has('filters')")]
    public function apiDemandeEntrepriseFiltreAnnee(Request $request, DemandeRepository $employeRepository, EntrepriseRepository $entrepriseRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $dateDebut = $filters['dateDebut'];
        $dateFin = $filters['dateFin'];


        $data = $employeRepository->getDemandeByEntreprise($dateDebut, $dateFin);
        //dd($data);
        $colors = ['DJELA' => '#262626', 'APPATAM' => '#cf2e2e', 'YEFIEN' => '#FF6600', "SUZANG GROUP" => '#FF6600', "SOCOPI" => '#FF6600'];
        // $series[] = ['name' => $_genre->getLibelle(), 'data' => $data, 'color' => $colors[$_genre->getCode()]];
        foreach ($data as $_row) {
            $datas[] = $_row['_total'];
            $series[0]['data'][] = $_row['_total'];
            $series[0]['colorByPoint'][] = "true";
            // $series[0]['name'][] = $_row['denomination'];
            $entreprises[] = $_row['denomination'];
        }



        return $this->json(['series' => $series, 'entreprises' => $entreprises]);
    }


    #[Route('/courbe/demande/entreprise/month/filtre/annee/entreprise', name: 'app_president_courbe_demande_entreprise_month_filtre_annee_entreprise')]
    public function indexCourbeDemandeByEntrepriseByMonth(Request $request, DemandeRepository $demandeRepository)
    {

        $data = $demandeRepository->getAnnee();
        $dataFin = $demandeRepository->getAnneeFin();
        //dd($data);
        $annees = [];
        $anneesFin = [];
        foreach ($data as $value) {
            $annees[$value['dateDebut']] =  $value['dateDebut'];
        }
        foreach ($dataFin as $value) {
            if ($value['dateFin'] != null) {

                $anneesFin[$value['dateFin']] =  $value['dateFin'];
            }
        }
        // dd($anneesFin);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_president_courbe_demande_entreprise_month_filtre_annee_entreprise'))
            ->setMethod('POST');
        if ($this->security->getUser()->getGroupe()->getName() == "Présidents") {
            $formBuilder->add('entreprise', EntityType::class, [
                'placeholder' => '---',
                'choice_label' => 'denomination',
                'label' => 'Selectionner une entreprise',
                'attr' => ['class' => 'has-select2'],
                'choice_attr' => function (Entreprise $entreprise) {
                    return ['data-value' => $entreprise->getDenomination()];
                },
                'class' => Entreprise::class,
                'required' => false
            ]);
        } else {
            $formBuilder->add('entreprise', EntityType::class, [
                'placeholder' => '---',
                'choice_label' => 'denomination',
                'label' => 'Selectionner une entreprise',
                'attr' => ['class' => 'has-select2'],
                'choice_attr' => function (Entreprise $entreprise) {
                    return ['data-value' => $entreprise->getDenomination()];
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->innerJoin('e.employes', 'em')
                        ->innerJoin('em.utilisateur', 'u')
                        ->andWhere('u =:user')
                        ->setParameter('user', $this->security->getUser());
                },
                'class' => Entreprise::class,
                'required' => false
            ]);
        }
        $formBuilder
            ->add(
                'dateDebut',
                ChoiceType::class,
                [
                    'placeholder' => '---',
                    'label' => 'Date debut',
                    'required'     => false,
                    'expanded'     => false,
                    'attr' => ['class' => 'has-select2 date'],
                    'multiple' => false,
                    'choices'  => array_flip($annees),
                ]
            );


        $form = $formBuilder->getForm();

        return $this->renderForm('president/dashboard/courbe_demande_by_entreprise_by_month.html.twig', [
            'form' => $form
        ]);
    }


    #[Route('/api/courbe/demande/entreprise/month/filtre/annee/entreprise', name: 'app_president_dashboard_api_courbe_demande_entreprise_month_filtre_annee_entreprise', condition: "request.query.has('filters')")]
    public function apiCourbeDemandeEntrepriseMonthFiltreAnneeEntreprise(Request $request, DemandeRepository $employeRepository, EntrepriseRepository $entrepriseRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        // $dateDebut = $filters['dateDebut'];
        $date = $filters['date'];
        $entreprise = $filters['entreprise'];

        //dd($date,$entreprise);
        $data = $employeRepository->getDemandeByMonthByEntreprise($date, $entreprise);
        //dd($data);

        $mois = [];
        $annees = [];
        $nombre = [];

        foreach ($data as $element) {
            $mois[] = $element['mois'];
            $nombre[] = $element['_total'];
        }

        $series = [
            [
                "name" => "Appatam",
                "marker" => [
                    "symbol" => 'square'
                ],
                "data" => $nombre,
            ]
        ];
        // dd($mois);


        return $this->json(['series' => $series, 'mois' => $mois]);
    }


    #[Route('/demande/motif/annee/entreprise', name: 'app_president_dashboard_demande_by_motif_annee_by_entreprise')]
    public function indexDemandeBymotifByAnneeByEntreprise(Request $request, DemandeRepository $demandeRepository)
    {

        $data = $demandeRepository->getAnnee();
        $dataFin = $demandeRepository->getAnneeFin();
        //dd($data);
        $annees = [];
        $anneesFin = [];
        foreach ($data as $value) {
            $annees[$value['dateDebut']] =  $value['dateDebut'];
        }
        foreach ($dataFin as $value) {
            if ($value['dateFin'] != null) {

                $anneesFin[$value['dateFin']] =  $value['dateFin'];
            }
        }
        // dd($anneesFin);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_president_dashboard_demande_by_motif_annee_by_entreprise'))
            ->setMethod('POST');
        if ($this->security->getUser()->getGroupe()->getName() == "Présidents") {
            $formBuilder->add('entreprise', EntityType::class, [
                'placeholder' => '---',
                'choice_label' => 'denomination',
                'label' => 'Selectionner une entreprise',
                'attr' => ['class' => 'has-select2'],
                'choice_attr' => function (Entreprise $entreprise) {
                    return ['data-value' => $entreprise->getDenomination()];
                },
                'class' => Entreprise::class,
                'required' => false
            ]);
        } else {
            $formBuilder->add('entreprise', EntityType::class, [
                'placeholder' => '---',
                'choice_label' => 'denomination',
                'label' => 'Selectionner une entreprise',
                'attr' => ['class' => 'has-select2'],
                'choice_attr' => function (Entreprise $entreprise) {
                    return ['data-value' => $entreprise->getDenomination()];
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->innerJoin('e.employes', 'em')
                        ->innerJoin('em.utilisateur', 'u')
                        ->andWhere('u =:user')
                        ->setParameter('user', $this->security->getUser());
                },
                'class' => Entreprise::class,
                'required' => false
            ]);
        }
        $formBuilder
            ->add(
                'dateDebut',
                ChoiceType::class,
                [
                    'placeholder' => '---',
                    'label' => 'Date debut',
                    'required'     => false,
                    'expanded'     => false,
                    'attr' => ['class' => 'has-select2 date'],
                    'multiple' => false,
                    'choices'  => array_flip($annees),
                ]
            );


        $form = $formBuilder->getForm();

        return $this->renderForm('president/dashboard/demande_by_motif_annee_entreprise.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/api/demande/motif/annee/entreprise', name: 'app_president_dashboard_api_demande_by_motif_annee_by_entreprise', condition: "request.query.has('filters')")]
    public function apiDemandeBymotifByAnneeByEntreprise(Request $request, DemandeRepository $employeRepository, EntrepriseRepository $entrepriseRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        // $dateDebut = $filters['dateDebut'];
        $date = $filters['date'];
        $entreprise = $filters['entreprise'];

        //dd($date,$entreprise);
        $data = $employeRepository->getDemandeByMotifByAnneeByEntreprise($date, $entreprise);


        $datas = [];


        foreach ($data as $skey => $_row) {
            if ($skey == 0) {
                $datas[] = [
                    'name' => $_row['libelle'],
                    'y' => $_row['_total'],
                    'sliced' => true,
                    'selected' => true,

                ];
            } else {
                $datas[] = [
                    'name' => $_row['libelle'],
                    'y' => $_row['_total']

                ];
            }
        }

        $series = [
            'series' => [
                "name" => "Appatam",
                "colorByPoint" => true,
                "data" => $datas,
            ]
        ];

        // dd($this->json($series));

        return $this->json(['series' => $series]);
    }

    #[Route('/demande/motif/entreprise', name: 'app_president_dashboard_demande_by_motif_by_entreprise')]
    public function indexDemandeBymotifByEntreprise(Request $request, DemandeRepository $demandeRepository)
    {

        $data = $demandeRepository->getAnnee();
        $dataFin = $demandeRepository->getAnneeFin();
        //dd($data);
        $annees = [];
        $anneesFin = [];
        foreach ($data as $value) {
            $annees[$value['dateDebut']] =  $value['dateDebut'];
        }
        foreach ($dataFin as $value) {
            if ($value['dateFin'] != null) {

                $anneesFin[$value['dateFin']] =  $value['dateFin'];
            }
        }
        // dd($anneesFin);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_president_dashboard_demande_by_motif_by_entreprise'))
            ->setMethod('POST');
        if ($this->security->getUser()->getGroupe()->getName() == "Présidents") {
            $formBuilder->add('entreprise', EntityType::class, [
                'placeholder' => '---',
                'choice_label' => 'denomination',
                'label' => 'Selectionner une entreprise',
                'attr' => ['class' => 'has-select2'],
                'choice_attr' => function (Entreprise $entreprise) {
                    return ['data-value' => $entreprise->getDenomination()];
                },
                'class' => Entreprise::class,
                'required' => false
            ]);
        } else {
            $formBuilder->add('entreprise', EntityType::class, [
                'placeholder' => '---',
                'choice_label' => 'denomination',
                'label' => 'Selectionner une entreprise',
                'attr' => ['class' => 'has-select2'],
                'choice_attr' => function (Entreprise $entreprise) {
                    return ['data-value' => $entreprise->getDenomination()];
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->innerJoin('e.employes', 'em')
                        ->innerJoin('em.utilisateur', 'u')
                        ->andWhere('u =:user')
                        ->setParameter('user', $this->security->getUser());
                },
                'class' => Entreprise::class,
                'required' => false
            ]);
        }
        $formBuilder
            ->add(
                'dateDebut',
                ChoiceType::class,
                [
                    'placeholder' => '---',
                    'label' => 'Date',
                    'required'     => false,
                    'expanded'     => false,
                    'attr' => ['class' => 'has-select2 date'],
                    'multiple' => false,
                    'choices'  => array_flip($annees),
                ]
            );


        $form = $formBuilder->getForm();

        return $this->renderForm('president/dashboard/demande_by_motif_entreprise.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/api/demande/motif/entreprise', name: 'app_president_dashboard_api_demande_by_motif_by_entreprise', condition: "request.query.has('filters')")]
    public function apiDemandeBymotifByEntreprise(Request $request, DemandeRepository $employeRepository, EntrepriseRepository $entrepriseRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        // $dateDebut = $filters['dateDebut'];
        $date = $filters['date'];

        $dataAppatam = [];
        $dataSocopi = [];
        $dataYefien = [];
        $dataDjela = [];
        $dataSuzang = [];
        $dataMotif = [];
        /* 
        $bigData = [
            'APPATAM' => $dataAppatam,
            'SOCOPI' => $dataSocopi,
            'YEFIEN' => $dataYefien,
            'DJELA' => $dataDjela,
            'SUZANG' => $dataSuzang,
        ]; */


        //dd($date,$entreprise);
        $data = $employeRepository->getDemandeByMotifByAnnee($date);

        foreach ($data  as $key => $value) {
            $dataMotif[] = $value['libelle'];
            if ($value['denomination'] == "APPATAM")
                $dataAppatam[] = $value['_total'];
            if ($value['denomination'] == "YEFIEN")
                $dataYefien[] = $value['_total'];
            if ($value['denomination'] == "SOCOPI")
                $dataSocopi[] = $value['_total'];
            if ($value['denomination'] == "DJELA")
                $dataDjela[] = $value['_total'];
            if ($value['denomination'] == "SUZANG GROUP")
                $dataSuzang[] = $value['_total'];
        }
        //dd(  $dataAppatam,$dataYefien);

        $series = [
            [
                "name" => 'Appatam',
                "data" => $dataAppatam
            ],

            [
                "name" => 'Socopi',
                "data" => $dataSocopi
            ],
            [
                "name" => 'Djela',
                "data" => $dataDjela
            ],
            [
                "name" => 'Yefien',
                "data" => $dataYefien
            ],
            [
                "name" => 'Suzang group',
                "data" => $dataSuzang
            ]


        ];

        /*  dd($this->json($series)); */

        return $this->json(['series' => $series, 'motif' => $dataMotif]);
    }


    #[Route('/demande/woman/man', name: 'app_president_dashboard_demande_woman_man')]
    public function indexWomanVsMan(Request $request, DemandeRepository $demandeRepository)
    {
        $data = $demandeRepository->getAnnee();
        $dataFin = $demandeRepository->getAnneeFin();
        //dd($data);
        $annees = [];
        $anneesFin = [];
        foreach ($data as $value) {
            $annees[$value['dateDebut']] =  $value['dateDebut'];
        }
        foreach ($dataFin as $value) {
            if ($value['dateFin'] != null) {

                $anneesFin[$value['dateFin']] =  $value['dateFin'];
            }
        }
        // dd($anneesFin);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_president_dashboard_demande_woman_man'))
            ->setMethod('POST');

        $formBuilder
            ->add(
                'date',
                ChoiceType::class,
                [
                    'placeholder' => '---',
                    'label' => 'Date',
                    'required'     => false,
                    'expanded'     => false,
                    'attr' => ['class' => 'has-select2 date'],
                    'multiple' => false,
                    'choices'  => array_flip($annees),
                ]
            );


        $form = $formBuilder->getForm();
        return $this->renderForm('president/dashboard/woman_vs_man.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/api/demande/woman/man', name: 'app_president_dashboard_api_demande_woman_man', condition: "request.query.has('filters')")]
    public function apiWomanVsMan(Request $request, DemandeRepository $employeRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $date = $filters['date'];

        $dataDemandeM = [];
        $dataDemandeF = [];
        $dataDemandeAccepteM = [];
        $dataDemandeAccepteF = [];
        $dataDemandeRefuseM = [];
        $dataDemandeRefuseF = [];
        $dataDemandeMotifAutreM = [];
        $dataDemandeMotifAutreF = [];
        $dataDemandeMotifEnfantM = [];
        $dataDemandeMotifEnfantF = [];



        //dd($date,$entreprise);
        $data = $employeRepository->getNombreDemandeMotifParSexe($date);
        $dataNombreDemande = $employeRepository->getNombreDemandeParSexe(null, $date);
        $dataNombreDemandeAccepte = $employeRepository->getNombreDemandeParSexe("demande_valider", $date);
        $dataNombreDemandeRefuse = $employeRepository->getNombreDemandeParSexe("demande_refuser", $date);

        foreach ($data as $key => $value) {
            if ($value['motif'] == "MOT1") {
                if ($value['genre'] == "M") {
                    $dataDemandeMotifEnfantM[] = $value['_total'];
                } else {
                    $dataDemandeMotifEnfantF[] = $value['_total'];
                }
            } else {
                if ($value['genre'] == "M") {
                    $dataDemandeMotifAutreM[] = $value['_total'];
                } else {
                    $dataDemandeMotifAutreF[] = $value['_total'];
                }
            }
        }

        foreach ($dataNombreDemande as $key => $value) {
            if ($value['code'] == "M") {
                $dataDemandeM[] = $value['_total'];
            } else {
                $dataDemandeF[] = $value['_total'];
            }
        }
        if ($dataDemandeM == null) {
            $dataDemandeM[] = 0;
        }
        if ($dataDemandeF == null) {
            $dataDemandeF[] = 0;
        }


        foreach ($dataNombreDemandeAccepte as $key => $value) {
            if ($value['code'] == "M") {
                $dataDemandeAccepteM[] = $value['_total'];
            } else {
                $dataDemandeAccepteF[] = $value['_total'];
            }
        }
        if ($dataDemandeAccepteM == null) {
            $dataDemandeAccepteM[] = 0;
        }
        if ($dataDemandeAccepteF == null) {
            $dataDemandeAccepteF[] = 0;
        }

        foreach ($dataNombreDemandeRefuse as $key => $value) {
            if ($value['code'] == "M") {
                $dataDemandeRefuseM[] = $value['_total'];
            } else {
                $dataDemandeRefuseF[] = $value['_total'];
            }
        }
        if ($dataDemandeRefuseM == null) {
            $dataDemandeRefuseM[] = 0;
        }
        if ($dataDemandeRefuseF == null) {
            $dataDemandeRefuseF[] = 0;
        }

        if ($dataDemandeMotifAutreF == null) {
            $dataDemandeMotifAutreF[] = 0;
        }
        if ($dataDemandeMotifAutreM == null) {
            $dataDemandeMotifAutreM[] = 0;
        }
        if ($dataDemandeMotifEnfantM == null) {
            $dataDemandeMotifEnfantM[] = 0;
        }
        if ($dataDemandeMotifEnfantF == null) {
            $dataDemandeMotifEnfantF[] = 0;
        }


        $series = [
            [
                "name" => 'Autre',
                "data" => [
                    $dataDemandeMotifAutreF[0],
                    $dataDemandeMotifAutreM[0]
                ],

            ],
            [

                "name" => 'Enfant malade',
                "data" => [
                    $dataDemandeMotifEnfantF[0],
                    $dataDemandeMotifEnfantM[0]
                ],

            ],

            [
                "name" => 'Nombre demandes réfusées',
                "data" => [
                    $dataDemandeRefuseF[0],
                    $dataDemandeAccepteM[0]
                ],
            ],
            [
                "name" => 'Demandes acceptées',
                "data" => [
                    $dataDemandeAccepteF[0],
                    $dataDemandeAccepteM[0]
                ],
            ],
            [
                "name" => 'Nombre demandes',
                "data" => [
                    $dataDemandeF[0],
                    $dataDemandeM[0]
                ],
            ]


        ];

        /*  dd($this->json($series)); */

        return $this->json(['series' => $series]);
    }

    #[Route('/classement/nombre/entreprise', name: 'app_president_dashboard_classement_par_nombre_entreprise')]
    public function indexClassement(Request $request, EmployeRepository $employeRepository)
    {



        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_president_dashboard_classement_par_nombre_entreprise'))
            ->setMethod('POST');

        if ($this->security->getUser()->getGroupe()->getName() == "Présidents") {
            $formBuilder->add('entreprise', EntityType::class, [
                'placeholder' => '---',
                'choice_label' => 'denomination',
                'label' => 'Selectionner une entreprise',
                'attr' => ['class' => 'has-select2'],
                'choice_attr' => function (Entreprise $entreprise) {
                    return ['data-value' => $entreprise->getDenomination()];
                },
                'class' => Entreprise::class,
                'required' => false
            ]);
        } else {
            $formBuilder->add('entreprise', EntityType::class, [
                'placeholder' => '---',
                'choice_label' => 'denomination',
                'label' => 'Selectionner une entreprise',
                'attr' => ['class' => 'has-select2'],
                'choice_attr' => function (Entreprise $entreprise) {
                    return ['data-value' => $entreprise->getDenomination()];
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->innerJoin('e.employes', 'em')
                        ->innerJoin('em.utilisateur', 'u')
                        ->andWhere('u =:user')
                        ->setParameter('user', $this->security->getUser());
                },
                'class' => Entreprise::class,
                'required' => false
            ]);
        }


        $form = $formBuilder->getForm();


        return $this->renderForm('president/dashboard/classement_par_nombre_demande.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/api/demande/classement/nombre', name: 'app_president_dashboard_api_demande_classement_nombre', condition: "request.query.has('filters')")]
    public function apiClassement(Request $request, DemandeRepository $employeRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        $entreprise = $filters['entreprise'];
        $categorie = [
            "categorie1" => '0-4',
            "categorie2" => '5-19',
            "categorie3" => '20-29',
            "categorie4" => '30-39',
            "categorie5" => '40-49',
            "categorie6" => '50+',
        ];
        $categorie1 = [];
        $categorie2 = [];
        $categorie3 = [];
        $categorie4 = [];
        $categorie5 = [];
        $categorie6 = [];

        $data = $employeRepository->getDemandeParNombre($entreprise);
        $total = 0;
        foreach ($data as $key => $value) {
            if (in_array($value['nbre_jour'], [0, 1, 2, 3, 4])) {

                $categorie1[] = $value['_total'];
            } elseif (in_array($value['nbre_jour'], [5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19])) {


                $categorie2[] = $value['_total'];
            } elseif (in_array($value['nbre_jour'], [20, 21, 22, 23, 24, 25, 26, 27, 28, 29])) {
                $categorie3[] = $value['_total'];
            } elseif (in_array($value['nbre_jour'], [30, 31, 32, 33, 34, 35, 36, 37, 38, 39])) {
                $categorie4[] = $value['_total'];
            } elseif (in_array($value['nbre_jour'], [40, 41, 42, 43, 44, 45, 46, 47, 48, 49])) {
                $categorie5[] = $value['_total'];
            } else {
                $categorie6[] = $value['_total'];
            }
        }

        if ($categorie1 == null) {
            $categorie1[] = 0;
        } elseif ($categorie2 == null) {
            $categorie2[] = 0;
        } elseif ($categorie3 == null) {
            $categorie3[] = 0;
        }

        if ($categorie4 == null) {
            $categorie4[] = 0;
        }
        if ($categorie5 == null) {
            $categorie5[] = 0;
        } else {
            $categorie6[] = 0;
        }
        /* dd(
  
    array_sum($categorie1), 
   

); */


        $series =  [
            "data" => [
                array_sum($categorie1),
                array_sum($categorie2),
                array_sum($categorie3),
                array_sum($categorie4),
                array_sum($categorie5),
                array_sum($categorie6),
            ],
            "name" => 'Cases',
            "showInLegend" => false,
        ];
        //dd($this->json([$series]));


        return $this->json(['series' => $series, 'categorie' => $categorie]);
    }
}
