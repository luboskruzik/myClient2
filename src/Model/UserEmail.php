<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class UserEmail
{
    /**
     * @Assert\Email (message = "The email '{{ value }}' is not a valid email.")
     */
    private string $email;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}