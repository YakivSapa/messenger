<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile/{id<\d+>}', name: 'app_profile')]
    public function index($id, UserRepository $repository): Response
    {
        $profile = $repository->findOneBy(['id' => $id]);
        return $this->render('profile/index.html.twig', [
            'profile' => $profile,
        ]);
    }
}
