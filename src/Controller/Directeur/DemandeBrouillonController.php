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
use App\Entity\Demande;
use App\Entity\Motif;
use App\Form\DemandeBrouillonFormAlerteType;
use App\Repository\DemandeRepository;
use App\Repository\ElementMotifRepository;
use App\Repository\MotifRepository;
use App\Repository\UtilisateurRepository;
use DateTime;
use Doctrine\ORM\QueryBuilder;
use LogicException;

#[Route('/directeur/demande/brouillon')]
class DemandeBrouillonController extends BaseController
{
    const INDEX_ROOT_NAME = 'app_directeur_demande_brouillon_index';

    #[Route('/{etat}', name: 'app_directeur_demande_brouillon_index', methods: ['GET', 'POST'])]
    public function index(Request $request,string $etat, DataTableFactory $dataTableFactory): Response
    {

        $groupe = $this->groupe;
       
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);
 //dd($permission);
        $table = $dataTableFactory->create()
            ->add('dateDebut', DateTimeColumn::class, ['label' => 'Date debut' ,'format' => 'd-m-Y'])
            ->add('dateFin', DateTimeColumn::class, ['label' => 'Date fin' ,'format' => 'd-m-Y'])
            ->add('nom', TextColumn::class, ['label' => 'Utilisateur', 'field' => 'e.getNomComplet'])
            ->add('entreprise', TextColumn::class, ['label' => 'Entreprise', 'field' => 'en.denomination'])
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
                        ->andWhere('d.etat =:etat')
                        ->setParameter('etat',$etat)
                       ;

