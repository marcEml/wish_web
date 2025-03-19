<?php

namespace App\Controller\membership;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Flasher\Prime\FlasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Membership;
use App\Entity\Wishlist;

final class MembershipController extends AbstractController
{
    #[Route('/membership', name: 'app_membership_new')]
    public function invite(Request $request, FlasherInterface $flasher, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $wishlistId = $request->request->get('wishlist-id');
            $userId = $request->request->get('user-id');

            // Basic validation
            if (empty($wishlistId) || empty($userId)) {
                $flasher->error('Remplissez tous les champs.');

                return $this->redirectToRoute('app_membership_new');
            }

            // Check if the user has already been invited to this wishlist
            $existingMembership = $entityManager->getRepository(User::class)->findOneBy(['wishlist' => $wishlistId, 'user' => $userId]);
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

                return $this->redirectToRoute('app_membership_new');
            }

            // Create new membership with status 'INVITED'
            $membership = new Membership();
            $membership->setWishlist($entityManager->getReference(Wishlist::class, $wishlistId));
            $membership->setUser($entityManager->getReference(User::class, $userId));
            $membership->setStatus('INVITED');

            // Persist the new membership
            $entityManager->persist($membership);
            $entityManager->flush();

            // Display a flash of success
            $flasher->success('Invitation envoyée !');

            $response = $this->redirectToRoute('app_home');

            return $response;
        }
    }

    #[Route('/membership/accept/{id}', name: 'app_membership_accept')]
    public function accept(int $id, FlasherInterface $flasher, EntityManagerInterface $entityManager): Response
    {
        $membership = $entityManager->getRepository(Membership::class)->find($id);

        if ($membership === null) {
            $flasher->error('Invitation introuvable.');

            return $this->redirectToRoute('app_home');
        }

        $membership->setStatus('ACCEPTED');
        $entityManager->flush();

        $flasher->success('Vous avez rejoint la wishlist !');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/membership/decline/{id}', name: 'app_membership_decline')]
    public function decline(int $id, FlasherInterface $flasher, EntityManagerInterface $entityManager): Response
    {
        $membership = $entityManager->getRepository(Membership::class)->find($id);

        if ($membership === null) {
            $flasher->error('Invitation introuvable.');

            return $this->redirectToRoute('app_home');
        }

        $membership->setStatus('DECLINED');
        $entityManager->flush();

        $flasher->success('Vous avez refusé l\'invitation.');

        return $this->redirectToRoute('app_home');
    }
}
