<?php

namespace App\Controller\Configuration;

use App\Controller\BaseController;
use App\Service\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('config/avis/directeur')]
class AvisConfigDirecteurController extends BaseController
{
    private const MODULE_NAME = 'directeur';
    const INDEX_ROOT_NAME = "app_config_avis_directeur_index";

    #[Route(path: '/', name: 'app_config_avis_directeur_index', methods: ['GET', 'POST'])]
    public function index(Request $request, Breadcrumb $breadcrumb): Response
    {
        $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(), self::INDEX_ROOT_NAME);

        $modules = [
            [
                'label' => "Attente approbation",
                'icon' => 'bi bi-list',
                'etat' => 'demande_initie',
                'name' => 'demande',
                'role' => 'ROLE_ALL',
                'href' => $this->generateUrl('app_demande_demande_avis_ls', ['etat' => 'demande_initie'])
            ],
            [
                'label' => "Approbation Pr",
                'icon' => 'bi bi-truck',
                'etat' => 'demande_valider_directeur',
                'role' => 'ROLE_ALL',
                'name' => 'retour',
                'href' => $this->generateUrl('app_demande_demande_avis_ls', ['etat' => 'demande_valider_directeur'])
            ],
            [
                'label' => "Documents",
                'icon' => 'bi bi-truck',
                'etat' => 'document_enregistre',
                'role' => 'ROLE_ALL',
                'name' => 'retour',
                'href' => $this->generateUrl('app_demande_demande_avis_ls', ['etat' => 'document_enregistre'])
            ],
            [
                'label' => 'Vérification Document',
                'icon' => 'bi bi-folder',
                'etat' => 'demande_valider_attente_document',
                'role' => 'ROLE_ALL',
                'name' => 'sortie',
                'href' => $this->generateUrl('app_demande_demande_avis_ls', ['etat' => 'demande_valider_attente_document'])
            ],
            [
                'label' => 'Demandes validées',
                'icon' => 'bi bi-people',
                'role' => 'ROLE_ALL',
                'etat' => 'demande_valider',
                'name' => 'demande_t',
                'href' => $this->generateUrl('app_demande_demande_avis_ls', ['etat' => 'demande_valider'])
            ],
            [
                'label' => 'Demandes réfusées',
                'icon' => 'bi bi-people',
                'role' => 'ROLE_ALL',
                'etat' => 'demande_refuser',
                'name' => 'mouvement',
                'href' => $this->generateUrl('app_demande_demande_avis_ls', ['etat' => 'demande_refuser'])
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

        return $this->render('directeur/config/index_avis.html.twig', [
            'modules' => $modules,
            'module_name' => self::MODULE_NAME,
            'breadcrumb' => $breadcrumb,
            'permition' => $permission,
        ]);
    }


    #[Route(path: '/{module}', name: 'app_config_directeur_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $module): Response
    {


        /**
         * @todo: A déplacer dans un service
         */
        $parametres = [];


        return $this->render('directeur/config/liste.html.twig', ['links' => $parametres[$module] ?? []]);
    }
}
