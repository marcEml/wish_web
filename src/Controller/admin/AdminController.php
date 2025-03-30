<?php

namespace App\Controller\admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ItemRepository;
use App\Repository\WishlistRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin/users', name: 'admin_users_management')]
    public function users_management(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('admin/users_management.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/remove-user/{id}', name: 'admin_remove_user', methods: 'POST')]
    public function removeUser(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_users_management');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'User removed successfully.');
        return $this->redirectToRoute('admin_users_management');
    }

    #[Route('/admin/lock-user/{id}', name: 'admin_lock_user')]
    public function lockUser(int $id, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');
            return $this->redirectToRoute('admin_users_management');
        }

        if ($user->isLocked()) {
            $user->setIsLocked(false);
            $entityManager->flush();
            $this->addFlash('success', 'User unlocked successfully.');
            return $this->redirectToRoute('admin_users_management');
        } else {
            $user->setIsLocked(true);
            $entityManager->flush();
            $this->addFlash('success', 'User locked successfully.');
            return $this->redirectToRoute('admin_users_management');
        }
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    

    #[Route('/admin/top3items', name: 'top3_items')]
    /*public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }*/
    public function top3Items(
        Request $request,
        WishlistRepository $wishlistRepository,
        ItemRepository $itemRepository
    ): Response {
        // 1) 获取全部 wishlist，供下拉菜单使用
        $allWishlists = $wishlistRepository->findAll();

        // 2) 读取 URL 或表单中传递的 wishlistId
        $selectedWishlistId = $request->query->get('wishlistId');

        $topItems = [];
        $selectedWishlist = null;

        // 3) 如果用户选择了某个 wishlist，则查询对应的 top-3 items
        if ($selectedWishlistId) {
            $selectedWishlist = $wishlistRepository->find($selectedWishlistId);

            if ($selectedWishlist) {
                // 在 ItemRepository 中新增一个方法: findTop3ByWishlist($wishlist)
                $topItems = $itemRepository->findTop3ByWishlist($selectedWishlist);
            }
        }

        return $this->render('admin/ItemRank.html.twig', [
            'wishlists' => $allWishlists,   // 下拉菜单选项
            'selectedWishlist' => $selectedWishlist,
            'topItems' => $topItems,
        ]);
    }

    #[Route('/admin/top3wishlists', name: 'top3_wishlists')]
    public function top3Wishlists(WishlistRepository $wishlistRepository): Response
    {
        
        $top3 = $wishlistRepository->findTop3ListsByValue();

        // 如果使用上面第二种写法 (getArrayResult)，$top3 是数组，需要手动遍历
        // $top3[0]['wishlist']  // Wishlist 实体
        // $top3[0]['totalValue'] // float

        return $this->render('admin/WishListRank.html.twig', [
            'top3Wishlists' => $top3,
        ]);
    }
}
