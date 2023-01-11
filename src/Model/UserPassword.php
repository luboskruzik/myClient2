<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class UserPassword
{

    /**
     * @Assert\Regex(
     *     pattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,10}$/",
     *     match = true,
     *     message = "Your password must have between 6 to 10 characters, contain at least one letter, one number and one special character"
     *     )
     */
    private $password;

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }
}