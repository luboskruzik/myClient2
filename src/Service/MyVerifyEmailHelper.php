<?php

namespace App\Service;

use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Entity\RegisterUser;

class MyVerifyEmailHelper 
{
    private $helper;
    
    
    public function __construct(VerifyEmailHelperInterface $helper) 
    {
        $this->helper = $helper;
    }
    
    public function getSignedUrl(RegisterUser $user): string
    {
        $signatureComponents = $this->helper->generateSignature(
            'verify',
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );
        return $signatureComponents->getSignedUrl();
    }
    
    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function verifySignedUrl(RegisterUser $user, string $uri): void
    {
        $this->helper->validateEmailConfirmation(
            $uri,
            $user->getId(),
            $user->getEmail(),
        );
        
    }
}
