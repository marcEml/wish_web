<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Flasher\Prime\FlasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Membership;
use App\Repository\MembershipRepository;
use App\Form\MembershipType;
use App\Entity\Wishlist;

#[Route('/membership')]
final class MembershipController extends AbstractController
{
    #[Route(name: 'app_membership_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userToken = (int)$request->cookies->get('user_session');
        // Get memberships for wishlists owned by the current user
        $userMemberships = $entityManager->getRepository(Membership::class)->findBy([
            'wishlist' => $entityManager->getRepository(Wishlist::class)->findBy(['user' => $userToken])
        ]);

        return $this->render('membership/index.html.twig', [
            'memberships' => $userMemberships,
        ]);
    }

    #[Route('/new', name: 'app_membership_new', methods: ['GET', 'POST'])]
    public function invite(Request $request, FlasherInterface $flasher, EntityManagerInterface $entityManager): Response
    {
        $membership = new Membership();
        $form = $this->createForm(MembershipType::class, $membership, array('userId' => $request->cookies->get('user_session')));
        $form->handleRequest($request);

        // Handles form submission
        if ($form->isSubmitted() && $form->isValid()) {
            $wishlistId = $membership->getWishlist()->getId();
            $userId = $membership->getUser()->getId();

            // Basic validation
            if (empty($wishlistId) || empty($userId)) {
                $flasher->error('Remplissez tous les champs.');

                return $this->redirectToRoute('app_membership_new');
            }

            // Check if the user is the owner of the wishlist
            $wishlist = $entityManager->getRepository(Wishlist::class)->find($wishlistId);
            if ($wishlist->getUser()->getId() === $userId) {
                $flasher->error('Vous ne pouvez pas inviter le propriétaire de la wishlist.');

                return $this->redirectToRoute('app_membership_new');
            }

            // Check if the user has already been invited to this wishlist
            $existingMembership = $entityManager->getRepository(Membership::class)->findOneBy(['wishlist' => $wishlistId, 'user' => $userId]);
            if (!empty($existingMembership)) {
                if ($existingMembership->getStatus() === 'INVITED') {
                    $flasher->error('Cet utilisateur a déjà été invité à cette wishlist.');

                    return $this->redirectToRoute('app_membership_new');
                }
                if ($existingMembership->getStatus() === 'DECLINED') {
                    $flasher->error('Cet utilisateur a déjà refusé de rejoindre cette wishlist.');

                    return $this->redirectToRoute('app_membership_new');
                }
                if ($existingMembership->getStatus() === 'ACCEPTED') {
                    $flasher->error('Cet utilisateur a déjà rejoint cette wishlist.');

                    return $this->redirectToRoute('app_membership_index');
                }
            }

            // Create new membership with status 'INVITED'
            $membership->setStatus('INVITED');

            // Persist the new membership
            $entityManager->persist($membership);
            $entityManager->flush();

            // Display a flash of success
            $flasher->success('Invitation envoyée !');

            $response = $this->redirectToRoute('app_home');

            return $response;
        }

        return $this->render('membership/new.html.twig', [
            'membership' => $membership,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/accept', name: 'app_membership_accept', methods: ['GET'])]
    public function accept(Request $request, FlasherInterface $flasher, EntityManagerInterface $entityManager): Response
    {
        $wishlistId = (int)$request->query->get('wishlist');
        $userToken = (int)$request->cookies->get('user_session');

        if (!$userToken) {
            $flasher->error('Session expirée, veuillez vous reconnecter.');
            return $this->redirectToRoute('app_login');
        }
        $membership = $entityManager->getRepository(Membership::class)->findOneBy(['wishlist' => $wishlistId, 'user' => $userToken]);

        if ($membership === null) {
            $flasher->error('Invitation introuvable.');

            return $this->redirectToRoute('app_home');
        }

        if ($membership->getUser()->getId() !== $userToken) {
            $flasher->error('Vous n\'êtes pas autorisé à rejoindre cette wishlist.');

            return $this->redirectToRoute('app_home');
        }

        if ($membership->getStatus() === 'ACCEPTED') {
            $flasher->error('Vous avez déjà accepté de rejoindre cette wishlist.');

            return $this->redirectToRoute('app_home');
        }

        if ($membership->getStatus() === 'DECLINED') {
            $flasher->error('Vous avez déjà refusé de rejoindre cette wishlist.');

            return $this->redirectToRoute('app_home');
        }

        $membership->setStatus('ACCEPTED');
        $entityManager->flush();

        $flasher->success('Vous avez rejoint la wishlist !');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/decline', name: 'app_membership_decline', methods: ['GET'])]
    public function decline(Request $request, FlasherInterface $flasher, EntityManagerInterface $entityManager): Response
    {

        $wishlistId = (int)$request->query->get('wishlist');
        $userToken = (int)$request->cookies->get('user_session');

        if (!$userToken) {
            $flasher->error('Session expirée, veuillez vous reconnecter.');
            return $this->redirectToRoute('app_login');
        }
        $membership = $entityManager->getRepository(Membership::class)->findOneBy(['wishlist' => $wishlistId, 'user' => $userToken]);

        if ($membership === null) {
            $flasher->error('Invitation introuvable.');

            return $this->redirectToRoute('app_home');
        }

        if ($membership->getUser()->getId() !== $userToken) {
            $flasher->error('Vous n\'êtes pas autorisé à rejoindre cette wishlist.');

            return $this->redirectToRoute('app_home');
        }

        if ($membership->getStatus() === 'ACCEPTED') {
            $flasher->error('Vous avez déjà accepté de rejoindre cette wishlist.');

            return $this->redirectToRoute('app_home');
        }

        if ($membership->getStatus() === 'DECLINED') {
            $flasher->error('Vous avez déjà refusé de rejoindre cette wishlist.');

            return $this->redirectToRoute('app_home');
        }

        $membership->setStatus('DECLINED');
        $entityManager->flush();

        $flasher->success('Vous avez refusé l\'invitation.');

        return $this->redirectToRoute('app_home');
    }
}
