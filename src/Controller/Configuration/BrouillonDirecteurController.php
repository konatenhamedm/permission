<?php

namespace App\Controller\Configuration;

use App\Service\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('config/brouillon/directeur')]
class BrouillonDirecteurController extends AbstractController
{
    private const MODULE_NAME = 'directeur';


    #[Route(path: '/', name: 'app_config_brouillon_directeur_index', methods: ['GET', 'POST'])]
    public function index(Request $request, Breadcrumb $breadcrumb): Response
    {
      
        $modules = [
            [
                'label' => "Brouiilons initiés",
                'icon' => 'bi bi-list',
                'etat'=>'brouillon_initie',
                'name' => 'demande',
                'role' => 'ROLE_ALL',
                'href' => $this->generateUrl('app_directeur_demande_brouillon_index', ['etat' => 'brouillon_initie'])
            ],
            [
                'label' => "Brouillons en revues",
                'icon' => 'bi bi-truck',
                'etat'=>'brouillon_review_president',
                'role' => 'ROLE_ALL',
                'name' => 'retour',
                'href' => $this->generateUrl('app_directeur_demande_brouillon_index', ['etat' => 'brouillon_review_president'])
            ],
            [
                'label' => 'Brouillons validés',
                'icon' => 'bi bi-folder',
                'etat'=>'brouillon_valider',
                'role' => 'ROLE_ALL',
                'name' => 'sortie',
                'href' => $this->generateUrl('app_directeur_demande_brouillon_index', ['etat' => 'brouillon_valider'])
            ],
            [
                'label' => 'Brouillons réjetés',
                'icon' => 'bi bi-people',
                'role' => 'ROLE_ALL',
                'etat'=>'brouillon_rejeter',
                'name' => 'demande_t',
                'href' => $this->generateUrl('app_directeur_demande_brouillon_index', ['etat' => 'brouillon_rejeter'])
            ]
           
        ];

        $breadcrumb->addItem([
            [
                'route' => 'app_default',
                'label' => 'Tableau de bord'
            ],
            [
                'label' => 'Avis directeur'
            ]
        ]);

        return $this->render('directeur/brouillon/index.html.twig', [
            'modules' => $modules,
            'module_name' => self::MODULE_NAME,
            'breadcrumb' => $breadcrumb
        ]);
    }


    #[Route(path: '/{module}', name: 'app_brouillon_directeur_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $module): Response
    {


        /**
         * @todo: A déplacer dans un service
         */
        $parametres = [
            
            
        ];


        return $this->render('directeur/brouillon/liste.html.twig', ['links' => $parametres[$module] ?? []]);
    }



}