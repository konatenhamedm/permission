<?php

namespace App\Controller\Directeur;

use App\Entity\Demande;
use App\Repository\DemandeRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;
use App\Entity\Employe;
use App\Form\DemandeFormAlerteType;
use App\Repository\EmployeRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\FichierRepository;
use App\Repository\MotifRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

#[Route('/directeur/infos')]
class InformationsEmployeController extends BaseController
{
    const INDEX_ROOT_NAME = 'app_utilisateur_employe_infos_index';




    #[Route('/{entreprise}', name: 'app_utilisateur_employe_infos_index', methods: ['GET', 'POST'])]
    public function index(Request $request,string $entreprise, DataTableFactory $dataTableFactory): Response
    {
        $groupeName = $this->security->getUser()->getGroupe()->getName();
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(),self::INDEX_ROOT_NAME);

        $table = $dataTableFactory->create()
        ->add('matricule', TextColumn::class, ['label' => 'Matricule'])
        ->add('civilite', TextColumn::class, ['field' => 'civilite.code', 'label' => 'Civilité'])
        ->add('nom', TextColumn::class, ['label' => 'Nom'])
        ->add('prenom', TextColumn::class, ['label' => 'Prénoms'])
        ->add('adresseMail', TextColumn::class, ['label' => 'Email'])
        ->add('fonction', TextColumn::class, ['field' => 'fonction.libelle', 'label' => 'Fonction'])
        ->createAdapter(ORMAdapter::class, [
            'entity' => Employe::class,
            'query' => function(QueryBuilder $qb) use ($entreprise){
                $qb->select('e, civilite, fonction')
                    ->from(Employe::class, 'e')
                    ->join('e.civilite', 'civilite')
                    ->join('e.fonction', 'fonction')
                    ->join('e.entreprise', 'entreprise')
                    /*->andWhere('entreprise.code = :entreprise')*/

                ;
                if($this->security->getUser()->getGroupe()->getName() != "Présidents"){
                    $qb->andWhere('entreprise.code = :entreprise')
                        ->setParameter('user',$this->security->getUser()->getEmploye()->getEntreprise()->getCode());
                }else{
                    $qb->andWhere('entreprise.code = :entreprise')
                        ->setParameter('entreprise',$entreprise);
                }
                
            }
        ])
        ->setName('dt_app_utilisateur_employe_infos'.$entreprise);
        if($permission != null){
            $renders = [
                'stat_general' =>  new ActionRender(function () use ($permission) {
                    if($permission == 'R'){
                        return true;
                    }elseif($permission == 'RD'){
                        return true;
                    }elseif($permission == 'RU'){
                        return true;
                    }elseif($permission == 'RUD'){
                        return true;
                    }elseif($permission == 'CRU'){
                        return true;
                    }
                    elseif($permission == 'CR'){
                        return true;
                    }else{
                        return true;
                    }

                }),
                'stat_simple' => new ActionRender(function () use ($permission) {
                    if($permission == 'R'){
                        return true;
                    }elseif($permission == 'RD'){
                        return true;
                    }elseif($permission == 'RU'){
                        return true;
                    }elseif($permission == 'RUD'){
                        return true;
                    }elseif($permission == 'CRU'){
                        return true;
                    }
                    elseif($permission == 'CR'){
                        return true;
                    }else{
                        return true;
                    }
                }),
          

            ];

            $hasActions = false;

            foreach ($renders as $_ => $cb) {
                if ($cb->execute()) {
                    $hasActions = true;
                    break;
                }
            }

            if ($hasActions) {
                $table->add('id', TextColumn::class, [
                    'label' => 'Actions'
                    , 'orderable' => false
                    ,'globalSearchable' => false
                    ,'className' => 'grid_row_actions'
                    , 'render' => function ($value, Employe $context) use ($renders) {
                        $options = [
                            'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                            'target' => '#exampleModalSizeLg2',

                            'actions' => [
                              /*   'stat_general' => [
                                    'target'=>'#exampleModalSizeSm2',
                                    'url' => $this->generateUrl('app_utilisateur_employe_infos_stats_generales', ['id' => $value])
                                    , 'ajax' => true
                                    , 'icon' => '%icon% bi bi-eye'
                                    , 'attrs' => ['class' => 'btn-default']
                                    , 'render' => $renders['stat_general']
                                ], */
                                'stat_simple' => [
                                    'target'=>'#exampleModalSizeSm2',
                                    'url' => $this->generateUrl('app_utilisateur_employe_infos_stats_simples', ['id' => $value])
                                    , 'ajax' => true
                                    , 'icon' => '%icon% bi bi-eye'
                                    , 'attrs' => ['class' => 'btn-primary']
                                    , 'render' => $renders['stat_simple']
                                ],
                              
                            ]

                        ];
                        return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                    }
                ]);
            }
        }


        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

