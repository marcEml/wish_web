<?php

namespace App\Controller;

use App\Entity\Item;
use App\Form\ItemType;
use App\Entity\Wishlist;
use App\Repository\ItemRepository;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Flasher\Prime\FlasherInterface;

#[Route('/item')]
final class ItemController extends AbstractController
{
    #[Route('/wishlist/{id}/items', name: 'app_item_index', methods: ['GET'])]
    public function index(WishlistRepository $wishlistRepository, int $id): Response
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


    #[Route('/new/{id}', name: 'app_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, FlasherInterface $flasher, int $id): Response
    {
        $wishlist = $entityManager->getRepository(Wishlist::class)->find($id);
        if (!$wishlist) {
            throw $this->createNotFoundException('Wishlist not found');
        }
        
        $item = new Item();
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $item->setWishlist($wishlist);
            $entityManager->persist($item);
            $entityManager->flush();

            $flasher->success('Un nouvel objet a été ajouté à la wishlist.');
            return $this->redirectToRoute('app_item_index', ['id' => $id], Response::HTTP_SEE_OTHER);
        }

        return $this->render('item/new.html.twig', [
            'item' => $item,
            'form' => $form,
            'wishListId' => $id,
        ]);
    }

    #[Route('/{id}/{wId}', name: 'app_item_show', methods: ['GET'])]
    public function show(Item $item, int $wId): Response
    {
        return $this->render('item/show.html.twig', [
            'item' => $item,
            'wishListId' => $wId,
        ]);
    }

    #[Route('/{id}/{wId}/edit', name: 'app_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Item $item, EntityManagerInterface $entityManager, int $wId): Response
    {
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_item_index', ['id' => $wId], Response::HTTP_SEE_OTHER);
        }

        return $this->render('item/edit.html.twig', [
            'item' => $item,
            'form' => $form,
            'wishListId' => $wId,
        ]);
    }

    #[Route('/{id}/{wId}', name: 'app_item_delete', methods: ['POST'])]
    public function delete(Request $request, Item $item, EntityManagerInterface $entityManager, int $wId): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($item);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_item_index', ['id' => $wId], Response::HTTP_SEE_OTHER);
    }
}
