<?php

namespace App\Controller;

use App\Entity\Wishlist;
use App\Form\WishlistType;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Flasher\Prime\FlasherInterface;

#[Route('/wishlist')]
final class WishlistController extends AbstractController
{
    #[Route(name: 'app_wishlist_index', methods: ['GET'])]
    public function index(WishlistRepository $wishlistRepository): Response
    {
        return $this->render('wishlist/index.html.twig', [
            'wishlists' => $wishlistRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_wishlist_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FlasherInterface $flasher, EntityManagerInterface $entityManager): Response
    {
        $userToken = (int)$request->cookies->get('user_session');
        if (empty($userToken)) {
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $userToken]);
        if ($user === null) {
            $flasher->error('Une erreur est survenue. Veuillez réessayer.');
            return $this->redirectToRoute('app_home');
        }

        $wishlist = new Wishlist();
        $form = $this->createForm(WishlistType::class, $wishlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wishlist->setUser($user);
            $entityManager->persist($wishlist);
            $entityManager->flush();

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wishlist/new.html.twig', [
            'wishlist' => $wishlist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_item_index', methods: ['GET'])]
    public function show(WishlistRepository $wishlistRepository, int $id): Response
    {
        $wishlist = $wishlistRepository->find($id);

        if (!$wishlist) {
            throw $this->createNotFoundException('Wishlist not found');
        }

        return $this->render('item/index.html.twig', [
            'items' => $wishlist->getItems(),
            'wishListId' => $wishlist->getId(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_wishlist_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Wishlist $wishlist, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WishlistType::class, $wishlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wishlist/edit.html.twig', [
            'wishlist' => $wishlist,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wishlist_delete', methods: ['POST'])]
    public function delete(Request $request, Wishlist $wishlist, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $wishlist->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($wishlist);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }
}
