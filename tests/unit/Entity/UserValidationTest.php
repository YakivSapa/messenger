<?php

namespace App\Tests\Unit\Entity;

use App\Tests\AbstractTestCase;
use App\Entity\User;

class UserValidationTest extends AbstractTestCase
{
    public function testUserCreation(): void
    {
        $testEmail = $this->generateRandomEmail();

        $user = new User();
        $user->setDisplayName('Test User');
        $user->setUsername('testuser');
        $user->setEmail($testEmail);
        $user->setPassword('securepassword');

        $this->assertEquals('Test User', $user->getDisplayName());
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals($testEmail, $user->getEmail());
        $this->assertEquals('securepassword', $user->getPassword());
    }
    public function testEmailValidation(): void
    {
        $testEmail = $this->generateRandomEmail();

        $user = new User();
        $user->setEmail($testEmail);

        $this->assertValidEmail($user->getEmail(), 'The email address is not valid.');
    }

}
