<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Item;
use App\Entity\Purchase;
use App\Entity\Wishlist;
use App\Form\PurchaseType;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Flasher\Prime\FlasherInterface;

#[Route('/purchase')]
final class PurchaseController extends AbstractController
{
    #[Route(name: 'app_purchase_index', methods: ['GET'])]
    public function index(PurchaseRepository $purchaseRepository): Response
    {
        return $this->render('purchase/index.html.twig', [
            'purchases' => $purchaseRepository->findAll(),
        ]);
    }

    #[Route('/new/{id}', name: 'app_purchase_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FlasherInterface $flasher, EntityManagerInterface $entityManager, int $id): Response
    {
        $purchase = new Purchase();

        // get user id
        $userToken = (int)$request->cookies->get('user_session');
        if (empty($userToken)) {
            $flasher->error('Vous devez être connecté pour téléverser une preuve de paiement.');
            return $this->redirectToRoute('app_authentication_login');
        }

        // set user
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $userToken]);
        $purchase->setUser($user);

        // set item
        $item = $entityManager->getRepository(Item::class)->findOneBy(['id' => $id]);
        $purchase->setItem($item);

        $form = $this->createForm(PurchaseType::class, $purchase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $receiptFile */
            $receiptFile = $form->get('receiptFile')->getData();

            if ($receiptFile) {
                $newFilename = uniqid() . '.' . $receiptFile->guessExtension();

                try {
                    $receiptFile->move(
                        $this->getParameter('receipts_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle exception if something happens during file upload
                }

                $purchase->setReceiptUrl($newFilename);
            }

            $entityManager->persist($purchase);
            $entityManager->flush();

            $wishlist = $entityManager->getRepository(Wishlist::class)->find($item->getWishlist()->getId());
            $publicToken = base64_encode($wishlist->getId() . '|public_secret');
            $publicLink = $this->generateUrl('app_public_wishlist', ['token' => $publicToken], UrlGeneratorInterface::ABSOLUTE_URL);

            return $this->redirectToRoute('app_public_wishlist', ['token' => $publicToken]);
        }

        return $this->render('purchase/new.html.twig', [
            'purchase' => $purchase,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_purchase_show', methods: ['GET'])]
    public function show(Purchase $purchase): Response
    {
        return $this->render('purchase/show.html.twig', [
            'purchase' => $purchase,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_purchase_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Purchase $purchase, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PurchaseType::class, $purchase);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_purchase_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('purchase/edit.html.twig', [
            'purchase' => $purchase,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_purchase_delete', methods: ['POST'])]
    public function delete(Request $request, Purchase $purchase, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $purchase->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($purchase);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_purchase_index', [], Response::HTTP_SEE_OTHER);
    }
}
