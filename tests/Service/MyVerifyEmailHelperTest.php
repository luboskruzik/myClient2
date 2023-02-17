<?php

namespace App\Tests\Service;

//use PHPUnit\Framework\TestCase;
use App\Service\MyVerifyEmailHelper;
use App\Entity\RegisterUser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;


class MyVerifyEmailHelperTest extends KernelTestCase
{
    public function testGetSignedUrl(): string
    {
        self::bootKernel();
        
        $container = static::getContainer();
        
        $helper = $container->get(MyVerifyEmailHelper::class);
        $user = $this->createMock(RegisterUser::class);
        $user
            ->method('getId')
            ->willReturn(1)
            ;
        $user
            ->method('getEmail')
            ->willReturn('john@doe.com')
            ;
        $signedUrl = $helper->getSignedUrl($user);
        $this->assertIsString($signedUrl);
        return $signedUrl;
    }
    
    
    /**
     * @depends testGetSignedUrl
     */
    public function testVerifySignedUrl(string $signedUrl) 
    {
        self::bootKernel();
        
        $container = static::getContainer();
        
        $helper = $container->get(MyVerifyEmailHelper::class);
        
        $user = $this->createMock(RegisterUser::class);
        $user
            ->method('getId')
            ->willReturn(1)
            ;
        $user
            ->method('getEmail')
            ->willReturn('john@doe.com')
            ;
        $helper->verifySignedUrl($user, $signedUrl);
        
        $user2 = $this->createMock(RegisterUser::class);
        $user2
            ->method('getId')
            ->willReturn(1)
            ;
        $user2
            ->method('getEmail')
            ->willReturn('wrong@email.com');
        $this->expectException(VerifyEmailExceptionInterface::class);
        $helper->verifySignedUrl($user2, $signedUrl);
    }
}