<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile/{uuid}', name: 'app_profile')]
    public function index(string $uuid, UserRepository $repository): Response
    {
        $profile = $repository->findOneBy(['uuid' => $uuid]);
        if (!$profile) {
            throw $this->createNotFoundException();
        }
        return $this->render('profile/index.html.twig', [
            'profile' => $profile,
        ]);
    }
}
