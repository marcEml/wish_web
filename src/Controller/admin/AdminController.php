<?php

namespace App\Controller\admin;

use App\Repository\ItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    

    #[Route('/admin/dashboard', name: 'top3_items')]
    /*public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }*/
    public function dashboard(ItemRepository $itemRepository): Response
    {
        $topItems = $itemRepository->findTop3ByPrice();

        return $this->render('admin/ItemRank.html.twig', [
            'controller_name' => 'AdminController',
            'topItems' => $topItems,
        ]);
    }
    /*public function index(ItemRepository $itemRepository): Response
    {
        // 调用自定义方法获取价格最高的前三个 Item
        $topItems = $itemRepository->findTop3ByPrice();

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'topItems' => $topItems,
        ]);
    }*/
    #[Route('/admin/top3wishlists', name: 'top3_wishlists')]
    public function top3Wishlists(WishlistRepository $wishlistRepository): Response
    {
        // 获取总价值最高的前三个愿望清单（含总价值）
        $top3 = $wishlistRepository->findTop3ListsByValue();

        // 如果使用上面第二种写法 (getArrayResult)，$top3 是数组，需要手动遍历
        // $top3[0]['wishlist']  // Wishlist 实体
        // $top3[0]['totalValue'] // float

        return $this->render('admin/WishListRank.html.twig', [
            'top3Wishlists' => $top3,
        ]);
    }
}
