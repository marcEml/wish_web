<?php

namespace App\Controller;

use App\Entity\Membership;
use App\Entity\Purchase;
use App\Entity\Wishlist;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Repository\WishlistRepository;
use Symfony\Component\HttpFoundation\Request;
use Flasher\Prime\FlasherInterface;
use Doctrine\ORM\EntityManagerInterface;

final class SharingController extends AbstractController
{
    #[Route('/sharing/{id}', name: 'app_sharing')]
    public function index(Wishlist $wishlist): Response
    {
        $publicToken = base64_encode($wishlist->getId() . '|public_secret');
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
    public function publicView(string $token, EntityManagerInterface $entityManager): Response
    {
        $decoded = base64_decode($token);

        if (!$decoded || !str_contains($decoded, '|')) {
            throw $this->createNotFoundException('Le lien est invalide.');
        }

        [$wishlistId, $secret] = explode('|', $decoded);

        if ($secret !== 'public_secret') {
            throw $this->createAccessDeniedException('Le lien est invalide.');
        }

        // Fetch the wishlist by ID
        $wishlist = $entityManager->getRepository(Wishlist::class)->find($wishlistId);

        if (!$wishlist) {
            throw $this->createNotFoundException('Aucune wishlist correspondante à ce lien n\'a été trouvée.');
        }

        $items = $wishlist->getItems();
        $purchases = [];
        // Initialize an array to store purchases for each item
        foreach ($items as $item) {
            // Find purchases for each item in the wishlist
            $itemPurchases = $entityManager->getRepository(Purchase::class)->findBy(['item' => $item]);
            if (!empty($itemPurchases)) {
                foreach ($itemPurchases as $purchase) {
                    $purchases[] = $purchase;
                }
            }
        }

        return $this->render('sharing/public.html.twig', [
            'wishlist' => $wishlist,
            'items' => $items,
            'purchases' => $purchases,
        ]);
    }

    #[Route('/wishlist/private/{token}', name: 'app_private_wishlist')]
    public function privateView(Request $request, string $token, EntityManagerInterface $entityManager, FlasherInterface $flasher): Response
    {
        $decoded = base64_decode($token);

        if (!$decoded || !str_contains($decoded, '|')) {
            throw $this->createNotFoundException('Le lien est invalide.');
        }

        [$wishlistId, $secret] = explode('|', $decoded);

        if ($secret !== 'private_secret') {
            throw $this->createAccessDeniedException('Le lien est invalide.');
        }

        // Fetch the wishlist by ID
        $wishlist = $entityManager->getRepository(Wishlist::class)->find($wishlistId);

        if (!$wishlist) {
            throw $this->createNotFoundException('Aucune wishlist correspondante à ce lien n\'a été trouvée.');
        }

        $userToken = $request->cookies->get('user_session');
        $user = $entityManager->getRepository(User::class)->find((int)$userToken);

        if (!$user) {
            $flasher->error('Vous devez être connecté pour rejoindre une wishlist.');
            return $this->redirectToRoute('app_authentication_login');
        }

        if ($user === $wishlist->getUser()) {
            $flasher->error('You cannot join your own wishlist');
            return $this->redirectToRoute('app_home');
        }

        $membership = $entityManager->getRepository(Membership::class)->findOneBy(['wishlist' => $wishlistId, 'user' => $user]);

        if ($membership) {
            switch ($membership->getStatus()) {
                case 'ACCEPTED':
                    $flasher->error('Vous avez déjà accepté de rejoindre cette wishlist.');
                    break;
                case 'INVITED':
                    $flasher->error('Vous avez déjà été invité à rejoindre cette wishlist. Regardez vos invitations.');
                    break;
                case 'DECLINED':
                    $flasher->error('Vous avez déjà refusé de rejoindre cette wishlist.');
                    break;
                default:
                    $flasher->error('Vous avez déjà été invité à rejoindre cette wishlist. Regardez vos invitations.');
            }
            return $this->redirectToRoute('app_home');
        }

        $membership = new Membership();
        $membership->setWishlist($wishlist);
        $membership->setUser($user);
        $membership->setStatus('INVITED');
        $entityManager->persist($membership);
        $entityManager->flush();
        $flasher->success('Vous avez été invité à rejoindre la wishlist ! Acceptez ou refusez l\'invitation.');
        return $this->redirectToRoute('app_home');
    }
}
