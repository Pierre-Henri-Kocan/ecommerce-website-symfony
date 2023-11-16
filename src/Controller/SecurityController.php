<?php

namespace App\Controller;

use App\Form\NewPasswordFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'lastUsername' => $lastUsername, 
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/forgotten', name: 'forgotten_password')]
    public function forgottenPaswword(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $em, SendMailService $mail): Response
    {
        $form = $this->createForm(ResetPasswordFormType::class);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // On vérifie si le mail de l'utilisateur est en base
            $user = $userRepository->findOneByEmail($form->get('email')->getData());
            
            if ($user) {
                // On génère un token de réinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $em->persist($user);
                $em->flush();

                // On génère un lien de réinitialisation de mot de passe
                $url = $this->generateUrl('reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                
                // On créé les données du mail
                $context = [
                    'url' => $url,
                    'user' => $user
                ];

                // On envoie le mail
                $mail->send('no-reply@kommercial.com', $user->getEmail(), 'Réinitialisation de mot de passe', 'password_reset', $context);

                $this->addFlash('success', 'Email envoyé avec succès');
                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('danger', 'Un problème est survenu');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'passwordForm' => $form->createView()
        ]);
    }

    #[Route(path: '/forgotten/{token}', name: 'reset_password')]
    public function resetPassword(string $token, Request $request, UserRepository $userRepository, EntityManagerInterface $em, UserPasswordHasherInterface $passwwordHasher): Response
    {
        // On vérifie si on a ce token dans la BDD
        $user = $userRepository->findOneByResetToken($token);
        
        if ($user) {
            $form = $this->createForm(NewPasswordFormType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // On efface le token
                $user->setResetToken('');
                $user->setPassword($passwwordHasher->hashPassword($user, $form->get('password')->getData()));
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'Mot de passe modifié avec succès');
                return $this->redirectToRoute('app_login');
            }


            return $this->render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);            

            $this->addFlash('danger', 'Token invalide');
            return $this->redirectToRoute('app_login');
        }

        $this->addFlash('danger', 'Un problème est survenu');
        return $this->redirectToRoute('app_login');
    }

}
