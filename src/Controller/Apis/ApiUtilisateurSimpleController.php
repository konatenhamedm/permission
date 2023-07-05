<?php

namespace App\Controller\Apis;

use App\Controller\ApiInterface;
use App\Entity\UtilisateurSimple;
use App\Repository\UtilisateurSimpleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;
use function Symfony\Component\String\toString;
use function Symfony\Component\VarDumper\Caster\__toString;

#[Route('/api/utilisateur/simple')]
class ApiUtilisateurSimpleController extends ApiInterface
{
    #[Route('/', name: 'api_utilisateurSimple', methods: ['GET'])]
    /**
     * Affiche toutes les utilisateurs front.
     * @OA\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=UtilisateurSimple::class, groups={"full"}))
     *     )
     * )
     * @OA\Tag(name="UtilisateurSimple")
     * @Security(name="Bearer")
     */
    public function getAll(UtilisateurSimpleRepository $utilisateurSimpleRepository): Response
    {
        try{

            $utilisateurSimples = $utilisateurSimpleRepository->findAll();
            $response = $this->response($utilisateurSimples);

        }catch (\Exception $exception){
            $this->setMessage($exception.toString());
            $response = $this->response(null);
        }

        // On envoie la réponse
        return $response;
    }


    #[Route('/getOne/{id}', name: 'api_utilisateurSimple_get_one', methods: ['GET'])]
    /**
     * Affiche une civilte en offrant un identifiant.
     * @OA\Tag(name="UtilisateurSimple")
     * @Security(name="Bearer")
     */
    public function getOne(?UtilisateurSimple $utilisateurSimple)
    {
     /*  $utilisateurSimple= $utilisateurSimpleRepository->find($id);*/
        try{
            if($utilisateurSimple){
                $response = $this->response($utilisateurSimple);
            }else{
                $this->setMessage('Cette ressource est inexistante');
                $this->setStatusCode(300);
                $response = $this->response($utilisateurSimple);
            }
        }catch (\Exception $exception){
            $this->setMessage($exception.toString());
            $response = $this->response(null);
        }


        return $response;
    }


    #[Route('/create', name: 'api_utilisateurSimple_create', methods: ['POST'])]
    /**
     * Permet de créer une utilisateurSimple.
     *
     * @OA\Tag(name="UtilisateurSimple")
     * @Security(name="Bearer")
     */
    public function create(Request $request,UtilisateurSimpleRepository $utilisateurSimpleRepository)
    {
            try{
                $data = json_decode($request->getContent());
//dd($data);
                $utilisateurSimple= $utilisateurSimpleRepository->findOneBy(array('username'=>$data->username));

                if($utilisateurSimple == null){
                    $utilisateurSimple= new UtilisateurSimple();
                   $utilisateurSimple->setContact($data->contact);
                    $utilisateurSimple->setNom($data->nom);
                    $utilisateurSimple->setEmail($data->email);
                    $utilisateurSimple->setPrenoms($data->prenoms);
                    $utilisateurSimple->setUsername($data->username);
                    $utilisateurSimple->setPassword($this->hasher->hashPassword($utilisateurSimple, $data->password));

                    // On sauvegarde en base
                    $utilisateurSimpleRepository->save($utilisateurSimple,true);

                    // On retourne la confirmation
                    $response = $this->response($utilisateurSimple);

                }else{
                    $this->setMessage("cette ressource existe deja en base");
                    $this->setStatusCode(300);
                    $response = $this->response(null);

                }
            }catch (\Exception $exception){
                $this->setMessage($exception);
                $response = $this->response(null);
            }


        return $response;
        }


    #[Route('/update/{id}', name: 'api_utilisateurSimple_update', methods: ['POST'])]
    /**
     * Permet de mettre à jour une utilisateurSimple.
     *
     * @OA\Tag(name="UtilisateurSimple")
     * @Security(name="Bearer")
     */
    public function update(Request $request,UtilisateurSimpleRepository $utilisateurSimpleRepository,$id)
    {
       try{
           $data = json_decode($request->getContent());

           $utilisateurSimple= $utilisateurSimpleRepository->find($id);
           if($utilisateurSimple!= null){

               $utilisateurSimple->setContact($data->contact);
               $utilisateurSimple->setEmail($data->email);
               $utilisateurSimple->setNom($data->nom);
               $utilisateurSimple->setPrenoms($data->prenoms);
               $utilisateurSimple->setUsername($data->username);
               $utilisateurSimple->setPassword($this->hasher->hashPassword($utilisateurSimple, $data->password));
               // On sauvegarde en base
               $utilisateurSimpleRepository->save($utilisateurSimple,true);

               // On retourne la confirmation
               $response = $this->response($utilisateurSimple);

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


    #[Route('/delete/{id}', name: 'api_utilisateurSimple_delete', methods: ['POST'])]
    /**
     * permet de supprimer une utilisateur Simple en offrant un identifiant.
     *
     * @OA\Tag(name="UtilisateurSimple")
     * @Security(name="Bearer")
     */
    public function delete(Request $request,UtilisateurSimpleRepository $utilisateurSimpleRepository,$id)
    {
        try{
            $data = json_decode($request->getContent());

            $utilisateurSimple= $utilisateurSimpleRepository->find($id);
            if($utilisateurSimple!= null){

                $utilisateurSimpleRepository->remove($utilisateurSimple,true);

                // On retourne la confirmation
                $response = $this->response($utilisateurSimple);

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


    #[Route('/active/{id}', name: 'api_utilisateurSimple_active', methods: ['GET'])]
    /**
     * Permet d'activer une utilisateur Simple en offrant un identifiant.
     * @OA\Tag(name="UtilisateurSimple")
     * @Security(name="Bearer")
     */
    public function active(?UtilisateurSimple $utilisateurSimple,UtilisateurSimpleRepository $utilisateurSimpleRepository)
    {
        /*  $utilisateurSimple= $utilisateurSimpleRepository->find($id);*/
        try{
            if($utilisateurSimple){

                //$utilisateurSimple->setCode("555"); //TO DO nous ajouter un champs active
                $utilisateurSimpleRepository->save($utilisateurSimple,true);
                $response = $this->response($utilisateurSimple);
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


    #[Route('/active/multiple', name: 'api_utilisateurSimple_active_multiple', methods: ['POST'])]
    /**
     * Permet de faire une desactivation multiple.
     *
     * @OA\Tag(name="UtilisateurSimple")
     * @Security(name="Bearer")
     */
    public function multipleActive(Request $request,UtilisateurSimpleRepository $utilisateurSimpleRepository){
        try{
            $data = json_decode($request->getContent());

            $listeUtilisateurSimples = $utilisateurSimpleRepository->findAllByListId($data->ids);
            foreach ($listeUtilisateurSimples as $listeUtilisateurSimple) {
                //$listeUtilisateurSimple->setCode("555");  //TO DO nous ajouter un champs active
                $utilisateurSimpleRepository->save($listeUtilisateurSimple,true);
            }
            
            $response = $this->response(null);
        }catch (\Exception $exception){
            $this->setMessage($exception.toString());
            $response = $this->response(null);
        }
        return $response;
    }
}
