<?php

namespace App\Tests\Application\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\DomCrawler\Form;

class RegistrationControllerTest extends AbstractWebTestCase
{
    public function testRegistrationFormIsDisplayed(): void
    {
        $crawler = $this->client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSame('Register', $crawler->filter('#page-title')->text());
        $this->assertCount(1, $crawler->filter('form[name="registration_form"]'));
    }

    public function testUserCanRegister(): void
    {
        $email = 'new-user@example.com';
        $crawler = $this->client->request('GET', '/register');
        $form = $this->registrationForm($crawler);
        $form['registration_form[email]'] = $email;
        $form['registration_form[username]'] = 'newuser';
        $form['registration_form[displayName]'] = 'New User';
        $form['registration_form[plainPassword]'] = 'password123';
        $form['registration_form[agreeTerms]'] = '1';

        $this->client->submit($form);

        $this->assertResponseRedirects('/verify/resend?email='.$email);

        $user = static::getContainer()
            ->get(UserRepository::class)
            ->findOneBy(['email' => $email]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame('newuser', $user->getUsername());
        $this->assertSame('New User', $user->getDisplayName());
        $this->assertNotSame('password123', $user->getPassword());
    }

    public function testInvalidRegistrationDoesNotCreateUser(): void
    {
        $email = 'invalid-user@example.com';
        $crawler = $this->client->request('GET', '/register');
        $form = $this->registrationForm($crawler);
        $form['registration_form[email]'] = $email;
        $form['registration_form[username]'] = 'newuser';
        $form['registration_form[displayName]'] = 'New User';
        $form['registration_form[plainPassword]'] = 'short';

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString('You should agree to our terms.', $this->client->getResponse()->getContent());
        $this->assertNull(
            static::getContainer()
                ->get(UserRepository::class)
                ->findOneBy(['email' => $email])
        );
    }

    public function testDuplicateUsernameIsRejected(): void
    {
        $existingUser = new User();
        $existingUser
            ->setEmail('existing-user@example.com')
            ->setUsername('takenuser')
            ->setDisplayName('Existing User')
            ->setPassword('hashed-password');
        $this->persist($existingUser);

        $crawler = $this->client->request('GET', '/register');
        $form = $this->registrationForm($crawler);
        $form['registration_form[email]'] = 'another-user@example.com';
        $form['registration_form[username]'] = 'takenuser';
        $form['registration_form[displayName]'] = 'Another User';
        $form['registration_form[plainPassword]'] = 'password123';
        $form['registration_form[agreeTerms]'] = '1';

        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422);
        $this->assertStringContainsString('Username is already taken.', $this->client->getResponse()->getContent());
        $this->assertNull(
            static::getContainer()
                ->get(UserRepository::class)
                ->findOneBy(['email' => 'another-user@example.com'])
        );
    }

    private function registrationForm(\Symfony\Component\DomCrawler\Crawler $crawler): Form
    {
        return $crawler->selectButton('Register')->form();
    }
}
