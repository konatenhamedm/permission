<?php

namespace App\Controller\Demande;

use App\Controller\BaseController;
use App\Entity\Demande;
use App\Entity\Motif;
use App\Form\DemandeType;
use App\Form\DemandeWorkflowType;
use App\Repository\AvisRepository;
use App\Repository\DemandeRepository;
use App\Repository\ElementMotifRepository;
use App\Repository\FichierRepository;
use App\Repository\MotifRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/demande/demande/simple')]
class DemandeSimpleController extends BaseController
{

    #[Route('/{etat}/liste/avis', name: 'app_demande_demande_avis_ls', methods: ['GET', 'POST'])]
    public function listeavis(Request $request, string $etat, DataTableFactory $dataTableFactory): Response
    {
        if ($etat == 'demande_initie') {

            $titre = "Demande en attente approbation";
        } elseif ($etat == 'demande_valider_directeur') {

            $titre = "Demandes en attente d'approbation du président";
        } elseif ($etat == 'demande_valider_attente_document') {

            $titre = "Demande en attente de validation de documents";
        } elseif ($etat == 'document_enregistre') {


            $titre = "Demande en attente de document pour être clôturée";
        } elseif ($etat == 'demande_valider') {

            $titre = "Demandes acceptées";
        } elseif ($etat == 'demande_refuser') {

            $titre = "Demandes réfusées";
        }
        $table = $dataTableFactory->create()
            ->add('dateCreation', DateTimeColumn::class, ['label' => 'Date demande', 'format' => 'd-m-Y', "searchable" => true, 'globalSearchable' => true])
            ->add('dateDebut', DateTimeColumn::class, ['label' => 'Date debut', 'format' => 'd-m-Y', "searchable" => true, 'globalSearchable' => true])
            /* ->add('dateFin', DateTimeColumn::class, ['label' => 'Date fin', 'format' => 'd-m-Y']) */
            ->add('nom', TextColumn::class, ['label' => 'Nom', 'field' => 'e.nom'])
            ->add('entreprise', TextColumn::class, ['label' => 'Entreprise', 'field' => 'en.denomination'])
            ->add('prenom', TextColumn::class, ['label' => 'Prenoms', 'field' => 'e.prenom'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Demande::class,
                'query' => function (QueryBuilder $qb)  use ($etat) {
                    $qb->select('d,u, e, f, en')
                        ->from(Demande::class, 'd')
                        ->join('d.utilisateur', 'u')
                        ->join('u.employe', 'e')
                        ->join('e.entreprise', 'en')
                        ->orderBy('d.dateCreation', 'ASC')
                        ->join('e.fonction', 'f');
                    /* ->andWhere('e.entreprise =:user')
                        ->setParameter('user',$this->security->getUser()->getEmploye()->getEntreprise());*/

                    if ($this->security->getUser()->getGroupe()->getName() != "Présidents") {
                        $qb->andWhere('e.entreprise =:user')
                            ->setParameter('user', $this->security->getUser()->getEmploye()->getEntreprise());
                    }

                    if ($etat == 'demande_valider_directeur') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider_directeur");
                    }
                    if ($etat == 'demande_initie') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_initie");
                    } elseif ($etat == 'demande_valider_directeur') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider_directeur");
                    } elseif ($etat == 'demande_valider_attente_document') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider_attente_document");
                    } elseif ($etat == 'document_enregistre') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "document_enregistre");
                    } elseif ($etat == 'demande_valider') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider");
                    } elseif ($etat == 'demande_refuser') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider");
                    }
                }

            ])
            ->setName('dt_app_demande_demande_avis_' . $etat);

        if ($this->security->getUser()->getGroupe()->getName() == "Présidents") {
            $renders = [
                'edit' =>  new ActionRender(fn () => $etat == false),
                'validation_directeur' =>  new ActionRender(fn () => false),
                'validation_president' =>  new ActionRender(fn () => $etat == 'demande_valider_directeur'),

                /*  'show' =>  new ActionRender(function () {
                    return true;
                }), */
                'show' =>  new ActionRender(function () {
                    return false;
                }),

            ];
        } else {
            $renders = [
                'edit' =>  new ActionRender(fn () => $etat == 'demande_valider_attente_document'),
                'validation_directeur' =>  new ActionRender(fn () => $etat == 'demande_initie'),
                'validation_president' =>  new ActionRender(fn () => false),
                //'imprimer' =>  new ActionRender(fn() => $etat == 'demande_livrer'),
                /*  'show' =>  new ActionRender(function () {
                    return true;
                }), */
                'shows' =>  new ActionRender(function () use ($etat) {
                    return  true;
                }),
                //'verification' => new ActionRender(fn () => $etat == 'document_enregistre'),
                'verification' => new ActionRender(fn () => $etat == 'demande_valider_attente_document'),
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
                'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, Demande $context) use ($renders) {
                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#exampleModalSizeSm2',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_verification', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-success'], 'render' => $renders['edit']
                            ],
                            'validation_directeur' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_directeur', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-main'],  'render' => $renders['validation_directeur']
                            ],
                            'validation_president' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_president', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-main'],  'render' => $renders['validation_president']
                            ],

                            /* 'show' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-eye'
                                , 'attrs' => ['class' => 'btn-default']
                                ,  'render' => $renders['show']
                            ], */
                            'shows' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_show_president', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-eye', 'attrs' => ['class' => 'btn-default'],  'render' => $renders['shows']
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
            'etat' => $etat,
            'titre' => $titre
        ]);
    }

    #[Route('/avis', name: 'app_demande_demande_index_avis', methods: ['GET', 'POST'])]
    public function indexavis(Request $request, DataTableFactory $dataTableFactory): Response
    {
        //dd($this->security->getUser()->getGroupe()->getName());
        if ($this->security->getUser()->getGroupe()->getName() == "Présidents") {
            $etats = [
                'demande_valider_directeur' => 'Attente approbation',
                'demande_valider_attente_document' => 'Documents',
                'document_enregistre' => 'Vérification Document',
                'demande_valider' => 'Demandes validées',
                'demande_refuser' => 'Demandes réfusées',

            ];
        } else {
            $etats = [
                'demande_initie' => 'Attente approbation',
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
            'titre' => "Liste des demandes"
        ]);
    }



    #[Route('/{etat}', name: 'app_demande_demande_index', methods: ['GET', 'POST'])]
    public function index(Request $request, string $etat, DataTableFactory $dataTableFactory): Response
    {

        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), "app_demande_demande_index");

        $table = $dataTableFactory->create()
            ->add('dateDebut', DateTimeColumn::class, ['label' => 'Date debut', 'format' => 'd-m-Y', "searchable" => true, 'globalSearchable' => true])
            ->add('dateFin', DateTimeColumn::class, ['label' => 'Date fin', 'format' => 'd-m-Y'])
            ->add('nom', TextColumn::class, ['label' => 'Nom', 'field' => 'e.nom'])
            ->add('prenom', TextColumn::class, ['label' => 'Prenoms', 'field' => 'e.prenom'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Demande::class,
                'query' => function (QueryBuilder $qb)  use ($etat) {
                    $qb->select('d,u, e, f')
                        ->from(Demande::class, 'd')
                        ->join('d.utilisateur', 'u')
                        ->join('u.employe', 'e')
                        ->join('e.fonction', 'f')
                        ->andWhere('d.utilisateur =:user')
                        ->orderBy('d.dateCreation', 'ASC')
                        ->setParameter('user', $this->security->getUser());

                    if ($etat == 'demande_initie') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_initie");
                    } elseif ($etat == 'demande_valider_directeur') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider_directeur");
                    } elseif ($etat == 'demande_valider_attente_document') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider_attente_document");
                    } elseif ($etat == 'document_enregistre') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "document_enregistre");
                    } elseif ($etat == 'demande_valider') {
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
            ->setName('dt_app_demande_demande_' . $etat);

        $renders = [
            'edit' =>  new ActionRender(function () use ($etat) {
                return $etat != 'demande_valider_directeur';
            }),
            'show' =>  new ActionRender(function () use ($etat) {
                return true;
            }),
            'delete' => new ActionRender(function () use ($etat) {
                return $etat != 'demande_valider_directeur';
            }),
            'document' =>  new ActionRender(fn () => ($etat != 'demande_valider_directeur' && $etat == 'demande_valider_attente_document')),

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
                'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, Demande $context) use ($renders) {
                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#exampleModalSizeSm2',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('app_demande_demande_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-default', 'tile' => 'yes'], 'render' => $renders['edit']
                            ],
                            'delete' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_delete', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-trash', 'attrs' => ['class' => 'btn-main'],  'render' => $renders['delete']
                            ],
                            'document' => [
                                'target' => '#exampleModalSizeLg2',
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_document', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-file-binary', 'attrs' => ['class' => 'btn-main'],  'render' => $renders['document']
                            ],

                            'show' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_show', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-eye', 'attrs' => ['class' => 'btn-main'],  'render' => $renders['show']
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




        return $this->render('demande/demande/index.html.twig', [
            'datatable' => $table,
            'etat' => $etat,
            'titre' => 'Demande',
            'permition' => $permission,
        ]);
    }


    #[Route('/{id}/show', name: 'app_demande_demande_show', methods: ['GET'])]
    public function show(Request $request, Demande $demande): Response
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
    #[Route('/{id}/show/president', name: 'app_demande_demande_show_president', methods: ['GET'])]
    public function showPresident(Request $request, Demande $demande, MotifRepository $motifRepository): Response
    {
        $form = $this->createForm(DemandeWorkflowType::class, $demande, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_demande_demande_show', [
                'id' =>  $demande->getId()
            ])
        ]);


        if ($this->security->getUser()->getGroupe()->getName() == "Présidents") {
            $template = 'show_president';
        } elseif ($this->security->getUser()->getGroupe()->getName() == "Collaborateurs") {
            $template = 'show_collaborateur';
        } else {
            $template = 'show_directeur';
        }

        return $this->renderForm('demande/demande/shows/' . $template . '.html.twig', [
            'demande' => $demande,
            'form' => $form,
            'documents' => $motifRepository->getDocumentCourrier($demande),
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
                return $this->json(compact('statut', 'message', 'redirect', 'data'), $statutCode);
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
                if ($form->getClickedButton()->getName() === 'passer') {
                    try {

                        if ($workflow->can($demande, 'passer')) {
                            $workflow->apply($demande, 'passer');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeRepository->save($demande, true);
                } elseif ($form->getClickedButton()->getName() === 'refuser') {
                    try {

                        if ($workflow->can($demande, 'demande_refuser')) {
                            $workflow->apply($demande, 'demande_refuser');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeRepository->save($demande, true);
                } else {
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
                return $this->json(compact('statut', 'message', 'redirect', 'data'), $statutCode);
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
                if ($form->getClickedButton()->getName() === 'passer') {
                    try {

                        if ($workflow->can($demande, 'passer')) {
                            $workflow->apply($demande, 'passer');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeRepository->save($demande, true);
                } elseif ($form->getClickedButton()->getName() === 'refuser') {
                    try {

                        if ($workflow->can($demande, 'demande_refuser')) {
                            $workflow->apply($demande, 'demande_refuser');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeRepository->save($demande, true);
                } else {
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
                return $this->json(compact('statut', 'message', 'redirect', 'data'), $statutCode);
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

    //firsttttttttttttttttttttttttttttttt

    #[Route('/{id}/edit/workflow_document', name: 'app_demande_demande_edit_workflow_document', methods: ['GET', 'POST'])]
    public function editWorkflowDocument(Request $request, Demande $demande, DemandeRepository $demandeRepository, MotifRepository $repository, FormError $formError): Response
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
            $redirect = $this->generateUrl('app_config_demande_index');
            $workflow = $this->workflow->get($demande, 'demande');

            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'document_enregister') {
                    try {

                        if ($workflow->can($demande, 'document_enregister')) {
                            $workflow->apply($demande, 'document_enregister');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demandeRepository->save($demande, true);
                } else {
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
                return $this->json(compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('demande/demande/edit_document.html.twig', [
            'demande' => $demande,
            /* 'element' => $repository->findOneBySomeField($demande)[0], */
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit/workflow_verification', name: 'app_demande_demande_edit_workflow_verification', methods: ['GET', 'POST'])]
    public function editWorkflowVerification(Request $request, FichierRepository $fichierRepository, Demande $demande, DemandeRepository $demandeRepository, MotifRepository $repository, FormError $formError): Response
    {
        //dd('ghgg');
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
            $redirect = $this->generateUrl('app_config_demande_index');
            $workflow = $this->workflow->get($demande, 'demande');

            if ($form->isValid()) {


                if ($form->getClickedButton()->getName() === 'document_enregister') {
                    dd('ghgg');
                    if ($workflow->can($demande, 'document_enregister')) {
                        $workflow->apply($demande, 'document_enregister');
                        $this->em->flush();
                    }
                }
                if ($form->getClickedButton()->getName() === 'document_verification_accepte') {
                    try {

                        if ($workflow->can($demande, 'document_verification_accepte')) {
                            $workflow->apply($demande, 'document_verification_accepte');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demandeRepository->save($demande, true);
                } elseif ($form->getClickedButton()->getName() === 'document_verification_refuse') {
                    try {

                        if ($workflow->can($demande, 'document_verification_refuse')) {
                            $workflow->apply($demande, 'document_verification_refuse');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demandeRepository->save($demande, true);
                } else {
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
                return $this->json(compact('statut', 'message', 'redirect', 'data'), $statutCode);
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('demande/demande/edit_verification.html.twig', [
            'demande' => $demande,
            'fichiers' => $repository->findOneBySomeFields($demande),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_demande_demande_delete', methods: ['DELETE', 'GET'])]
    public function delete(Request $request, Demande $demande, DemandeRepository $demandeRepository): Response
    {
        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'app_demande_demande_delete',
                    [
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
