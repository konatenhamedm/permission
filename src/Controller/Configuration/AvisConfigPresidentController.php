<?php

namespace App\Controller\Configuration;

use App\Service\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('config/avis/president')]
class AvisConfigPresidentController extends AbstractController
{
    private const MODULE_NAME = 'president';


    #[Route(path: '/', name: 'app_config_avis_president_index', methods: ['GET', 'POST'])]
    public function index(Request $request, Breadcrumb $breadcrumb): Response
    {

        $modules = [
            [
                'label' => "Approbation directeur",
                'icon' => 'bi bi-list',
                'name' => 'demande',
                'role' => 'ROLE_ALL',
                'href' => $this->generateUrl('app_config_president_ls', ['module' => 'approbation_dir'])
            ],
            [
                'label' => 'Documents',
                'icon' => 'bi bi-folder',
                'role' => 'ROLE_ALL',
                'name' => 'sortie',
                'href' => $this->generateUrl('app_config_president_ls', ['module' => 'document'])
            ],
            [
                'label' => "Documents attente validation",
                'icon' => 'bi bi-truck',
                'role' => 'ROLE_ALL',
                'name' => 'retour',
                'href' => $this->generateUrl('app_config_president_ls', ['module' => 'approbation_pr'])
            ],
            [
                'label' => 'Demandes traitées',
                'icon' => 'bi bi-people',
                'role' => 'ROLE_ALL',
                'name' => 'demande_t',
                'href' => $this->generateUrl('app_config_president_ls', ['module' => 'demande_t'])
            ]
            ,
            [
                'label' => 'Demandes réfusées',
                'icon' => 'bi bi-people',
                'role' => 'ROLE_ALL',
                'name' => 'mouvement',
                'href' => $this->generateUrl('app_config_president_ls', ['module' => 'demande_r'])
            ]
           
        ];

        $breadcrumb->addItem([
            [
                'route' => 'app_default',
                'label' => 'Tableau de bord'
            ],
            [
                'label' => 'Avis président'
            ]
        ]);

        return $this->render('president/config/index.html.twig', [
            'modules' => $modules,
            'module_name' => self::MODULE_NAME,
            'breadcrumb' => $breadcrumb
        ]);
    }


