<?php

namespace App\Tests\Application\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTestCase;
use App\Repository\ResetPasswordRequestRepository;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordControllerTest extends AbstractWebTestCase
{
    public function testRequestFormIsDisplayed(): void
    {
        $crawler = $this->client->request('GET', '/reset-password');

        $this->assertResponseIsSuccessful();
        $this->assertSame('Reset your password', $crawler->filter('h1')->text());
        $this->assertCount(1, $crawler->filter('form[name="reset_password_request_form"]'));
    }

    public function testInvalidRequestFormIsRejected(): void
    {
        $crawler = $this->client->request('GET', '/reset-password');
        $form = $crawler->selectButton('Send password reset email')->form();
        $form['reset_password_request_form[email]'] = '';

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString('Please enter your email', $this->client->getResponse()->getContent());
    }

    public function testUnknownEmailRedirectsToCheckEmailWithoutSendingMail(): void
    {
        $crawler = $this->client->request('GET', '/reset-password');
        $form = $crawler->selectButton('Send password reset email')->form();
        $form['reset_password_request_form[email]'] = 'unknown@example.com';

        $this->client->submit($form);

        $this->assertResponseRedirects('/reset-password/check-email');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('If an account matching your email exists', $this->client->getResponse()->getContent());
    }

    public function testKnownEmailSendsResetEmailAndRedirectsToCheckEmail(): void
    {
        $user = $this->createUser('reset@example.com');
        $crawler = $this->client->request('GET', '/reset-password');
        $form = $crawler->selectButton('Send password reset email')->form();
        $form['reset_password_request_form[email]'] = $user->getEmail();

        $this->client->submit($form);

        $this->assertResponseRedirects('/reset-password/check-email');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertNotNull(
            static::getContainer()
                ->get(ResetPasswordRequestRepository::class)
                ->findOneBy(['user' => $user]),
        );
    }

    public function testResetWithoutTokenReturnsNotFound(): void
    {
        $this->client->request('GET', '/reset-password/reset');

        $this->assertResponseStatusCodeSame(404);
        $this->assertStringContainsString('No reset password token found', $this->client->getResponse()->getContent());
    }

    public function testInvalidTokenRedirectsToRequestForm(): void
    {
        $this->client->request('GET', '/reset-password/reset/invalid-token');
        $this->assertResponseRedirects('/reset-password/reset');

        $this->client->request('GET', '/reset-password/reset');

        $this->assertResponseRedirects('/reset-password');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('There was a problem validating your password reset request', $this->client->getResponse()->getContent());
    }

    public function testValidTokenChangesPasswordAndRedirectsToLogin(): void
    {
        $user = $this->createUser('change-password@example.com', 'old-password');
        $resetToken = static::getContainer()
            ->get(ResetPasswordHelperInterface::class)
            ->generateResetToken($user);

        $this->client->request('GET', '/reset-password/reset/'.$resetToken->getToken());
        $this->assertResponseRedirects('/reset-password/reset');

        $crawler = $this->client->followRedirect();
        $form = $crawler->selectButton('Reset password')->form();
        $form['change_password_form[plainPassword][first]'] = 'New-strong-password-123!';
        $form['change_password_form[plainPassword][second]'] = 'New-strong-password-123!';

        $this->client->submit($form);

        $this->assertResponseRedirects('/login');
        $this->entityManager->clear();
        $updatedUser = static::getContainer()
            ->get(UserRepository::class)
            ->findOneBy(['email' => $user->getEmail()]);

        $this->assertNotNull($updatedUser);
        $this->assertNotSame('old-password', $updatedUser->getPassword());
        $this->assertTrue(password_verify('New-strong-password-123!', $updatedUser->getPassword()));
    }

    private function createUser(string $email, string $password = 'password123'): User
    {
        $user = new User();
        $user
            ->setEmail($email)
            ->setUsername(str_replace(['@', '.'], '-', $email))
            ->setDisplayName('Reset Test User')
            ->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $this->persist($user);

        return $user;
    }
}
