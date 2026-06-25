<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class CommunitySearchUserController extends AbstractController
{
    #[Route('/community/search/user', name: 'app_community_search_user', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, #[CurrentUser] ?User $currentUser): Response
    {
        $query = $request->query->get('q', '');
        $users = [];

        if (!empty($query) && strlen($query) >= 2) {
            $users = $userRepository->searchUsers($query);
        }
        return $this->render('community_search_user/index.html.twig', [
            'users' => $users,
            'searchQuery' => $query,
            'currentUser' => $currentUser
        ]);
    }
}
