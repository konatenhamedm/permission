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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\FormError;
use DateTime;


#[Route('/all/methodes')]
class AllMethodesDemandeController extends BaseController
{
    #[Route('/new', name: 'app_demande_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DemandeRepository $demandeRepository,ElementMotifRepository $elementRepository,AvisRepository $avisRepository, FormError $formError): Response
    {

        //dd("ee");
        $demande = new Demande();
        $date = new DateTime();
        $rest = $date->modify('+1 day');

        $motif = new Motif();
        $motif->setElement($elementRepository->find("MOT1"));
        $demande->addMotif($motif);

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
                if($this->security->getUser()->getGroupe()->getName() == "Directeurs"){
                    $demande->setEtat("demande_valider_directeur");
                    $demande->setAvis($avisRepository->findOneBy(array('code'=>'AV1')));
                    $demande->setJustificationDirecteur('Demande permission directeur');
                }else{
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
}
