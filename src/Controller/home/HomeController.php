<?php

namespace App\Controller\home;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Membership;
use App\Entity\Wishlist;
use App\Entity\User;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userToken = (int)$request->cookies->get('user_session');

        if (!$userToken) {
            return $this->redirectToRoute('app_authentication_login');
        }

        $pending_memberships = $entityManager->getRepository(Membership::class)->findBy(['user' => $userToken, 'status' => 'INVITED']);
        $accepted_memberships = $entityManager->getRepository(Membership::class)->findBy(['user' => $userToken, 'status' => 'ACCEPTED']);
        $acceptedWishlistIds = array_map(function ($membership) {
            return $membership->getWishlist()->getId();
        }, $accepted_memberships);
        $own_wishlists = $entityManager->getRepository(Wishlist::class)->findBy(['user' => $userToken]);
        $accepted_wishlists = $entityManager->getRepository(Wishlist::class)->findBy(['id' => $acceptedWishlistIds]);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'memberships' => $pending_memberships,
            'accepted_wishlists' => $accepted_wishlists,
            'own_wishlists' => $own_wishlists,
        ]);
    }
}