    #[Route(path: '/{module}', name: 'app_config_president_ls', methods: ['GET', 'POST'])]
    public function liste(Request $request, string $module): Response
    {


        /**
         * @todo: A déplacer dans un service
         */
        $parametres = [
            'approbation_dir' => [
                [
                    'label' => 'Suzang group ',
                    'etat'=>'demande_valider_directeur',
                    'id' => 'demande_valider_directeur_suzang',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_valider_directeur','entreprise' => 'suzang group'])
                ],
                [
                    'label' => 'Appatam',
                    'etat'=>'demande_valider_directeur',
                    'id' => 'demande_valider_directeur_appatam',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat' =>'demande_valider_directeur','entreprise' => 'appatam'])
                ],
                [
                    'label' => 'Socopi',
                    'etat'=>'demande_valider_directeur',
                    'id' => 'param_validation_demande_socopi',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat' =>'demande_valider_directeur','entreprise' => 'socopi'])
                ],
                [
                    'label' => 'Djela',
                    'etat'=>'demande_valider_directeur',
                    'id' => 'demande_valider_directeur_djela',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat' =>'demande_valider_directeur','entreprise' => 'djela'])
                ],
                [
                    'label' => 'Yefien',
                    'etat'=>'demande_valider_directeur',
                    'id' => 'demande_valider_directeur_yefien',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_valider_directeur','entreprise' => 'yefien'])
                ],
            ],

            'approbation_pr' => [
                [
                    'label' => 'suzang group',
                    'etat'=>'demande_valider_attente_document',
                    'id' => 'param_initie_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_valider_attente_document','entreprise' => 'suzang group'])
                ],
                [
                    'label' => 'Appatam',
                     'etat'=>'demande_valider_attente_document',
                    'id' => 'param_initie_demande_appatam',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat' =>'demande_valider_attente_document','entreprise' => 'appatam'])
                ],
                [
                    'label' => 'Socopi',
                     'etat'=>'demande_valider_attente_document',
                    'id' => 'param_validation_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat' =>'demande_valider_attente_document','entreprise' => 'socopi'])
                ],
                [
                    'label' => 'Djela',
                     'etat'=>'demande_valider_attente_document',
                    'id' => 'param_livraison_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat' =>'demande_valider_attente_document','entreprise' => 'djela'])
                ],
                [
                    'label' => 'Yefien',
                     'etat'=>'demande_valider_attente_document',
                    'id' => 'param_livrer_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_valider_attente_document','entreprise' => 'yefien'])
                ],
            ],

            'document' => [
                [
                    'label' => 'suzang group',
                    'etat'=>'document_enregistre',
                    'id' => 'param_initie_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'document_enregistre','entreprise' => 'suzang group'])
                ],
                [
                    'label' => 'Appatam',
                     'etat'=>'document_enregistre',
                    'id' => 'param_initie_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'document_enregistre','entreprise' => 'appatam'])
                ],
                [
                    'label' => 'Socopi',
                     'etat'=>'document_enregistre',
                    'id' => 'param_validation_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'document_enregistre','entreprise' => 'socopi'])
                ],
                [
                    'label' => 'Djela',
                     'etat'=>'document_enregistre',
                    'id' => 'param_livraison_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'document_enregistre','entreprise' => 'djela'])
                ],
                [
                    'label' => 'Yefien',
                     'etat'=>'document_enregistre',
                    'id' => 'param_livrer_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'document_enregistre','entreprise' => 'yefien'])
                ],
            ],

            'demande_t' => [
                [
                    'label' => 'suzang group',
                    'etat'=>'demande_valider',
                    'id' => 'param_initie_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_valider','entreprise' => 'suzang group'])
                ],
                [
                    'label' => 'Appatam',
                    'etat'=>'demande_valider',
                    'id' => 'param_initie_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_valider','entreprise' => 'appatam'])
                ],
                [
                    'label' => 'Socopi',
                    'etat'=>'demande_valider',
                    'id' => 'param_validation_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_valider','entreprise' => 'socopi'])
                ],
                [
                    'label' => 'Djela',
                    'etat'=>'demande_valider',
                    'id' => 'param_livraison_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_valider','entreprise' => 'djela'])
                ],
                [
                    'label' => 'Yefien',
                    'etat'=>'demande_valider',
                    'id' => 'param_livrer_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_valider','entreprise' => 'yefien'])
                ],
            ],
            'demande_r' => [
                [
                    'label' => 'suzang group',
                    'etat'=>'demande_refuser',
                    'id' => 'param_initie_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_refuser','entreprise' => 'suzang group'])
                ],
                [
                    'label' => 'Appatam',
                    'etat'=>'demande_refuser',
                    'id' => 'param_initie_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_refuser','entreprise' => 'appatam'])
                ],
                [
                    'label' => 'Socopi',
                    'etat'=>'demande_refuser',
                    'id' => 'param_validation_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_refuser','entreprise' => 'socopi'])
                ],
                [
                    'label' => 'Djela',
                    'etat'=>'demande_refuser',
                    'id' => 'param_livraison_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_refuser','entreprise' => 'djela'])
                ],
                [
                    'label' => 'Yefien',
                    'etat'=>'demande_refuser',
                    'id' => 'param_livrer_demande',
                    'href' => $this->generateUrl('app_president_demande_index', ['etat'=>'demande_refuser','entreprise' => 'yefien'])
                ],
            ],
            
        ];


        return $this->render('president/config/liste.html.twig', ['links' => $parametres[$module] ?? []]);
    }
}