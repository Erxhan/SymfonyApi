<?php

namespace Dev\ApiBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Login
{
    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="255"
     * )
     */
    private $username;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="255"
     * )
     */
    private $password;

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }



}

