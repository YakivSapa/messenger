<?php

namespace App\Tests\Unit\Entity;

use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Tests\AbstractTestCase;

class ResetPasswordRequestTest extends AbstractTestCase
{
    public function testConstructorInitializesRequestData(): void
    {
        $user = new User();
        $expiresAt = new \DateTimeImmutable('+1 hour');
        $selector = 'selector';
        $hashedToken = 'hashed-token';

        $request = new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);

        $this->assertNull($request->getId());
        $this->assertSame($user, $request->getUser());
        $this->assertSame($expiresAt, $request->getExpiresAt());
        $this->assertSame($selector, $request->getSelector());
        $this->assertSame($hashedToken, $request->getHashedToken());
        $this->assertInstanceOf(\DateTimeInterface::class, $request->getRequestedAt());
        $this->assertFalse($request->isExpired());
    }

    public function testRequestWithPastExpirationIsExpired(): void
    {
        $request = new ResetPasswordRequest(
            new User(),
            new \DateTimeImmutable('-1 minute'),
            'selector',
            'hashed-token',
        );

        $this->assertTrue($request->isExpired());
    }
}