       if($groupeName == "Présidents"){
        return $this->render('directeur/demande/infos/index_president.html.twig', [
                    'datatable' => $table,
                    'permition' => $permission,
                   'entreprise'=>$entreprise,
                   
                ]);
       }else{
        return $this->render('directeur/demande/infos/index_directeur.html.twig', [
            'datatable' => $table,
            'permition' => $permission,
            
        ]);
       }
       
    }


    #[Route('/', name: 'app_utilisateur_employe_infos_directeur_index', methods: ['GET', 'POST'])]
    public function indexDirecteur(Request $request, DataTableFactory $dataTableFactory): Response
    {
        $groupeName = $this->security->getUser()->getGroupe()->getName();
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(),self::INDEX_ROOT_NAME);

        $table = $dataTableFactory->create()
            ->add('matricule', TextColumn::class, ['label' => 'Matricule'])
            ->add('civilite', TextColumn::class, ['field' => 'civilite.code', 'label' => 'Civilité'])
            ->add('nom', TextColumn::class, ['label' => 'Nom'])
            ->add('prenom', TextColumn::class, ['label' => 'Prénoms'])
            ->add('adresseMail', TextColumn::class, ['label' => 'Email'])
            ->add('fonction', TextColumn::class, ['field' => 'fonction.libelle', 'label' => 'Fonction'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Employe::class,
                'query' => function(QueryBuilder $qb) {
                    $qb->select('e, civilite, fonction')
                        ->from(Employe::class, 'e')
                        ->join('e.civilite', 'civilite')
                        ->join('e.fonction', 'fonction')
                        ->join('e.entreprise', 'entreprise')
                        /*->andWhere('entreprise.code = :entreprise')*/

                    ;
                    if($this->security->getUser()->getGroupe()->getName() != "Présidents"){
                        $qb->andWhere('entreprise.code = :entreprise')
                            ->setParameter('entreprise',$this->security->getUser()->getEmploye()->getEntreprise()->getCode());
                   
                        }

                }
            ])
            ->setName('dt_app_utilisateur_employe_infos_directeur');
        if($permission != null){
            $renders = [
                'stat_general' =>  new ActionRender(function () use ($permission) {
                    if($permission == 'R'){
                        return true;
                    }elseif($permission == 'RD'){
                        return true;
                    }elseif($permission == 'RU'){
                        return true;
                    }elseif($permission == 'RUD'){
                        return true;
                    }elseif($permission == 'CRU'){
                        return true;
                    }
                    elseif($permission == 'CR'){
                        return true;
                    }else{
                        return true;
                    }

                }),
                'stat_simple' => new ActionRender(function () use ($permission) {
                    if($permission == 'R'){
                        return true;
                    }elseif($permission == 'RD'){
                        return true;
                    }elseif($permission == 'RU'){
                        return true;
                    }elseif($permission == 'RUD'){
                        return true;
                    }elseif($permission == 'CRU'){
                        return true;
                    }
                    elseif($permission == 'CR'){
                        return true;
                    }else{
                        return true;
                    }
                }),


            ];

            $hasActions = false;

            foreach ($renders as $_ => $cb) {
                if ($cb->execute()) {
                    $hasActions = true;
                    break;
                }
            }

            if ($hasActions) {
                $table->add('id', TextColumn::class, [
                    'label' => 'Actions'
                    , 'orderable' => false
                    ,'globalSearchable' => false
                    ,'className' => 'grid_row_actions'
                    , 'render' => function ($value, Employe $context) use ($renders) {
                        $options = [
                            'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                            'target' => '#exampleModalSizeLg2',

                            'actions' => [
                                /*   'stat_general' => [
                                      'target'=>'#exampleModalSizeSm2',
                                      'url' => $this->generateUrl('app_utilisateur_employe_infos_stats_generales', ['id' => $value])
                                      , 'ajax' => true
                                      , 'icon' => '%icon% bi bi-eye'
                                      , 'attrs' => ['class' => 'btn-default']
                                      , 'render' => $renders['stat_general']
                                  ], */
                                'stat_simple' => [
                                    'target'=>'#exampleModalSizeSm2',
                                    'url' => $this->generateUrl('app_utilisateur_employe_infos_stats_simples', ['id' => $value])
                                    , 'ajax' => true
                                    , 'icon' => '%icon% bi bi-eye'
                                    , 'attrs' => ['class' => 'btn-primary']
                                    , 'render' => $renders['stat_simple']
                                ],

                            ]

                        ];
                        return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                    }
                ]);
            }
        }


        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        if($groupeName == "Présidents"){
            return $this->render('directeur/demande/infos/index_president.html.twig', [
                'datatable' => $table,
                'permition' => $permission,
                //'entreprise'=>$entreprise,

            ]);
        }else{
            return $this->render('directeur/demande/infos/index_directeur.html.twig', [
                'datatable' => $table,
                'permition' => $permission,

            ]);
        }

    }

    #[Route('/{id}/generale', name: 'app_utilisateur_employe_infos_stats_generales', methods: ['GET'])]
    public function statsGenerales(Employe $employe,DemandeRepository $demandeRepository): Response
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

        return $this->renderForm('directeur/demande/infos/stats_generale.html.twig', [
            'form' => $form,
            'employe'=>$employe
        ]);
    }

    #[Route('/{id}/simple', name: 'app_utilisateur_employe_infos_stats_simples', methods: ['GET'])]
    public function statsSimple(Employe $employe,DemandeRepository $demandeRepository): Response
    {
        $data = $demandeRepository->getAnnee();
        $mois = $demandeRepository->getMois();
      // dd($data);
        $annees = [];
        $dataMois = [];
        foreach ($data as $value) {
            $annees[$value['dateDebut']] =  $value['dateDebut'];
        }
        
        foreach ($mois as $value) {
          
                $dataMois[$value['mois']] =  $value['mois'];
          
        }//dd($annees);
        // dd($anneesFin);

        $formBuilder = $this->createFormBuilder()
            ->setAction($this->generateUrl('app_president_dashboard_demande_by_motif_by_entreprise'))
            ->setMethod('POST');

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
            )

            ->add(
                'mois',
                ChoiceType::class,
                [
                    'placeholder' => '---',
                    'label' => 'Mois',
                    'required'     => false,
                    'expanded'     => false,
                    'attr' => ['class' => 'has-select2 date'],
                    'multiple' => false,
                    'choices'  => array_flip($dataMois),
                ]
            );



        $form = $formBuilder->getForm();
        return $this->renderForm('directeur/demande/infos/stats_simple.html.twig', [
            'form' => $form,
            'employe'=>$employe
        ]);
    }



    #[Route('/courbe/detail/employe', name: 'app_president_courbe_details_employe',condition: "request.query.has('filters')")]
    public function apiGetDetailsEmploye(Request $request, EmployeRepository $employeRepository, EntrepriseRepository $entrepriseRepository)
    {
        $all = $request->query->all();
        $filters = $all['filters'] ?? [];
        // $dateDebut = $filters['dateDebut'];
        $date = $filters['date'];
        $employe = $filters['employe'];
        //$mois = $filters['mois'];

        //dd($date,$employe);
        $dataDemande = $employeRepository->getNombreDemandeParMois($date,$employe,null);
        //$dataRefuse = $employeRepository->getNombreDemandeParMois($date,$employe,"demande_refuser");
      // $data = $employeRepository->getNombreDemandeParMois($mois,$employe,"non null");
       //dd($data);

        $moisDemande = [];
        $jour = [];
        $nombreDemande = [];
        $nombreDemandedata = [];
        

        foreach ($dataDemande as $element) {
            $moisDemande[] = $element['mois'];
            $nombreDemande[] = $element['_total'];
        }

       /*  foreach ($data as $element) {
            $jour[] = $element['jour'];
            $nombreDemandedata[] = $element['nbre_jour'];
           
        } */
      
       /*  if($nombreDemandeRefuse == null){
            $nombreDemandeRefuse [] = 0; 
        }
        if($nombreDemandeAccepte == null){
            $nombreDemandeAccepte [] = 0; 
        }
         */
      //dd();
        $seriesDemande = [
            [
                "colorByPoint"=> "true",
                "data" => $nombreDemande,
            ]
        ];

       /*  $seriesDemandeJour= [
            [
                "name" => "Demande par jour du mois",
                "marker" => [
                    "symbol" => 'square'
                ],
                "data" => $nombreDemandedata,
                ]
        ];
 */
      

     
///dd(array_unique(array_merge($moisDemandeA,$moisDemandeR)));
       // dd(array_sum($seriesDemandeAccepte),$employe);
      // dd($this->json(['dataFusion' => $seriesDemandeJour]));


        return $this->json([
            'seriesDemande' => $seriesDemande, 
            //'dataFusion' => $seriesDemandeJour, 
            'jour' =>$jour, 
            'moisDemande' => $moisDemande]);
    }
   

}
