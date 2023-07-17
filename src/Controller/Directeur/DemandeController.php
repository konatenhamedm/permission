<?php

namespace App\Controller\Directeur;

use App\Entity\Demande;
use App\Form\Demande1Type;
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
use App\Form\DemandeFormAlerteType;
use App\Repository\FichierRepository;
use App\Repository\MotifRepository;
use Doctrine\ORM\QueryBuilder;

#[Route('/directeur/demande')]
class DemandeController extends BaseController
{
    const INDEX_ROOT_NAME = 'app_directeur_demande_index';

    #[Route('/{etat}/avis', name: 'app_directeur_demande_index', methods: ['GET', 'POST'])]
    public function index(Request $request, string $etat, DataTableFactory $dataTableFactory): Response
    {  if ($etat == 'demande_initie') {

        $titre ="Demandes initiées";
    }elseif($etat == 'demande_valider_directeur') {

        $titre ="Demandes en attente d'approbation du président";
    } elseif($etat == 'demande_valider_attente_document') {

        $titre ="Demandes en attente de documents";
    }
    elseif($etat == 'document_enregistre') {

        $titre ="Demandes dont le demandeur à soumis le document";
    }
    elseif($etat == 'demande_valider') {

        $titre ="Demandes acceptées";
    }
    elseif($etat == 'demande_refuser') {

        $titre ="Demandes réfusées";
    }
//dd($this->security->getUser()->getEmploye()->getEntreprise());

        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);

