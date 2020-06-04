<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UsersRepository::class)
 */
class Users {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int {
        return $this->id;
    }

    /**
     * @ORM\Column(type="string")
     */
    private $username;

    public function setUsername(string $username) {
        $this->username = $username;
    }

    public function getUsername() : string {
        return $this->username;
    }

    /**
     * @ORM\Column(type="string")
     */
    private $password;

    public function setPassword(string $password) {
        $this->password = $password;
    }

    public function getPassword() : string {
        return $this->password;
    }

    /**
     * @ORM\Column(type="string")
     */
    private $email;

    public function setemail(string $email) {
        $this->email = $email;
    }

    public function getEmail() : string {
        return $this->email;
    }

    /**
     * @ORM\Column(type="string")
     */
    private $code;
    
    public function setCode(string $code) {
        $this->code = $code;
    }

    public function getCode() : string {
        return $this->code;
    }

    /**
     * @ORM\Column(type="boolean")
     */
    private $activated;

    public function setActivated(bool $activated) {
        $this->activated = $activated;
    }

    public function getActivated() : boolean {
        return $this->activated; 
    }
}
