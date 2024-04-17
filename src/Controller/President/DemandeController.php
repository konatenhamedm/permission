<?php

namespace App\Controller\President;

use App\Entity\Demande;
use App\Form\DemandeType;
use App\Repository\DemandeRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\BoolColumn;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;
use App\Form\DemandeWorkflowType;
use App\Repository\MotifRepository;
use LogicException;

#[Route('/president/demande')]
class DemandeController extends BaseController
{
    const INDEX_ROOT_NAME = "app_president_demande_index";

    #[Route('/{etat}/{entreprise}/liste', name: 'app_president_demande_index', methods: ['GET', 'POST'])]
    public function index(Request $request, string $etat, string $entreprise, DataTableFactory $dataTableFactory): Response
    {
        if ($etat == 'demande_valider_directeur') {

            $titre = "Demandes en attente d'approbation du président";
        } elseif ($etat == 'demande_valider_attente_document') {

            $titre = "Demandes en attente de validation documents";
        } elseif ($etat == 'document_soumis_directeur') {

            $titre = "Demandes en attente de vérification de document pour la  clôture";
        } elseif ($etat == 'demande_valider') {

            $titre = "Demandes acceptées";
        } elseif ($etat == 'demande_refuser') {

            $titre = "Demandes réfusées";
        }


        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);

        $table = $dataTableFactory->create()
            ->add('dateDebut', DateTimeColumn::class, ['label' => 'Date debut', 'format' => 'd-m-Y', "searchable" => true, 'globalSearchable' => true])
            ->add('dateFin', DateTimeColumn::class, ['label' => 'Date fin', 'format' => 'd-m-Y'])
            ->add('nom', TextColumn::class, ['label' => 'Nom', 'field' => 'e.nom'])
            ->add('entreprise', TextColumn::class, ['label' => 'Entreprise', 'field' => 'en.denomination'])
            ->add('prenom', TextColumn::class, ['label' => 'Prenoms', 'field' => 'e.prenom'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Demande::class,
                'query' => function (QueryBuilder $qb)  use ($etat, $entreprise) {
                    $qb->select('d,u, e, f, en')
                        ->from(Demande::class, 'd')
                        ->join('d.utilisateur', 'u')
                        ->join('u.employe', 'e')
                        ->join('e.entreprise', 'en')
                        ->orderBy('d.dateCreation', 'ASC')
                        ->join('e.fonction', 'f')
                        ->andWhere('en.denomination =:entreprise')
                        ->setParameter('entreprise', $entreprise);

                    if ($etat == 'demande_initie') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_initie");
                    } elseif ($etat == 'demande_valider_directeur') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider_directeur");
                    } elseif ($etat == 'demande_valider_attente_document') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider_attente_document");
                    } elseif ($etat == 'document_soumis_directeur') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "document_soumis_directeur");
                    } elseif ($etat == 'demande_valider') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_valider");
                    } elseif ($etat == 'demande_refuser') {
                        $qb->andWhere("d.etat =:etat")
                            ->setParameter('etat', "demande_refuser");
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
            ->setName('dt_app_president_demande' . $etat . $entreprise);

        if ($permission != null) {

            $renders = [



                'validation_president' =>  new ActionRender(function () use ($permission, $etat) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return false;
                    } elseif ($permission == 'RU') {
                        return $etat == 'demande_valider_directeur';
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return $etat == 'demande_valider_directeur';
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return $etat == 'demande_valider_directeur';
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
                    return true;
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
                            'target' => '#exampleModalSizeLg2',

                            'actions' => [

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
        }

        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }


        return $this->render('president/demande/index.html.twig', [
            'datatable' => $table,
            'permition' => $permission,
            'titre' => $titre,
            'etat' => $etat,
            'entreprise' => $entreprise
        ]);
    }

    #[Route('/{id}/edit/workflow_president', name: 'app_demande_demande_edit_workflow_president', methods: ['GET', 'POST'])]
    public function editWorkflowPresident(Request $request, Demande $demande, DemandeRepository $demandeRepository, MotifRepository $repository, FormError $formError): Response
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
                if ($form->getClickedButton()->getName() === 'accepatation_president') {
                    try {

                        if ($workflow->can($demande, 'accepatation_president')) {
                            $workflow->apply($demande, 'accepatation_president');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demandeRepository->save($demande, true);
                } elseif ($form->getClickedButton()->getName() === 'refuser_president') {
                    try {

                        if ($workflow->can($demande, 'demande_refuser_president')) {
                            $workflow->apply($demande, 'demande_refuser_president');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demandeRepository->save($demande, true);
                } elseif ($form->getClickedButton()->getName() == 'accepatation_president_attente_document') {
                    if ($workflow->can($demande, 'accepatation_president_attente_document')) {
                        $workflow->apply($demande, 'accepatation_president_attente_document');
                        $this->em->flush();
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

        return $this->renderForm('demande/demande/edit_workflow_president_test.html.twig', [
            'demande' => $demande,
            /*  'element' => $repository->findOneBySomeField($demande)[0], */
            'form' => $form,
        ]);
    }
}