        $table = $dataTableFactory->create()
            ->add('dateDebut', DateTimeColumn::class, ['label' => 'Date debut','format' => 'd-m-Y',"searchable"=>true,'globalSearchable'=>true])
            ->add('dateFin', DateTimeColumn::class, ['label' => 'Date fin','format' => 'd-m-Y'])
            ->add('nom', TextColumn::class, ['label' => 'Nom', 'field' => 'e.nom'])
            ->add('entreprise', TextColumn::class, ['label' => 'Entreprise', 'field' => 'en.denomination'])
            ->add('prenom', TextColumn::class, ['label' => 'Prenoms', 'field' => 'e.prenom'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Demande::class,
                'query' => function(QueryBuilder $qb)  use ($etat) {
                    $qb->select('d,u, e, f, en')
                        ->from(Demande::class, 'd')
                        ->join('d.utilisateur', 'u')
                        ->join('u.employe', 'e')
                        ->join('e.entreprise', 'en')
                        ->orderBy('d.dateCreation','ASC')
                        ->join('e.fonction', 'f') 
                        ->andWhere('e.entreprise =:entreprise')
                        ->setParameter('entreprise',$this->security->getUser()->getEmploye()->getEntreprise());
                     
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
               
            ])
            ->setName('dt_app_directeur_demande_'.$etat);

            $renders = [
                'edit' =>  new ActionRender(function () use ($permission,$etat) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return false;
                    } elseif ($permission == 'RU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CRUD') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CRU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return $etat == 'document_enregistre';
                    }
                }),
                'edit_document' =>  new ActionRender(function () use ($permission,$etat) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return false;
                    } elseif ($permission == 'RU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CRUD') {
                        return $etat == 'demande_valider_attente_document';
                    } elseif ($permission == 'CRU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return $etat == 'document_enregistre';
                    }
                }),
                'validation_directeur' => new ActionRender(function () use ($permission,$etat) {
                    if ($permission == 'R') {
                        return true;
                    } elseif ($permission == 'RD') {
                        return $etat == 'demande_initie';
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'CRUD') {
                        return $etat == 'demande_initie';
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return true;
                    } else {
                        return $etat == 'demande_initie';
                    }
                }),
                'shows' => new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return true;
                    } elseif ($permission == 'RD') {
                        return true;
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return true;
                    } else {
                        return true;
                    }
                    
                }),
                'verification' => new ActionRender(function () use ($permission,$etat) {
                    if ($permission == 'R') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'RD') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'RU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'RUD') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CRU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CR') {
                        return $etat == 'document_enregistre';
                    } else {
                        return $etat == 'document_enregistre';
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
                , 'render' => function ($value, Demande $context) use ($renders) {
                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#exampleModalSizeSm2',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_document', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-pen'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => $renders['edit']
                            ],
                            'edit_document' => [
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_verification', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-pen'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => $renders['edit_document']
                            ],
                            'validation_directeur' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_directeur', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-pen'
                                , 'attrs' => ['class' => 'btn-main']
                                ,  'render' => $renders['validation_directeur']
                            ],
                            'shows' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_show_president', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-eye'
                                , 'attrs' => ['class' => 'btn-default']
                                ,  'render' => $renders['shows']
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

        return $this->render('directeur/demande/index.html.twig', [
            'datatable' => $table,
            'etat' => $etat,
            'titre' => $titre,
            'permition' => $permission,
        ]);
    }


    #[Route('/{etat}/demande', name: 'app_directeur_demande_demande_index', methods: ['GET', 'POST'])]
    public function indexDemande(Request $request, string $etat, DataTableFactory $dataTableFactory): Response
    {  if ($etat == 'demande_initie') {

        $titre ="Demandes initiées";
    }elseif($etat == 'demande_valider_directeur') {

        $titre ="Demandes en attente d'approbation du président";
    } elseif($etat == 'demande_valider_attente_document') {

        $titre ="Demandes en attente de documents";
    }
    elseif($etat == 'document_enregistre') {

        $titre ="Demandes dont le demandeur à soumis le document";
    }
    elseif($etat == 'demande_valider') {

        $titre ="Demandes acceptées";
    }
    elseif($etat == 'demande_refuser') {

        $titre ="Demandes réfusées";
    }
//dd($this->security->getUser()->getEmploye()->getEntreprise());

        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);

        $table = $dataTableFactory->create()
            ->add('dateDebut', DateTimeColumn::class, ['label' => 'Date debut','format' => 'd-m-Y',"searchable"=>true,'globalSearchable'=>true])
            ->add('dateFin', DateTimeColumn::class, ['label' => 'Date fin','format' => 'd-m-Y'])
            ->add('nom', TextColumn::class, ['label' => 'Nom', 'field' => 'e.nom'])
            ->add('entreprise', TextColumn::class, ['label' => 'Entreprise', 'field' => 'en.denomination'])
            ->add('prenom', TextColumn::class, ['label' => 'Prenoms', 'field' => 'e.prenom'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Demande::class,
                'query' => function(QueryBuilder $qb)  use ($etat) {
                    $qb->select('d,u, e, f, en')
                        ->from(Demande::class, 'd')
                        ->join('d.utilisateur', 'u')
                        ->join('u.employe', 'e')
                        ->join('e.entreprise', 'en')
                        ->orderBy('d.dateCreation','ASC')
                        ->join('e.fonction', 'f') 
                        ->andWhere('u =:user')
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
                    elseif($etat == 'demande_refuser') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider");
                    }

                }
               
            ])
            ->setName('dt_app_directeur_demande_'.$etat);

            $renders = [
                'edit' =>  new ActionRender(function () use ($permission,$etat) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return false;
                    } elseif ($permission == 'RU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CRUD') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CRU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return $etat == 'document_enregistre';
                    }
                }),
                'edit_document' =>  new ActionRender(function () use ($permission,$etat) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return false;
                    } elseif ($permission == 'RU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CRUD') {
                        return $etat == 'demande_valider_attente_document';
                    } elseif ($permission == 'CRU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return $etat == 'document_enregistre';
                    }
                }),
                'validation_directeur' => new ActionRender(function () use ($permission,$etat) {
                    if ($permission == 'R') {
                        return true;
                    } elseif ($permission == 'RD') {
                        return $etat == 'demande_initie';
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'CRUD') {
                        return $etat == 'demande_initie';
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return true;
                    } else {
                        return $etat == 'demande_initie';
                    }
                }),
                'shows' => new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return true;
                    } elseif ($permission == 'RD') {
                        return true;
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return true;
                    } else {
                        return true;
                    }
                    
                }),
                'verification' => new ActionRender(function () use ($permission,$etat) {
                    if ($permission == 'R') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'RD') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'RU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'RUD') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CRU') {
                        return $etat == 'document_enregistre';
                    } elseif ($permission == 'CR') {
                        return $etat == 'document_enregistre';
                    } else {
                        return $etat == 'document_enregistre';
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
                , 'render' => function ($value, Demande $context) use ($renders) {
                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#exampleModalSizeSm2',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_document', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-pen'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => $renders['edit']
                            ],
                            'edit_document' => [
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
                            'shows' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_show_president', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% bi bi-eye'
                                , 'attrs' => ['class' => 'btn-default']
                                ,  'render' => $renders['shows']
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

        return $this->render('directeur/demande/index.html.twig', [
            'datatable' => $table,
            'etat' => $etat,
            'titre' => $titre,
            'permition' => $permission,
        ]);
    }


    #[Route('/{id}/rejeter', name: 'app_demande_demande_rejeter', methods: ['GET', 'POST'])]
    public function Rejeter(Request $request,FichierRepository $fichierRepository, Demande $demande, DemandeRepository $demandeRepository,MotifRepository $repository, FormError $formError): Response
    {
        //dd();
        $form = $this->createForm(DemandeFormAlerteType::class, $demande, [
            'method' => 'POST',
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::UPLOAD_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl('app_demande_demande_rejeter', [
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
               //dd($workflow->can($demande,'document_verification_refuse'));
                if($workflow->can($demande,'document_verification_refuse')){
                    $workflow->apply($demande, 'document_verification_refuse');
                    $this->em->flush();
                }
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

        return $this->renderForm('directeur/demande/edit.html.twig', [
            'demande' => $demande,
            'fichiers'=>$repository->findOneBySomeFields($demande),
            'form' => $form,
        ]);
    }
}
