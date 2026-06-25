<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

final class CommunityController extends AbstractController
{
    #[Route('/community', name: 'app_community')]
    public function index(UserRepository $userRepository): Response
    {
        $verifiedUsersStats = $userRepository->countVerifiedUsers();
        return $this->render('community/index.html.twig', [
            'verifiedUsersStats' => $verifiedUsersStats
        ]);
    }
}
