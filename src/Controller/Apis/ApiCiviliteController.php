<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\Civilite;
use App\Repository\CiviliteRepository;
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

#[Route('/api/civilite')]
class ApiCiviliteController extends ApiInterface
{
    #[Route('/', name: 'api_civilite', methods: ['GET'])]
    /**
     * Affiche toutes les civiltes.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Civilite::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="Civilite")
     * @Security(name="Bearer")
     */
    public function getAll(CiviliteRepository $civiliteRepository): Response
    {
        try{

            $civilites = $civiliteRepository->findAll();
            $response = $this->response($civilites);

        }catch (\Exception $exception){
            $this->setMessage($exception.toString());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/getOne/{id}', name: 'api_civilite_get_one', methods: ['GET'])]
    /**
     * Affiche une civilte en offrant un identifiant.
     * @OA\Tag(name="Civilite")
     * @Security(name="Bearer")
     */
    public function getOne(?Civilite $civilite)
    {
     /*  $civilite = $civiliteRepository->find($id);*/
        try{
            if($civilite){
                $response = $this->response($civilite);
            }else{
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($civilite);
            }
        }catch (\Exception $exception){
            $this->setMessage($exception.toString());
            $response = $this->response(null);
        }


        return $response;
    }


    #[Route('/create', name: 'api_civilite_create', methods: ['POST'])]
    /**
     * Permet de créer une civilite.
     *
     * @OA\Tag(name="Civilite")
     * @Security(name="Bearer")
     */
    public function create(Request $request,CiviliteRepository $civiliteRepository)
    {
            try{
                $data = json_decode($request->getContent());

                $civilite = $civiliteRepository->findOneBy(array('code'=>$data->code));
                if($civilite == null){
                    $civilite = new Civilite();
                    $civilite->setCode($data->code);
                    $civilite->setLibelle($data->libelle);

                    // On sauvegarde en base
                    $civiliteRepository->add($civilite,true);

                    // On retourne la confirmation
                    $response = $this->response($civilite);

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


    #[Route('/update/{id}', name: 'api_civilite_update', methods: ['POST'])]
    /**
     * Permet de mettre à jour une civilite.
     *
     * @OA\Tag(name="Civilite")
     * @Security(name="Bearer")
     */
    public function update(Request $request,CiviliteRepository $civiliteRepository,$id)
    {
       try{
           $data = json_decode($request->getContent());

           $civilite = $civiliteRepository->find($id);
           if($civilite != null){

               $civilite->setCode($data->code);
               $civilite->setLibelle($data->libelle);

               // On sauvegarde en base
               $civiliteRepository->add($civilite,true);

               // On retourne la confirmation
               $response = $this->response($civilite);

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


    #[Route('/delete/{id}', name: 'api_civilite_delete', methods: ['POST'])]
    /**
     * permet de supprimer une civilite en offrant un identifiant.
     *
     * @OA\Tag(name="Civilite")
     * @Security(name="Bearer")
     */
    public function delete(Request $request,CiviliteRepository $civiliteRepository,$id)
    {
        try{
            $data = json_decode($request->getContent());

            $civilite = $civiliteRepository->find($id);
            if($civilite != null){

                $civiliteRepository->remove($civilite,true);

                // On retourne la confirmation
                $response = $this->response($civilite);

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


    #[Route('/active/{id}', name: 'api_civilite_active', methods: ['GET'])]
    /**
     * Permet d'activer une civilite en offrant un identifiant.
     * @OA\Tag(name="Civilite")
     * @Security(name="Bearer")
     */
    public function active(?Civilite $civilite,CiviliteRepository $civiliteRepository)
    {
        /*  $civilite = $civiliteRepository->find($id);*/
        try{
            if($civilite){

                //$civilite->setCode("555"); //TO DO nous ajouter un champs active
                $civiliteRepository->add($civilite,true);
                $response = $this->response($civilite);
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


    #[Route('/active/multiple', name: 'api_civilite_active_multiple', methods: ['POST'])]
    /**
     * Permet de faire une desactivation multiple.
     *
     * @OA\Tag(name="Civilite")
     * @Security(name="Bearer")
     */
    public function multipleActive(Request $request,CiviliteRepository $civiliteRepository){
        try{
            $data = json_decode($request->getContent());

            $listeCivilites = $civiliteRepository->findAllByListId($data->ids);
            foreach ($listeCivilites as $listeCivilite) {
                //$listeCivilite->setCode("555");  //TO DO nous ajouter un champs active
                $civiliteRepository->add($listeCivilite,true);
            }
            
            $response = $this->response(null);
        }catch (\Exception $exception){
            $this->setMessage($exception.toString());
            $response = $this->response(null);
        }
        return $response;
    }
}
