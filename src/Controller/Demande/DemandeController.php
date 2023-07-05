<?php

namespace App\Controller\Demande;

use App\Controller\FileTrait;
use App\Entity\Demande;
use App\Entity\Utilisateur;
use App\Form\DemandeType;
use App\Form\DemandeWorkflowType;
use App\Repository\DemandeRepository;
use App\Repository\FichierRepository;
use App\Repository\MotifRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\Registry;

#[Route('/demande/demande')]
class DemandeController extends AbstractController
{

    use FileTrait;

    private const UPLOAD_PATH = 'demande';
    private $security;
    private $workflow;
    private $em;


    public function __construct(Security $security,Registry $workflow,EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->workflow = $workflow;
        $this->em = $em;
    }




    #[Route('/', name: 'app_demande_demande_index', methods: ['GET', 'POST'])]
    public function index(Request $request, DataTableFactory $dataTableFactory): Response
    {//dd($this->security->getUser()->getGroupe()->getName());
        $etats = ['demande_initie' => 'Approbation directeur',
            'demande_valider_directeur' => 'Approbation président',
            'demande_valider_attente_document' => 'Documents',
            'document_enregistre' => 'Vérification Document',
            'demande_valider' => 'Demandes traitées',
            'demande_refuser' => 'Demandes réfusées',

        ];
        $tabs = [];
        foreach ($etats as $etat => $label) {
            $tabs[] =  [
                'name' => $etat,
                'label' => $label,
                'url' => $this->generateUrl('app_demande_demande_ls', ['etat' => $etat])
            ];
        }


        return $this->render('demande/demande/index.html.twig', [
            'tabs' => $tabs,
            'titre'=>"Liste des demandes"
        ]);
    }