                        if($this->groupe =="Directeur"){
                            $qb->andWhere('e.entreprise =:entreprise')
                            ->setParameter('entreprise',$this->security->getUser()->getEmploye()->getEntreprise());
                        
                        }
                    
                    }
                
            ])
            ->setName('dt_app_directeur_demande_brouillon'.$etat);
        if ($permission != null) {

            $renders = [
                'edit' =>  new ActionRender(function () use ($permission,$groupe,$etat) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return false;
                    } elseif ($permission == 'RU' && ($groupe == "Directeurs") && ($etat =="brouillon_initie" )) {
                        return true;
                    } elseif (($permission == 'CRUD') && ($groupe == "Directeurs") && ($etat =="brouillon_initie" )) {
                        return true;
                    } elseif ($permission == 'CRU' && ($groupe == "Directeurs") && ($etat =="brouillon_initie" )) {
                        return true;
                    } elseif ($permission == 'CR') {
                        return false;
                    }
                }),
                'delete' => new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return true;
                    } elseif ($permission == 'RU') {
                        return false;
                    } elseif ($permission == 'CRUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return false;
                    } elseif ($permission == 'CR') {
                        return false;
                    } 
                }),
                'show' => new ActionRender(function () use ($permission) {
                    if ($permission == 'R') {
                        return true;
                    } elseif ($permission == 'RD') {
                        return true;
                    } elseif ($permission == 'RU') {
                        return true;
                    } elseif ($permission == 'CRUD') {
                        return true;
                    } elseif ($permission == 'CRU') {
                        return true;
                    } elseif ($permission == 'CR') {
                        return true;
                    } 
                }),
                
                'edit_president' =>  new ActionRender(function () use ($permission,$groupe,$etat) {
                    if ($permission == 'R') {
                        return false;
                    } elseif ($permission == 'RD') {
                        return false;
                    } elseif ($permission == 'RU' && ($groupe !="Directeurs") && ($etat =="brouillon_review_president")) {
                        return true;
                    } elseif (($permission == 'CRUD') && ($groupe !="Directeurs") && ($etat =="brouillon_review_president")) {
                        return true;
                    } elseif ($permission == 'CRU' && ($groupe !="Directeurs") && ($etat =="brouillon_review_president")) {
                        return true;
                    } elseif ($permission == 'CR') {
                        return false;
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
                    'label' => 'Actions', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, DemandeBrouillon $context) use ($renders) {
                        $options = [
                            'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                            'target' => '#exampleModalSizeLg2',

                            'actions' => [
                                'edit' => [
                                    'url' => $this->generateUrl('app_directeur_demande_brouillon_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-default'], 'render' => $renders['edit']
                                ],
                                'edit_president' => [
                                    'url' => $this->generateUrl('app_directeur_demande_brouillon_edit_president', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% bi bi-pen', 'attrs' => ['class' => 'btn-default'], 'render' => $renders['edit_president']
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

    

    #[Route('/{id}/show', name: 'app_directeur_demande_brouillon_show', methods: ['GET'])]
    public function show(DemandeBrouillon $demandeBrouillon): Response
    {
        return $this->render('directeur/demande_brouillon/show.html.twig', [
            'demande_brouillon' => $demandeBrouillon,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_directeur_demande_brouillon_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DemandeBrouillon $demandeBrouillon,ElementMotifRepository $elementMotifRepository,DemandeRepository $demandeRepository,UtilisateurRepository $utilisateurRepository,MotifRepository $motifRepository, DemandeBrouillonRepository $demandeBrouillonRepository, FormError $formError): Response
    {
        $groupe = $this->groupe;
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
            $redirect = $this->generateUrl('app_config_brouillon_directeur_index');
            $workflow = $this->workflow->get($demandeBrouillon, 'demandeBrouillon');

            $date1 = $form->get('dateDebut')->getData(); //date fr le 01 mai 2010
            $date2 = $form->get('dateFin')->getData(); // date fr le 01 octobre 2010
            // On transforme les 2 dates en timestamp

           // dd( );
            $date3 = strtotime($date1->format('Y-m-d'));
            $date4 = strtotime( $date2->format('Y-m-d'));
            
            // On récupère la différence de timestamp entre les 2 précédents
            $nbJoursTimestamp = $date4 - $date3;
            
            // ** Pour convertir le timestamp (exprimé en secondes) en jours **
            // On sait que 1 heure = 60 secondes * 60 minutes et que 1 jour = 24 heures donc :
            $nbJours = $nbJoursTimestamp/86400; // 86 400 = 60*60*24
            
           
            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'valider_president'){
                    try {

                        if ($workflow->can($demandeBrouillon,'valider_president')){
                            $workflow->apply($demandeBrouillon, 'valider_president');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demande = new Demande();
                    $demande->setType("TYPE_JOURNEE")
                            ->setDateCreation(new DateTime())
                            ->setDateDebut($form->get('dateDebut')->getData())
                            ->setDateFin($form->get('dateFin')->getData())
                            ->setUtilisateur($utilisateurRepository->find($form->get('utilisateur')->getData()))
                            ->setEtat("demande_valider")
                            ->setNbreJour($nbJours)
                            ->setJustificationDirecteur("RAS")
                            ->setJustificationPresident("RAS")
                            ->setNbreJour($nbJours);

                            $demandeRepository->save($demande,true);

                     $motif = new Motif();
                     $motif->setDemande($demande) ;      
                     $motif->setDateCreation(new DateTime())  ;  
                     $motif->setElement($elementMotifRepository->findOneBy(array('code'=>'MOT3')))  ;     
                     $motif->setPrecisez($form->get('motif')->getData())  ;     
                     $motifRepository->save($motif,true);
                     $demandeBrouillon->setDemande($demande);
                    $demandeBrouillonRepository->save($demandeBrouillon, true);
                }
                if ($form->getClickedButton()->getName() === 'valider'){
                    try {

                        if ($workflow->can($demandeBrouillon,'passer')){
                            $workflow->apply($demandeBrouillon, 'passer');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demande = new Demande();
                    $demande->setType("TYPE_JOURNEE")
                            ->setDateCreation(new DateTime())
                            ->setDateDebut($form->get('dateDebut')->getData())
                            ->setDateFin($form->get('dateFin')->getData())
                            ->setUtilisateur($utilisateurRepository->find($form->get('utilisateur')->getData()))
                            ->setEtat("demande_valider")
                            ->setNbreJour($nbJours)
                            ->setJustificationDirecteur("RAS")
                            ->setJustificationPresident("RAS")
                            ->setNbreJour($nbJours);

                            $demandeRepository->save($demande,true);

                     $motif = new Motif();
                     $motif->setDemande($demande) ;      
                     $motif->setDateCreation(new DateTime())  ;  
                     $motif->setElement($elementMotifRepository->findOneBy(array('code'=>'MOT3')))  ;     
                     $motif->setPrecisez($form->get('motif')->getData())  ;     
                     $motifRepository->save($motif,true);
                     $demandeBrouillon->setDemande($demande);
                    $demandeBrouillonRepository->save($demandeBrouillon, true);
                }elseif($form->getClickedButton()->getName() === 'review_president'){
                    try {

                        if ($workflow->can($demandeBrouillon,'review_president')){
                            $workflow->apply($demandeBrouillon, 'review_president');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeBrouillonRepository->save($demandeBrouillon, true);
                }
                else{
                    $demandeBrouillonRepository->save($demandeBrouillon, true);
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

        return $this->renderForm('directeur/demande_brouillon/edit.html.twig', [
            'demande_brouillon' => $demandeBrouillon,
            'form' => $form,
            'groupe'=>$groupe 
        ]);
    }


    #[Route('/{id}/edit/rejeter', name: 'app_directeur_demande_brouillon_edit_rejeter', methods: ['GET', 'POST'])]
    public function editRejeter(Request $request, DemandeBrouillon $demandeBrouillon,ElementMotifRepository $elementMotifRepository,DemandeRepository $demandeRepository,UtilisateurRepository $utilisateurRepository,MotifRepository $motifRepository, DemandeBrouillonRepository $demandeBrouillonRepository, FormError $formError): Response
    {
        $groupe = $this->groupe;
        $form = $this->createForm(DemandeBrouillonFormAlerteType::class, $demandeBrouillon, [
            'method' => 'POST',
            'action' => $this->generateUrl('app_directeur_demande_brouillon_edit_rejeter', [
                'id' =>  $demandeBrouillon->getId()
            ])
        ]);

        $data = null;
        $statutCode = Response::HTTP_OK;

        $isAjax = $request->isXmlHttpRequest();


        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_config_brouillon_directeur_index');
            $workflow = $this->workflow->get($demandeBrouillon, 'demandeBrouillon');

            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'rejeter'){
                    try {

                        if ($workflow->can($demandeBrouillon,'rejeter_directeur')){
                            $workflow->apply($demandeBrouillon, 'rejeter_directeur');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demandeBrouillonRepository->save($demandeBrouillon, true);
                }elseif($form->getClickedButton()->getName() === 'rejeter_president'){
                    try {

                        if ($workflow->can($demandeBrouillon,'rejeter_president')){
                            $workflow->apply($demandeBrouillon, 'rejeter_president');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeBrouillonRepository->save($demandeBrouillon, true);
                }
                else{
                    $demandeBrouillonRepository->save($demandeBrouillon, true);
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

        return $this->renderForm('directeur/demande_brouillon/annuler.html.twig', [
            'demande_brouillon' => $demandeBrouillon,
            'form' => $form,
            'groupe'=>$groupe 
        ]);
    }

    #[Route('/{id}/edit/president', name: 'app_directeur_demande_brouillon_edit_president', methods: ['GET', 'POST'])]
    public function editPresident(Request $request, DemandeBrouillon $demandeBrouillon,ElementMotifRepository $elementMotifRepository,DemandeRepository $demandeRepository,UtilisateurRepository $utilisateurRepository,MotifRepository $motifRepository, DemandeBrouillonRepository $demandeBrouillonRepository, FormError $formError): Response
    {
        $groupe = $this->getUser()->getGroupe()->getName();
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
            $redirect = $this->generateUrl('app_config_brouillon_directeur_index');
            $workflow = $this->workflow->get($demandeBrouillon, 'demandeBrouillon');

            $date1 = $form->get('dateDebut')->getData(); //date fr le 01 mai 2010
            $date2 = $form->get('dateFin')->getData(); // date fr le 01 octobre 2010
            // On transforme les 2 dates en timestamp

           // dd( );
            $date3 = strtotime($date1->format('Y-m-d'));
            $date4 = strtotime( $date2->format('Y-m-d'));
            
            // On récupère la différence de timestamp entre les 2 précédents
            $nbJoursTimestamp = $date4 - $date3;
            
            // ** Pour convertir le timestamp (exprimé en secondes) en jours **
            // On sait que 1 heure = 60 secondes * 60 minutes et que 1 jour = 24 heures donc :
            $nbJours = $nbJoursTimestamp/86400; // 86 400 = 60*60*24
            
           
            if ($form->isValid()) {
                if ($form->getClickedButton()->getName() === 'valider'){
                    try {

                        if ($workflow->can($demandeBrouillon,'passer')){
                            $workflow->apply($demandeBrouillon, 'passer');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }

                    $demande = new Demande();
                    $demande->setType("TYPE_JOURNEE")
                            ->setDateCreation(new DateTime())
                            ->setDateDebut($form->get('dateDebut')->getData())
                            ->setDateFin($form->get('dateFin')->getData())
                            ->setUtilisateur($utilisateurRepository->find($form->get('utilisateur')->getData()))
                            ->setEtat("demande_valider")
                            ->setNbreJour($nbJours)
                            ->setJustificationDirecteur("RAS")
                            ->setJustificationPresident("RAS")
                            ->setNbreJour($nbJours);

                            $demandeRepository->save($demande,true);

                     $motif = new Motif();
                     $motif->setDemande($demande) ;      
                     $motif->setDateCreation(new DateTime())  ;  
                     $motif->setElement($elementMotifRepository->findOneBy(array('code'=>'MOT3')))  ;     
                     $motif->setPrecisez($form->get('motif')->getData())  ;     
                     $motifRepository->save($motif,true);
                     $demandeBrouillon->setDemande($demande);
                    $demandeBrouillonRepository->save($demandeBrouillon, true);
                }elseif($form->getClickedButton()->getName() === 'review_president'){
                    try {

                        if ($workflow->can($demandeBrouillon,'review_president')){
                            $workflow->apply($demandeBrouillon, 'review_president');
                            $this->em->flush();
                        }
                    } catch (LogicException $e) {

                        $this->addFlash('danger', sprintf('No, that did not work: %s', $e->getMessage()));
                    }
                    $demandeBrouillonRepository->save($demandeBrouillon, true);
                }
                else{
                    $demandeBrouillonRepository->save($demandeBrouillon, true);
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

        return $this->renderForm('directeur/demande_brouillon/edit.html.twig', [
            'demande_brouillon' => $demandeBrouillon,
            'form' => $form,
            'groupe'=>$groupe 
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
