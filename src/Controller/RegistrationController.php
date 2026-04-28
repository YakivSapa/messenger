<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, VerifyEmailHelperInterface $verifyEmailHelper, MailerInterface $mailer, BodyRendererInterface $bodyRenderer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            // $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            // $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // $entityManager->persist($user);
            // $entityManager->flush();

            // $signatureComponents = $verifyEmailHelper->generateSignature(
            //     'app_verify_email',
            //     $user->getId(),
            //     $user->getEmail(),
            //     ['id' => $user->getId()]
            // );
            $email = (new Email())
                ->from('no-reply@example.com')
                ->to('test@example.com')
                ->subject('Registration of a new account')
                ->text('Please click the following link to verify your email: ');
            $bodyRenderer->render($email);
            $mailer->send($email);
            // return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
    #[Route('/verify', name: 'app_verify_email')]
    public function verify(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
