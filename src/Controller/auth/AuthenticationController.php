<?php

namespace App\Controller\auth;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Prime\FlasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

final class AuthenticationController extends AbstractController
{
    #[Route('/authentication/signin', name: 'app_authentication_signin')]
    public function signin(
        Request $request,
        FlasherInterface $flasher,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $lastname = $request->request->get('lastname');
            $firstname = $request->request->get('firstname');
            $password_first = $request->request->get('password-first');
            $password_second = $request->request->get('password-second');

            // Basic validation
            if (empty($email) || empty($lastname) || empty($firstname) || empty($password_first) || empty($password_second)) {
                $flasher->error('Remplissez tous les champs.');

                return $this->redirectToRoute('app_authentication_signin');
            }

            if ($password_first !== $password_second) {
                $flasher->error('Les mots de passe doivent être identiques.');

                return $this->redirectToRoute('app_authentication_signin');
            }

            // Check if the user already exists
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $flasher->error('Email already registered.');

                return $this->redirectToRoute('app_authentication_signin');
            }

            // Create new user
            $user = new User();
            $user->setEmail($email);
            $user->setLastname($lastname);
            $user->setFirstname($firstname);
            $user->setPasswordSalt(10);
            $user->setStatus('ACTIVE');

            // Hash the password
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $password_first
            );
            $user->setPassword($hashedPassword);

            // Persist the user
            $entityManager->persist($user);
            $entityManager->flush();

            $flasher->success('Enregistrement reussie');

            // Create the cookie (example: storing user ID or token)
            $cookie = Cookie::create('user_session')
            ->withValue($user->getId())
            ->withExpires(strtotime('+7 days'))
            ->withSecure(true)
            ->withHttpOnly(true);

            $response = $this->redirectToRoute('app_authentication_login');
            $response->headers->setCookie($cookie);

            return $response;
        }

        return $this->render('authentication/index.html.twig', []);
    }

    #[Route('/authentication/login', name: 'app_authentication_login')]
    public function login(
        Request $request,
        FlasherInterface $flasher,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user || !$passwordEncoder->isPasswordValid($user, $password)) {
                $flasher->error('Email ou mot de passe incorrect');

                return $this->redirectToRoute('app_authentication_login');
            }

            $flasher->success('Bon retour parmis nous '.$user->getLastname());

            $cookie = Cookie::create('user_session')
            ->withValue($user->getId())
            ->withExpires(strtotime('+7 days'))
            ->withSecure(true)
            ->withHttpOnly(true);

            $response = $this->redirectToRoute('app_home');
            $response->headers->setCookie($cookie);

            return $response;
        }

        return $this->render('authentication/login.html.twig', []);
    }

    #[Route('/authentication/logout', name: 'app_authentication_logout')]
    public function logout(): Response
    {
        // Create a response that removes the cookie
        $response = $this->redirectToRoute('app_authentication_login');

        // Invalidate the cookie by setting it with a past expiration date
        $response->headers->setCookie(
            Cookie::create('user_session')
                ->withValue('')
                ->withExpires(strtotime('-1 day'))
                ->withSecure(true)
                ->withHttpOnly(true)
        );

        return $response;
    }
}
