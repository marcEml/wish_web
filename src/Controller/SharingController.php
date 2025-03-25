<?php

namespace App\Controller;

use App\Entity\Wishlist;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Repository\WishlistRepository;

final class SharingController extends AbstractController
{
    #[Route('/sharing/{id}', name: 'app_sharing')]
    public function index(Wishlist $wishlist): Response
    {
        $publicToken = hash('sha256', $wishlist->getId() . '_public_secret');
        $privateToken = base64_encode($wishlist->getId() . '|private_secret');

        $publicLink = $this->generateUrl('app_public_wishlist', ['token' => $publicToken], UrlGeneratorInterface::ABSOLUTE_URL);
        $privateLink = $this->generateUrl('app_private_wishlist', ['token' => $privateToken], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('sharing/index.html.twig', [
            'wishlist' => $wishlist,
            'public_link' => $publicLink,
            'private_link' => $privateLink,
        ]);
    }

    #[Route('/wishlist/public/{token}', name: 'app_public_wishlist')]
    public function publicView(string $token): Response
    {
        // Validate token logic here...
        // Fetch the wishlist based on token or reverse calculate
        return $this->render('wishlist/public.html.twig', ['token' => $token]);
    }

    #[Route('/wishlist/private/{token}', name: 'app_private_wishlist')]
    public function privateView(string $token, WishlistRepository $wishlistRepository): Response
    {
        $decoded = base64_decode($token);

        if (!$decoded || !str_contains($decoded, '|')) {
            throw $this->createNotFoundException('Invalid token');
        }
    
        [$wishlistId, $secret] = explode('|', $decoded);
    
        // Optional: Validate the secret if you want extra security
        if ($secret !== 'private_secret') {
            throw $this->createAccessDeniedException('Invalid secret');
        }
    
        // Fetch the wishlist by ID
        $wishlist = $wishlistRepository->find($wishlistId);
    
        if (!$wishlist) {
            throw $this->createNotFoundException('Wishlist not found');
        }

        $items = $wishlist->getItems();

        return $this->render('sharing/private.html.twig', [
            'wishlist' => $wishlist,
            'items' => $items,
        ]);
    }
}
