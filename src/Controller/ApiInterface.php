<?php
namespace App\Controller;

use App\Service\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ApiInterface extends AbstractController
{
    use FileTrait;

    protected const UPLOAD_PATH = 'media';
    protected $security;
    protected  $hasher;

    public function __construct(Security $security,UserPasswordHasherInterface $hasher)
    {

        $this->security = $security;
        $this->hasher = $hasher;

    }


    /**
     * @var integer HTTP status code - 200 (OK) by default
     */
    protected $statusCode = 200;
    protected $message ="Operation effectuée avec succes";

    /**
     * Gets the value of statusCode.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the value of statusCode.
     *
     * @param integer $statusCode the status code
     *
     * @return self
     */
    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }
    protected function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function response($data, $headers = [])
    {
        // On spécifie qu'on utilise l'encodeur JSON
        $encoders = [new JsonEncoder()];

        // On instancie le "normaliseur" pour convertir la collection en tableau
        $normalizers = [new ObjectNormalizer()];

        // On instancie le convertisseur
        $serializer = new Serializer($normalizers, $encoders);
        if($data == null){
            $arrayData = [
                'data'=>null,
                'message'=>$this->getMessage(),
                'status'=>$this->getStatusCode()
            ];
        }
        else{
            $arrayData = [
                'data'=>$data,
                'message'=>$this->getMessage(),
                'status'=>$this->getStatusCode()
            ];
        }

        // On convertit en json
        $jsonContent = $serializer->serialize($arrayData, 'json', [
            'circular_reference_handler' => function ($object) {
                return  $object->getId();
            },

        ]);

        // On instancie la réponse
        $response = new Response($jsonContent);

        // On ajoute l'entête HTTP
        $response->headers->set('Content-Type', 'application/json');
        return $response;
        //return new JsonResponse($response, $this->getStatusCode(), $headers);
    }

}