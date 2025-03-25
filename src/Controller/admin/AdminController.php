<?php

namespace App\Controller\admin;

use App\Repository\ItemRepository;
use App\Repository\WishlistRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    

    #[Route('/admin/top3items', name: 'top3_items')]

    public function top3Items(
        Request $request,
        WishlistRepository $wishlistRepository,
        ItemRepository $itemRepository
    ): Response {
        // 1) get all wishlist
        $allWishlists = $wishlistRepository->findAll();

        // 2) select wishlistId
        $selectedWishlistId = $request->query->get('wishlistId');

        $topItems = [];
        $selectedWishlist = null;

        // 3) check correspond top-3 items
        if ($selectedWishlistId) {
            $selectedWishlist = $wishlistRepository->find($selectedWishlistId);

            if ($selectedWishlist) {
                
                $topItems = $itemRepository->findTop3ByWishlist($selectedWishlist);
            }
        }

        return $this->render('admin/ItemRank.html.twig', [
            'wishlists' => $allWishlists,   // drop menu
            'selectedWishlist' => $selectedWishlist,
            'topItems' => $topItems,
        ]);
    }

    #[Route('/admin/top3wishlists', name: 'top3_wishlists')]
    public function top3Wishlists(WishlistRepository $wishlistRepository): Response
    {
        // the sorted list of the top-3 wishlists by total value of purchased gifts
        $top3 = $wishlistRepository->findTop3ListsByValue();

        return $this->render('admin/WishListRank.html.twig', [
            'top3Wishlists' => $top3,
        ]);
    }
}
