<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UserAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMailService $mail, JWTService $jwt): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // On génère le JWT de l'utilisateur
            // On créé le Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            // On créé le Payload
            $payload = [
                'user_id' => $user->getId()
            ];

            // On génère le token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // On evoie un mail
            $mail->send(
                'no-reply@kommercial.com',
                $user->getEmail(),
                'Activation de votre compte sur le site Kommercial',
                'register',
                [
                    'user' => $user,
                    'token' => $token
                ]
                );


            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        // On vérifie si le token est valide, qu'il n'a pas expiré et qu'il n'a pas été modifié
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))){
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']);

            if ($user && !$user->getIsVerified()) {
                $user->setIsVerified(true);
                $em->flush($user);

                $this->addFlash('success', 'Profil activé');

                return $this->redirectToRoute('profile_index');
            }
        }

        // Si on rencontre un problème avec le token
        $this->addFlash('danger', 'Le token est invalide ou a expiré');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/reactivate', name: 'resend_verif')]
    public function resendVerif(JWTService $jwt, SendMailService $mail, UserRepository $userRepository): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder àc ette page');
            return $this->redirectToRoute('app_login');
        }

        if ($user->getIsVerified()) {
            $this->addFlash('warning', 'Cet utilisateur est déjà activé');
            return $this->redirectToRoute('profile_index');
        }

            //* On génère le JWT de l'utilisateur
            // On créé le Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            // On créé le Payload
            $payload = [
                'user_id' => $user->getId()
            ];

            // On génère le token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // On evoie un mail
            $mail->send(
                'no-reply@kommercial.com',
                $user->getEmail(),
                'Activation de votre compte sur le site Kommercial',
                'register',
                [
                    'user' => $user,
                    'token' => $token
                ]
                );
            
            $this->addFlash('success', 'Email de vérification envoyé');
            return $this->redirectToRoute('profile_index');
    } 
}
