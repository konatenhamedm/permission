<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\ConfigAppRepository;
use App\Repository\EmployeRepository;
use App\Repository\UtilisateurRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Service\FormError;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_default');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/oublie-pass', name: 'forget_password')]
    public function forgetPassword(
        Request $request,
        FormError $formError,
        UtilisateurRepository $utilisateurRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        SendMailService $sendMailService,
        EmployeRepository $employeRepository,
        ConfigAppRepository $configAppRepository
    ): Response {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $response = [];
            $redirect = $this->generateUrl('app_login');
            $employe = $employeRepository->findOneByAdresseMail($form->get('email')->getData());
            $user = $utilisateurRepository->findOneBy(['employe' => $employe]);
            //dd($user);

            if ($form->isValid()) {

                if ($user) {

                    $token = $tokenGenerator->generateToken();
                    $user->setResetToken($token);
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $fichier = $configAppRepository->findOneBy(['entreprise' => $employe->getEntreprise()])->getLogo();

                    $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                    $context = compact('url', 'user', 'fichier');

                    // TO DO
                    $sendMailService->send(
                        //'konatefvaly@gmail.com',
                        'konatenhamed@ufrseg.enig-sarl.com',
                        $employe->getAdresseMail(),
                        'reinitialisation',
                        'password_reset',
                        $context
                    );
                    $this->addFlash('warning ', 'email envoyé avec success');
                    return  $this->redirectToRoute('app_login');
                }
                $this->addFlash('danger', 'Un probleme est survenu');
                return $this->redirectToRoute('app_login');
            }
        }
        return $this->render(
            'security/reset_password_request.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    #[Route(path: '/oublie-pass/{token}', name: 'reset_pass')]
    public function resetPassword(
        string $token,
        Request $request,
        UtilisateurRepository $utilisateurRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $hasher,


    ): Response {
        $user = $utilisateurRepository->findOneByResetToken($token);

        if ($user) {
            $form = $this->createForm(ResetPasswordFormType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPassword($hasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                ));
                $user->setResetToken('');

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Mot de passe changé avec success');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/reset_password.html.twig', [
                'form' => $form->createView()
            ]);
        }
        $this->addFlash('danger', 'Jeton invalide');
        return $this->redirectToRoute('app_login');
    }
}
