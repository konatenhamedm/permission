<?php

namespace App\Controller\Directeur;

use App\Entity\DemandeBrouillon;
use App\Form\DemandeBrouillonType;
use App\Repository\DemandeBrouillonRepository;
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
use Doctrine\ORM\QueryBuilder;

#[Route('/directeur/demande/brouillon')]
class DemandeBrouillonController extends BaseController
{
    const INDEX_ROOT_NAME = 'app_directeur_demande_brouillon_index';

    #[Route('/{etat}', name: 'app_directeur_demande_brouillon_index', methods: ['GET', 'POST'])]
    public function index(Request $request,string $etat, DataTableFactory $dataTableFactory): Response
    {


        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);

        $table = $dataTableFactory->create()
            ->add('dateDebut', DateTimeColumn::class, ['label' => 'Date debut'])
            ->add('dateFin', DateTimeColumn::class, ['label' => 'Date fin'])
            ->add('nom', TextColumn::class, ['label' => 'Utilisateur', 'field' => 'e.getNomComplet'])
            ->createAdapter(ORMAdapter::class, [
                'entity' => DemandeBrouillon::class,
                'query' => function(QueryBuilder $qb)  use ($etat) {
                    $qb->select('d,u, e, f, en')
                        ->from(DemandeBrouillon::class, 'd')
                        ->join('d.utilisateur', 'u')
                        ->join('u.employe', 'e')
                        ->join('e.entreprise', 'en')
                        ->orderBy('d.dateCreation','ASC')
                        ->join('e.fonction', 'f') 
                        ->andWhere('e.entreprise =:entreprise')
                        ->andWhere('d.etat =:etat')
                        ->setParameter('entreprise',$this->security->getUser()->getEmploye()->getEntreprise())
                        ->setParameter('etat',$etat);
                    
                    }
                
            ])
            ->setName('dt_app_directeur_demande_brouillon'.$etat);
        if ($permission != null) {

            $renders = [
                'edit' =>  new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return false;
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return true;
                    }
                }),
                'delete' => new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return true;
                    } elseif ($permission == 'RU') {
                        return false;
                    } elseif ($permission == 'RUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return false;
                    } elseif ($permission == 'CR') {
                        return false;
                    } else {
                        return true;
                    }
                }),
                'show' => new ActionRender(function () use ($permission) {
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
                    'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, DemandeBrouillon $context) use ($renders) {
                        $options = [
                            'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                            'target' => '#exampleModalSizeLg2',

                            'actions' => [
                                'edit' => [
                                    'url' => $this->generateUrl('app_directeur_demande_brouillon_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-default'], 'render' => $renders['edit']
                                ],
                                'show' => [
                                    'url' => $this->generateUrl('app_directeur_demande_brouillon_show', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-eye', 'attrs' => ['class' => 'btn-primary'], 'render' => $renders['show']
                                ],
                                'delete' => [
                                    'target' => '#exampleModalSizeNormal',
                                    'url' => $this->generateUrl('app_directeur_demande_brouillon_delete', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-trash', 'attrs' => ['class' => 'btn-main'],  'render' => $renders['delete']
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


        return $this->render('directeur/demande_brouillon/index.html.twig', [
            'datatable' => $table,
            'permition' => $permission,
            'etat' => $etat,
        ]);
    }

    #[Route('/new', name: 'app_directeur_demande_brouillon_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DemandeBrouillonRepository $demandeBrouillonRepository, FormError $formError): Response
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
           // $redirect = $this->generateUrl('app_directeur_demande_brouillon_index');


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

    #[Route('/{id}/show', name: 'app_directeur_demande_brouillon_show', methods: ['GET'])]
    public function show(DemandeBrouillon $demandeBrouillon): Response
    {
        return $this->render('directeur/demande_brouillon/show.html.twig', [
            'demande_brouillon' => $demandeBrouillon,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_directeur_demande_brouillon_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DemandeBrouillon $demandeBrouillon, DemandeBrouillonRepository $demandeBrouillonRepository, FormError $formError): Response
    {

        $form = $this->createForm(DemandeBrouillonType::class, $demandeBrouillon, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_directeur_demande_brouillon_edit', [
                'id' =>  $demandeBrouillon->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_directeur_demande_brouillon_index');


            if ($form->isValid()) {

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
                    return $this->redirect($redirect, Response::HTTP_OK);
                }
            }
        }

        return $this->renderForm('directeur/demande_brouillon/edit.html.twig', [
            'demande_brouillon' => $demandeBrouillon,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_directeur_demande_brouillon_delete', methods: ['DELETE', 'GET'])]
    public function delete(Request $request, DemandeBrouillon $demandeBrouillon, DemandeBrouillonRepository $demandeBrouillonRepository): Response
    {
        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'app_directeur_demande_brouillon_delete',
                    [
                        'id' => $demandeBrouillon->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = true;
            $demandeBrouillonRepository->remove($demandeBrouillon, true);

            $redirect = $this->generateUrl('app_directeur_demande_brouillon_index');

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

        return $this->renderForm('directeur/demande_brouillon/delete.html.twig', [
            'demande_brouillon' => $demandeBrouillon,
            'form' => $form,
        ]);
    }
}
