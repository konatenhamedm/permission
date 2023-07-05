<?php


namespace App\Controller;


use App\Controller\FileTrait;
use App\Service\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

class BaseController extends AbstractController
{
    use FileTrait;

    protected const UPLOAD_PATH = 'media_entreprise';
    protected $em;
    protected $security;
    protected $menu;
    protected UserPasswordHasherInterface $hasher;


    public function __construct(EntityManagerInterface $em,Menu $menu,Security $security,UserPasswordHasherInterface $hasher)
    {
        $this->em = $em;
        $this->security = $security;
        $this->menu = $menu;
        $this->hasher = $hasher;
    }

   
}