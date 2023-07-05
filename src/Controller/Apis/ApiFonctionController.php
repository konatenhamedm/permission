<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Fonction;
use App\Repository\FonctionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function Symfony\Component\String\toString;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/api/fonction')]
class ApiFonctionController extends ApiInterface
{
    #[Route('/', name: 'api_fonction', methods: ['GET'])]
    /**
     * Affiche toutes les fonctions.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Fonction::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Fonction")
     * @Security(name="Bearer")
     * @param FonctionRepository $fonctionRepository
     * @return Response
     */
    public function getAll(FonctionRepository $fonctionRepository): Response
    {
        try{

            $fonctions = $fonctionRepository->findAll();
            $response = $this->response($fonctions);

        }catch (\Exception $exception){
            $this->setMessage($exception.toString());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/getOne/{id}', name: 'api_fonction_get_one', methods: ['GET'])]
    /**
     * Affiche une fonction en offrant un identifiant.
     * @OA\Tag(name="Fonction")
     * @Security(name="Bearer")
     */
    public function getOne(?Fonction $fonction)
    {
     /*  $fonction = $fonctionRepository->find($id);*/
        try{
            if($fonction){
                $response = $this->response($fonction);
            }else{
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($fonction);
            }
        }catch (\Exception $exception){
            $this->setMessage($exception.toString());
            $response = $this->response(null);
        }


        return $response;
    }


    #[Route('/create', name: 'api_fonction_create', methods: ['POST'])]
    /**
     * Permet de créer une fonction.
     * @OA\Tag(name="Fonction")
     * @Security(name="Bearer")
     */
    public function create(Request $request,FonctionRepository $fonctionRepository)
    {
            try{
                $data = json_decode($request->getContent());

                $fonction = $fonctionRepository->findOneBy(array('code'=>$data->code));
                if($fonction == null){
                    $fonction = new Fonction();
                    $fonction->setCode($data->code);
                    $fonction->setLibelle($data->libelle);

                    // On sauvegarde en base
                    $fonctionRepository->add($fonction,true);

                    // On retourne la confirmation
                    $response = $this->response($fonction);

                }else{
                    $this->setMessage("cette ressource existe deja en base");
                    $this->setStatusCode(300);
                    $response = $this->response(null);

                }
            }catch (\Exception $exception){
                $this->setMessage($exception.toString());
                $response = $this->response(null);
            }


        return $response;
        }


    #[Route('/update/{id}', name: 'api_fonction_update', methods: ['PUT'])]
    /**
     * Permet de mettre à jour une fonction.
     * @OA\Tag(name="Fonction")
     * @Security(name="Bearer")
     */
    public function update(Request $request,FonctionRepository $fonctionRepository,$id)
    {
       try{
           $data = json_decode($request->getContent());

           $fonction = $fonctionRepository->find($id);
           if($fonction != null){

               $fonction->setCode($data->code);
               $fonction->setLibelle($data->libelle);

               // On sauvegarde en base
               $fonctionRepository->add($fonction,true);

               // On retourne la confirmation
               $response = $this->response($fonction);

           }else{
               $this->setMessage("cette ressource est inexsitante");
               $this->setStatusCode(300);
               $response = $this->response(null);

           }


       }catch (\Exception $exception){
           $this->setMessage($exception.toString());
           $response = $this->response(null);
       }
        return $response;
    }


    #[Route('/delete/{id}', name: 'api_fonction_delete', methods: ['POST'])]
    /**
     * permet de supprimer une fonction en offrant un identifiant.
     * @OA\Tag(name="Fonction")
     * @Security(name="Bearer")
     */
    public function delete(Request $request,FonctionRepository $fonctionRepository,$id)
    {
        try{
            $data = json_decode($request->getContent());

            $fonction = $fonctionRepository->find($id);
            if($fonction != null){

                $fonctionRepository->remove($fonction,true);

                // On retourne la confirmation
                $response = $this->response($fonction);

            }else{
                $this->setMessage("cette ressource est inexistante");
                $this->setStatusCode(300);
                $response = $this->response(null);

            }
        }catch (\Exception $exception){
            $this->setMessage($exception.toString());
            $response = $this->response(null);
        }
        return $response;
    }
    #[Route('/active/{id}', name: 'api_fonction_active', methods: ['GET'])]
    /**
     * Permet d'activer une fonction en offrant un identifiant.
     * @OA\Tag(name="Fonction")
     * @Security(name="Bearer")
     */
    public function active(?Fonction $fonction,FonctionRepository $fonctionRepository)
    {
        /*  $civilite = $civiliteRepository->find($id);*/
        try{
            if($fonction){

                //$civilite->setCode("555"); //TO DO nous ajouter un champs active
                $fonctionRepository->add($fonction,true);
                $response = $this->response($fonction);
            }else{
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response(null);
            }
        }catch (\Exception $exception){
            $this->setMessage($exception.toString());
            $response = $this->response(null);
        }


        return $response;
    }


    #[Route('/active/multiple', name: 'api_fonction_active_mulptiple', methods: ['POST'])]
    /**
     * Permet de faire une desactivation multiple.
     * @OA\Tag(name="Fonction")
     * @Security(name="Bearer")
     */
    public function multipleActive(Request $request,FonctionRepository $fonctionRepository){
        try{
            $data = json_decode($request->getContent());

            $listeFonctions = $fonctionRepository->findAllByListId($data->ids);
            foreach ($listeFonctions as $listeFonction) {
                $listeFonction->setCode("555");
                $fonctionRepository->add($listeFonction,true);
            }
            
            $response = $this->response(true);
        }catch (\Exception $exception){
            $this->setMessage($exception.toString());
            $response = $this->response(null);
        }
        return $response;
    }



}
