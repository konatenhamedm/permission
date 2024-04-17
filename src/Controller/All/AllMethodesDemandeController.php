<?php

namespace App\Controller\All;

use App\Controller\BaseController;
use App\Entity\Demande;
use App\Entity\DemandeBrouillon;
use App\Entity\Motif;
use App\Form\DemandeBrouillonType;
use App\Form\DemandeType;
use App\Repository\AvisRepository;
use App\Repository\DemandeBrouillonRepository;
use App\Repository\DemandeRepository;
use App\Repository\ElementMotifRepository;
use App\Service\ActionRender;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FormError;
use App\Service\Omines\Adapter\ORMAdapter;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;

#[Route('/all/methodes')]
class AllMethodesDemandeController extends BaseController
{
    #[Route('/new', name: 'app_demande_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DemandeRepository $demandeRepository, ElementMotifRepository $elementRepository, AvisRepository $avisRepository, FormError $formError): Response
    {

        //dd("ee");
        $demande = new Demande();
        $date = new DateTime();
        $rest = $date->modify('+1 day');

        $motif = new Motif();
        $motif->setElement($elementRepository->find("MOT1"));
        $demande->addMotif($motif);

        $demande->setDateDebut($rest);
        $demande->setDateFin(new DateTime());
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
            $redirect = $this->generateUrl('app_config_demande_index');



            if ($form->isValid()) {
                $demande->setDateCreation(new \DateTime());
                if ($this->security->getUser()->getGroupe()->getName() == "Directeurs") {
                    $demande->setEtat("demande_valider_directeur");
                    $demande->setAvis($avisRepository->findOneBy(array('code' => 'AV1')));
                    $demande->setJustificationDirecteur('Demande permission directeur');
                } else {
                    $demande->setEtat("demande_initie");
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
                return $this->json(compact('statut', 'message', 'redirect', 'data'), $statutCode);
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

    #[Route('/brouillon/new', name: 'app_directeur_demande_brouillon_new', methods: ['GET', 'POST'])]
    public function newBrouillon(Request $request, DemandeBrouillonRepository $demandeBrouillonRepository, FormError $formError): Response
    {
        $demandeBrouillon = new DemandeBrouillon();
        $form = $this->createForm(DemandeBrouillonType::class, $demandeBrouillon, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_directeur_demande_brouillon_new')
        ]);
        $form->handleRequest($request);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_brouillon_directeur_index');


            if ($form->isValid()) {

                $demandeBrouillon->setEtat('brouillon_initie');
                $demandeBrouillonRepository->save($demandeBrouillon, true);
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
                    return null;
                }
            }
        }

        return $this->renderForm('directeur/demande_brouillon/new.html.twig', [
            'demande_brouillon' => $demandeBrouillon,
            'form' => $form,
        ]);
    }

    #[Route('/{etat}/demande', name: 'app_directeur_demande_demande_dupliquer_index', methods: ['GET', 'POST'])]
    public function indexDemande(Request $request, string $etat, DataTableFactory $dataTableFactory): Response
    {
        if ($etat == 'demande_initie') {

            $titre = "Demandes initiées";
        } elseif ($etat == 'demande_valider_directeur') {

            $titre = "Demandes en attente d'approbation du président";
        } elseif ($etat == 'demande_valider_attente_document') {

            $titre = "Demandes en attente de documents";
        } elseif ($etat == 'document_enregistre') {

            $titre = "Demandes en attente de documents pour la clôture";
        } elseif ($etat == 'demande_valider') {

            $titre = "Demandes acceptées";
        } elseif ($etat == 'demande_refuser') {

            $titre = "Demandes réfusées";
        }
        //dd($this->security->getUser()->getEmploye()->getEntreprise());

        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), "app_directeur_demande_demande_index");
        // dd(';jjkj');
        $table = $dataTableFactory->create()
            /*   ->add('dateDebut', DateTimeColumn::class, ['label' => 'Date debut', 'format' => 'd-m-Y', "searchable" => true, 'globalSearchable' => true])
            ->add('dateFin', DateTimeColumn::class, ['label' => 'Date fin', 'format' => 'd-m-Y']) 
            ->add('nom', TextColumn::class, ['label' => 'Nom', 'field' => 'e.nom'])
            ->add('entreprise', TextColumn::class, ['label' => 'Entreprise', 'field' => 'en.denomination'])
            ->add('prenom', TextColumn::class, ['label' => 'Prenoms', 'field' => 'e.prenom'])*/
            ->createAdapter(ORMAdapter::class, [
                'entity' => Demande::class,
                'query' => function (QueryBuilder $qb)  use ($etat) {
                    $qb->select('d,u, e, f, en')
                        ->from(Demande::class, 'd')
                        ->join('d.utilisateur', 'u')
                        ->join('u.employe', 'e')
                        ->join('e.entreprise', 'en')
                        ->orderBy('d.dateCreation', 'ASC')
                        ->join('e.fonction', 'f')
                        ->andWhere('u =:user')
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
                    } elseif ($etat == 'demande_refuser') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_refuser");
                    }
                }

            ])
            ->setName('dt_app_directeur_demande_' . $etat);

        $renders = [
            'edit' =>  new ActionRender(function () use ($permission, $etat) {
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
            'edit_document' =>  new ActionRender(function () use ($permission, $etat) {
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
            'validation_directeur' => new ActionRender(function () use ($permission, $etat) {
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
            'verification' => new ActionRender(function () use ($permission, $etat) {
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
                'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, Demande $context) use ($renders) {
                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#exampleModalSizeSm2',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_document', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-success'], 'render' => $renders['edit']
                            ],
                            'edit_document' => [
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_verification', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-success'], 'render' => $renders['edit_document']
                            ],
                            'validation_directeur' => [
                                'target' => '#exampleModalSizeSm2',
                                'url' => $this->generateUrl('app_demande_demande_edit_workflow_directeur', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-main'],  'render' => $renders['validation_directeur']
                            ],
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

        return $this->render('directeur/demande/index.html.twig', [
            'datatable' => $table,
            'etat' => $etat,
            'titre' => $titre,
            'permition' => $permission,
            'type' => "demande"
        ]);
    }
}