    #[Route('/avis', name: 'app_demande_demande_index_avis', methods: ['GET', 'POST'])]
    public function indexavis(Request $request, DataTableFactory $dataTableFactory): Response
    {
//dd($this->security->getUser()->getGroupe()->getName());
        if($this->security->getUser()->getGroupe()->getName() == "Présidents"){
            $etats = [
                'demande_valider_directeur' => 'Attente approbation',
                'demande_valider_attente_document' => 'Documents',
                'document_enregistre' => 'Vérification Document',
                'demande_valider' => 'Demandes validées',
                'demande_refuser' => 'Demandes réfusées',

            ];
        }else{
            $etats = ['demande_initie' => 'Attente approbation',
                'demande_valider_attente_document' => 'Documents',
                'document_enregistre' => 'Vérification Document',
                'demande_valider' => 'Demandes validées',
                'demande_refuser' => 'Demandes réfusées',

            ];
        }

        $tabs = [];
        foreach ($etats as $etat => $label) {
            $tabs[] =  [
                'name' => $etat,
                'label' => $label,
                'url' => $this->generateUrl('app_demande_demande_avis_ls', ['etat' => $etat])
            ];
        }


        return $this->render('demande/demande/index_avis.html.twig', [
            'tabs' => $tabs,
            'titre'=>"Liste des demandes"
        ]);
    }
    #[Route('/{etat}/liste/avis', name: 'app_demande_demande_avis_ls', methods: ['GET', 'POST'])]
    public function listeavis(Request $request, string $etat, DataTableFactory $dataTableFactory): Response
    {

        $table = $dataTableFactory->create()
            ->add('dateDebut', DateTimeColumn::class, ['label' => 'Date debut','format' => 'd-m-Y',"searchable"=>true,'globalSearchable'=>true])
            ->add('dateFin', DateTimeColumn::class, ['label' => 'Date fin','format' => 'd-m-Y'])
            ->add('nom', TextColumn::class, ['label' => 'Nom', 'field' => 'e.nom'])
            ->add('prenom', TextColumn::class, ['label' => 'Prenoms', 'field' => 'e.prenom'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Demande::class,
                'query' => function(QueryBuilder $qb)  use ($etat) {
                    $qb->select('d,u, e, f')
                        ->from(Demande::class, 'd')
                        ->join('d.utilisateur', 'u')
                        ->join('u.employe', 'e')
                        ->orderBy('d.dateCreation','ASC')
                        ->join('e.fonction', 'f');
                       /* ->andWhere('e.entreprise =:user')
                        ->setParameter('user',$this->security->getUser()->getEmploye()->getEntreprise());*/

                    if($this->security->getUser()->getGroupe()->getName() != "Présidents"){
                        $qb->andWhere('e.entreprise =:user')
                            ->setParameter('user',$this->security->getUser()->getEmploye()->getEntreprise());
                    }


                    if ($etat == 'demande_initie') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_initie");
                    } elseif($etat == 'demande_valider_directeur') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider_directeur");
                    }
                    elseif($etat == 'demande_valider_attente_document') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider_attente_document");
                    }
                    elseif($etat == 'document_enregistre') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "document_enregistre");
                    }
                    elseif($etat == 'demande_valider') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider");
                    }
                    elseif($etat == 'demande_refuser') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider");
                    }

                }
                /*->add('numero', TextColumn::class, ['label' => 'Numéro', 'className' => 'w-100px'])
                ->add('libelle', TextColumn::class, ['label' => 'Libellé'])
                ->createAdapter(ORMAdapter::class, [
                    'entity' => Offre::class,
                    'query' => function (QueryBuilder $qb) use ($etat) {
                        $qb->select('offre')
                            ->from(Offre::class, 'offre');


                        if ($etat == 'non_attribue') {
                            $qb->andWhere('offre.attribue = 0');
                        } else {
                            $qb->andWhere('offre.attribue = 1');
                        }

                    }*/
            ])
            ->setName('dt_app_demande_demande_avis_'.$etat);

        if($this->security->getUser()->getGroupe()->getName() == "Présidents"){
            $renders = [
                'edit' =>  new ActionRender(fn() => $etat == false),
                'validation_directeur' =>  new ActionRender(fn() => false),
                'validation_president' =>  new ActionRender(fn() => $etat == 'demande_valider_directeur' ),
                'show' =>  new ActionRender(fn() => $etat == true),
                /*'show' =>  new ActionRender(function () {
                    return true;
                }),*/

            ];
        }else{
            $renders = [
                'edit' =>  new ActionRender(fn() => $etat == 'document_enregistre'),
                'validation_directeur' =>  new ActionRender(fn() => $etat == 'demande_initie'),
                'validation_president' =>  new ActionRender(fn() => false),
                //'imprimer' =>  new ActionRender(fn() => $etat == 'demande_livrer'),
                'show' =>  new ActionRender(function () {
                    return true;
                }),
                'verification' => new ActionRender(fn() => $etat == 'document_enregistre'),
            ];
        }
        /*$renders = [
            'edit' =>  new ActionRender(fn() => $etat == 'demande_initie'),
            'validation_directeur' =>  new ActionRender(fn() => $etat == 'demande_valider_directeur'),
            'validation_president' =>  new ActionRender(fn() => $etat == 'demande_valider_president'),
            //'imprimer' =>  new ActionRender(fn() => $etat == 'demande_livrer'),
            'show' =>  new ActionRender(function () {
                return true;
            }),
        ];*/



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
                , 'render' => function ($value, Demande $context) use ($renders) {
                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#exampleModalSizeSm2',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_verification', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-pen'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => $renders['edit']
                            ],
                            'validation_directeur' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_directeur', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-pen'
                                , 'attrs' => ['class' => 'btn-main']
                                ,  'render' => $renders['validation_directeur']
                            ],
                            'validation_president' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_president', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-pen'
                                , 'attrs' => ['class' => 'btn-main']
                                ,  'render' => $renders['validation_president']
                            ],
                             
                            'show' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-eye'
                                , 'attrs' => ['class' => 'btn-default']
                                ,  'render' => $renders['show']
                            ]
                        ]

                    ];
                    return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                }
            ]);
        }


        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }




        return $this->render('demande/demande/liste_avis.html.twig', [
            'datatable' => $table,
            'etat' => $etat
        ]);
    }

    #[Route('/{etat}/liste', name: 'app_demande_demande_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $etat, DataTableFactory $dataTableFactory): Response
    {
        $table = $dataTableFactory->create()
            ->add('dateDebut', DateTimeColumn::class, ['label' => 'Date debut','format' => 'd-m-Y',"searchable"=>true,'globalSearchable'=>true])
            ->add('dateFin', DateTimeColumn::class, ['label' => 'Date fin','format' => 'd-m-Y'])
            ->add('nom', TextColumn::class, ['label' => 'Nom', 'field' => 'e.nom'])
            ->add('prenom', TextColumn::class, ['label' => 'Prenoms', 'field' => 'e.prenom'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Demande::class,
                'query' => function(QueryBuilder $qb)  use ($etat) {
                    $qb->select('d,u, e, f')
                        ->from(Demande::class, 'd')
                        ->join('d.utilisateur', 'u')
                        ->join('u.employe', 'e')
                        ->join('e.fonction', 'f')
                        ->andWhere('d.utilisateur =:user')
                        ->orderBy('d.dateCreation','ASC')
                        ->setParameter('user',$this->security->getUser());

                    if ($etat == 'demande_initie') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_initie");
                    } elseif($etat == 'demande_valider_directeur') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider_directeur");
                    }
                    elseif($etat == 'demande_valider_attente_document') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider_attente_document");
                    }
                    elseif($etat == 'document_enregistre') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "document_enregistre");
                    }
                    elseif($etat == 'demande_valider') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider");
                    }

                }
            /*->add('numero', TextColumn::class, ['label' => 'Numéro', 'className' => 'w-100px'])
            ->add('libelle', TextColumn::class, ['label' => 'Libellé'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Offre::class,
                'query' => function (QueryBuilder $qb) use ($etat) {
                    $qb->select('offre')
                        ->from(Offre::class, 'offre');


                    if ($etat == 'non_attribue') {
                        $qb->andWhere('offre.attribue = 0');
                    } else {
                        $qb->andWhere('offre.attribue = 1');
                    }

                }*/
            ])
            ->setName('dt_app_demande_demande_'.$etat);

        $renders = [
            'edit' =>  new ActionRender(function () {
                return true;
            }),
            'show' =>  new ActionRender(function () {
                return true;
            }),
            'delete' => new ActionRender(function () {
                return true;
            }),
            'document' =>  new ActionRender(fn() => $etat == 'demande_valider_attente_document'),

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
                , 'render' => function ($value, Demande $context) use ($renders) {
                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#exampleModalSizeSm2',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('app_demande_demande_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-pen'
                                , 'attrs' => ['class' => 'btn-default']
                                , 'render' => $renders['edit']
                            ],
                            'delete' => [
                                'target' => '#exampleModalSizeNormal',
                                'url' => $this->generateUrl('app_demande_demande_delete', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-trash'
                                , 'attrs' => ['class' => 'btn-main']
                                ,  'render' => $renders['delete']
                            ],
                            'document' => [
                                'target' => '#exampleModalSizeLg2',
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_document', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-file-binary'
                                , 'attrs' => ['class' => 'btn-main']
                                ,  'render' => $renders['document']
                            ],

                            'show' => [
                                'target' => '#exampleModalSizeNormal',
                                'url' => $this->generateUrl('app_demande_demande_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-eye'
                                , 'attrs' => ['class' => 'btn-main']
                                ,  'render' => $renders['show']
                            ]
                        ]

                    ];
                    return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                }
            ]);
        }


        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }




        return $this->render('demande/demande/liste.html.twig', [
            'datatable' => $table,
            'etat' => $etat
        ]);
    }

    #[Route('/new', name: 'app_demande_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DemandeRepository $demandeRepository, FormError $formError): Response
    {
        $demande = new Demande();
        $date = new DateTime();
        $rest = $date->modify('+1 day');
        $demande->setDateDebut($rest);
        $form = $this->createForm(DemandeType::class, $demande, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('app_demande_demande_new')
        ]);

        $form->handleRequest($request);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_demande_demande_index');




            if ($form->isValid()) {
                $demande->setDateCreation(new \DateTime());
                $demande->setEtat("demande_initie");
                $demandeRepository->save($demande, true);
                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                if (!$isAjax) {
                  $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }


        }

        return $this->renderForm('demande/demande/new.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_demande_demande_show', methods: ['GET'])]
    public function show(Request $request,Demande $demande): Response
    {
        $form = $this->createForm(DemandeWorkflowType::class, $demande, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_demande_demande_show', [
                'id' =>  $demande->getId()
            ])
        ]);


        return $this->renderForm('demande/demande/show.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_demande_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, DemandeRepository $demandeRepository, FormError $formError): Response
    {

        $form = $this->createForm(DemandeType::class, $demande, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_demande_demande_edit', [
                    'id' =>  $demande->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_demande_demande_index');


            if ($form->isValid()) {

                $demandeRepository->save($demande, true);
                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                if (!$isAjax) {
                  $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('demande/demande/edit.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit/workflow', name: 'app_demande_demande_edit_workflow', methods: ['GET', 'POST'])]
    public function editWorkflow(Request $request, Demande $demande, DemandeRepository $demandeRepository, FormError $formError): Response
    {

        $form = $this->createForm(DemandeWorkflowType::class, $demande, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_demande_demande_edit_workflow', [
                'id' =>  $demande->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_demande_demande_index_avis');
            $workflow = $this->workflow->get($demande, 'demande');

            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'passer'){
                    try {

                        if ($workflow->can($demande,'passer')){
                            $workflow->apply($demande, 'passer');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeRepository->save($demande, true);
                }elseif($form->getClickedButton()->getName() === 'refuser'){
                    try {

                        if ($workflow->can($demande,'demande_refuser')){
                            $workflow->apply($demande, 'demande_refuser');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeRepository->save($demande, true);
                }
                else{
                    $demandeRepository->save($demande, true);
                }
                //$demandeRepository->save($demande, true);
                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('demande/demande/edit_workflow_directeur.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }
    #[Route('/{id}/edit/workflow_directeur', name: 'app_demande_demande_edit_workflow_directeur', methods: ['GET', 'POST'])]
    public function editWorkflowDirecteur(Request $request, Demande $demande, DemandeRepository $demandeRepository, FormError $formError): Response
    {

        $form = $this->createForm(DemandeWorkflowType::class, $demande, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_demande_demande_edit_workflow_directeur', [
                'id' =>  $demande->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_demande_demande_index_avis');
            $workflow = $this->workflow->get($demande, 'demande');
            //dd($form->getClickedButton()->getName());
            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'validation'){
                    try {

                        if ($workflow->can($demande,'passer')){
                            $workflow->apply($demande, 'passer');
                            $this->em->flush();
                        }elseif($workflow->can($demande,'validation')){
                            $workflow->apply($demande, 'validation');
                            $this->em->flush();
                        }elseif($workflow->can($demande,'accepatation_president')){
                            $workflow->apply($demande, 'accepatation_president');
                            $this->em->flush();
                        }
                        elseif($workflow->can($demande,'accepatation_president_attente_document')){
                            $workflow->apply($demande, 'accepatation_president_attente_document');
                            $this->em->flush();
                        }
                        elseif($workflow->can($demande,'document_enregister')){
                            $workflow->apply($demande, 'document_enregister');
                            $this->em->flush();
                        }

                        elseif($workflow->can($demande,'document_verification_accepte')){
                            $workflow->apply($demande, 'document_verification_accepte');
                            $this->em->flush();
                        }
                        elseif($workflow->can($demande,'document_verification_refuse')){
                            $workflow->apply($demande, 'document_verification_refuse');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeRepository->save($demande, true);
                }else{
                    $demandeRepository->save($demande, true);
                }
                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('demande/demande/edit_workflow_directeur.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit/workflow_president', name: 'app_demande_demande_edit_workflow_president', methods: ['GET', 'POST'])]
    public function editWorkflowPresident(Request $request, Demande $demande, DemandeRepository $demandeRepository,MotifRepository $repository, FormError $formError): Response
    {
/*dd();*/
        $form = $this->createForm(DemandeWorkflowType::class, $demande, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_demande_demande_edit_workflow_president', [
                'id' =>  $demande->getId()
            ])
        ]);
    //    dd($form->getData());

        /*foreach ($form->getData()->getAvis()-as $el){

            $demande->
        }*/

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_demande_demande_index_avis');
            $workflow = $this->workflow->get($demande, 'demande');

            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'accepatation_president'){
                    try {

                     if($workflow->can($demande,'accepatation_president')){
                            $workflow->apply($demande, 'accepatation_president');
                            $this->em->flush();
                        }

                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demandeRepository->save($demande, true);
                }elseif($form->getClickedButton()->getName() === 'refuser_president'){
                    try {

                        if($workflow->can($demande,'demande_refuser_president')){
                               $workflow->apply($demande, 'demande_refuser_president');
                               $this->em->flush();
                           }
   
                       } catch (LogicException $e) {
   
                           $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                       }
   
                       $demandeRepository->save($demande, true);
                }
                elseif($form->getClickedButton()->getName()== 'accepatation_president_attente_document'){
                    if($workflow->can($demande,'accepatation_president_attente_document')){
                        $workflow->apply($demande, 'accepatation_president_attente_document');
                        $this->em->flush();
                    }
                    $demandeRepository->save($demande, true);
                }

                else{
                    $demandeRepository->save($demande, true);
                   // return $this->redirect('app_demande_demande_index_avis');
                }
                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('demande/demande/edit_workflow_president.html.twig', [
            'demande' => $demande,
            'element' => $repository->findOneBySomeField($demande)[0],
            'form' => $form,
        ]);
    }


    #[Route('/{id}/edit/workflow_document', name: 'app_demande_demande_edit_workflow_document', methods: ['GET', 'POST'])]
    public function editWorkflowDocument(Request $request, Demande $demande, DemandeRepository $demandeRepository,MotifRepository $repository, FormError $formError): Response
    {
        /*dd();*/
        $form = $this->createForm(DemandeWorkflowType::class, $demande, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('app_demande_demande_edit_workflow_document', [
                'id' =>  $demande->getId()
            ])
        ]);
        //    dd($form->getData());

        /*foreach ($form->getData()->getAvis()-as $el){

            $demande->
        }*/

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_demande_demande_index');
            $workflow = $this->workflow->get($demande, 'demande');

            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'document_enregister'){
                    try {

                        if($workflow->can($demande,'document_enregister')){
                            $workflow->apply($demande, 'document_enregister');
                            $this->em->flush();
                        }

                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demandeRepository->save($demande, true);
                }

                else{
                    $demandeRepository->save($demande, true);
                    // return $this->redirect('app_demande_demande_index_avis');
                }
                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('demande/demande/edit_document.html.twig', [
            'demande' => $demande,
            'element' => $repository->findOneBySomeField($demande)[0],
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit/workflow_verification', name: 'app_demande_demande_edit_workflow_verification', methods: ['GET', 'POST'])]
    public function editWorkflowVerification(Request $request,FichierRepository $fichierRepository, Demande $demande, DemandeRepository $demandeRepository,MotifRepository $repository, FormError $formError): Response
    {
        //dd();
        $form = $this->createForm(DemandeWorkflowType::class, $demande, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('app_demande_demande_edit_workflow_verification', [
                'id' =>  $demande->getId()
            ])
        ]);
        //    dd($form->getData());

        /*foreach ($form->getData()->getAvis()-as $el){

            $demande->
        }*/

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_demande_demande_index');
            $workflow = $this->workflow->get($demande, 'demande');

            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'document_verification_accepte'){
                    try {

                        if($workflow->can($demande,'document_verification_accepte')){
                            $workflow->apply($demande, 'document_verification_accepte');
                            $this->em->flush();
                        }

                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demandeRepository->save($demande, true);
                }elseif ($form->getClickedButton()->getName() === 'document_verification_refuse'){
                    try {

                        if($workflow->can($demande,'document_verification_refuse')){
                            $workflow->apply($demande, 'document_verification_refuse');
                            $this->em->flush();
                        }

                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demandeRepository->save($demande, true);
                }

                else{
                    $demandeRepository->save($demande, true);
                    // return $this->redirect('app_demande_demande_index_avis');
                }
                $data = true;
                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);


            } else {
                $message = $formError->all($form);
                $statut = 0;
                $statutCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }

            }


            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('demande/demande/edit_verification.html.twig', [
            'demande' => $demande,
            'fichiers'=>$repository->findOneBySomeFields($demande),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_demande_demande_delete', methods: ['DELETE', 'GET'])]
    public function delete(Request $request, Demande $demande, DemandeRepository $demandeRepository): Response
    {
        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                'app_demande_demande_delete'
                ,   [
                        'id' => $demande->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
        ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = true;
            $demandeRepository->remove($demande, true);

            $redirect = $this->generateUrl('app_demande_demande_index');

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut'   => 1,
                'message'  => $message,
                'redirect' => $redirect,
                'data' => $data
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }
        }

        return $this->renderForm('demande/demande/delete.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }
}
