<?php


namespace App\Controller;


use App\Controller\FileTrait;
use App\Service\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\Registry;

class BaseController extends AbstractController
{
    use FileTrait;

    protected const UPLOAD_PATH = 'media_entreprise';
    protected $em;
    protected $security;
    protected $menu;
    protected UserPasswordHasherInterface $hasher;
    protected $workflow;
    



    public function __construct(EntityManagerInterface $em,Menu $menu,Security $security,UserPasswordHasherInterface $hasher,Registry $workflow)
    {
        $this->em = $em;
        $this->security = $security;
        $this->menu = $menu;
        $this->hasher = $hasher;
        $this->workflow = $workflow;
    }

   
}