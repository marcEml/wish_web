<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class RootController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userToken = $request->cookies->get('user_session');

        if ($userToken) {
            return $this->forward('App\Controller\home\HomeController::index', [
                'request' => $request,
                'entityManager' => $entityManager,
            ]);
        }

        return $this->forward('App\Controller\LandingController::index');
    }
}
