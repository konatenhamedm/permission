<?php

namespace App\Controller\Parametre;

use App\Entity\AvisPresident;
use App\Form\AvisPresidentType;
use App\Repository\AvisPresidentRepository;
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

#[Route('/parametre/avis/president')]
class AvisPresidentController extends BaseController
{

    const INDEX_ROOT_NAME = 'app_parametre_avis_president_index';

    #[Route('/', name: 'app_parametre_avis_president_index', methods: ['GET', 'POST'])]
    public function index(Request $request, DataTableFactory $dataTableFactory): Response
    {
    $permission = $this->menu->getPermissionIfDifferentNull($this->security->getUser()->getGroupe()->getId(),self::INDEX_ROOT_NAME);

    $table = $dataTableFactory->create()
        ->add('libelle', TextColumn::class, ['label' => 'Libelle'])
        ->add('code', TextColumn::class, ['label' => 'Code'])
    ->createAdapter(ORMAdapter::class, [
    'entity' => AvisPresident::class,
    ])
    ->setName('dt_app_parametre_avis_president');
    if($permission != null){

    $renders = [
        'edit' =>  new ActionRender(function () use ($permission) {
        if($permission == 'R'){
        return false;
        }elseif($permission == 'RD'){
        return false;
        }elseif($permission == 'RU'){
        return true;
        }elseif($permission == 'RUD'){
        return true;
        }elseif($permission == 'CRU'){
        return true;
        }
        elseif($permission == 'CR'){
        return false;
        }else{
        return true;
        }

        }),
        'delete' => new ActionRender(function () use ($permission) {
        if($permission == 'R'){
        return false;
        }elseif($permission == 'RD'){
        return true;
        }elseif($permission == 'RU'){
        return false;
        }elseif($permission == 'RUD'){
        return true;
        }elseif($permission == 'CRU'){
        return false;
        }
        elseif($permission == 'CR'){
        return false;
        }else{
        return true;
        }
        }),
        'show' => new ActionRender(function () use ($permission) {
        if($permission == 'R'){
        return true;
        }elseif($permission == 'RD'){
        return true;
        }elseif($permission == 'RU'){
        return true;
        }elseif($permission == 'RUD'){
        return true;
        }elseif($permission == 'CRU'){
        return true;
        }
        elseif($permission == 'CR'){
        return true;
        }else{
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
    'label' => 'Actions'
    , 'orderable' => false
    ,'globalSearchable' => false
    ,'className' => 'grid_row_actions'
    , 'render' => function ($value, AvisPresident $context) use ($renders) {
    $options = [
    'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
    'target' => '#exampleModalSizeLg2',

    'actions' => [
    'edit' => [
    'url' => $this->generateUrl('app_parametre_avis_president_edit', ['id' => $value])
    , 'ajax' => true
    , 'icon' => '%icon% bi bi-pen'
    , 'attrs' => ['class' => 'btn-default']
    , 'render' => $renders['edit']
    ],
    'show' => [
    'url' => $this->generateUrl('app_parametre_avis_president_show', ['id' => $value])
    , 'ajax' => true
    , 'icon' => '%icon% bi bi-eye'
    , 'attrs' => ['class' => 'btn-primary']
    , 'render' => $renders['show']
    ],
    'delete' => [
    'target' => '#exampleModalSizeNormal',
    'url' => $this->generateUrl('app_parametre_avis_president_delete', ['id' => $value])
    , 'ajax' => true
    , 'icon' => '%icon% bi bi-trash'
    , 'attrs' => ['class' => 'btn-main']
    ,  'render' => $renders['delete']
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


    return $this->render('parametre/avis_president/index.html.twig', [
    'datatable' => $table,
    'permition' => $permission
    ]);
    }

    #[Route('/new', name: 'app_parametre_avis_president_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AvisPresidentRepository $avisPresidentRepository, FormError $formError): Response
{
$avisPresident = new AvisPresident();
$form = $this->createForm(AvisPresidentType::class, $avisPresident, [
'method' => 'POST',
'action' => $this->generateUrl('app_parametre_avis_president_new')
]);
$form->handleRequest($request);

$data = null;
$statutCode = Response::HTTP_OK;

$isAjax = $request->isXmlHttpRequest();

    if ($form->isSubmitted()) {
    $response = [];
    $redirect = $this->generateUrl('app_parametre_avis_president_index');


    if ($form->isValid()) {

    $avisPresidentRepository->save($avisPresident, true);
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

    return $this->renderForm('parametre/avis_president/new.html.twig', [
    'avis_president' => $avisPresident,
    'form' => $form,
    ]);
}

    #[Route('/{id}/show', name: 'app_parametre_avis_president_show', methods: ['GET'])]
public function show(AvisPresident $avisPresident): Response
{
return $this->render('parametre/avis_president/show.html.twig', [
'avis_president' => $avisPresident,
]);
}

    #[Route('/{id}/edit', name: 'app_parametre_avis_president_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AvisPresident $avisPresident, AvisPresidentRepository $avisPresidentRepository, FormError $formError): Response
{

$form = $this->createForm(AvisPresidentType::class, $avisPresident, [
'method' => 'POST',
'action' => $this->generateUrl('app_parametre_avis_president_edit', [
'id' =>  $avisPresident->getId()
])
]);

$data = null;
$statutCode = Response::HTTP_OK;

$isAjax = $request->isXmlHttpRequest();


$form->handleRequest($request);

    if ($form->isSubmitted()) {
    $response = [];
    $redirect = $this->generateUrl('app_parametre_avis_president_index');


    if ($form->isValid()) {

    $avisPresidentRepository->save($avisPresident, true);
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

    return $this->renderForm('parametre/avis_president/edit.html.twig', [
    'avis_president' => $avisPresident,
    'form' => $form,
    ]);
}

    #[Route('/{id}/delete', name: 'app_parametre_avis_president_delete', methods: ['DELETE', 'GET'])]
    public function delete(Request $request, AvisPresident $avisPresident, AvisPresidentRepository $avisPresidentRepository): Response
{
$form = $this->createFormBuilder()
->setAction(
$this->generateUrl(
'app_parametre_avis_president_delete'
,   [
'id' => $avisPresident->getId()
]
)
)
->setMethod('DELETE')
->getForm();
$form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
    $data = true;
    $avisPresidentRepository->remove($avisPresident, true);

    $redirect = $this->generateUrl('app_parametre_avis_president_index');

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

return $this->renderForm('parametre/avis_president/delete.html.twig', [
'avis_president' => $avisPresident,
'form' => $form,
]);
}
}
