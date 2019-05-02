<?php declare(strict_types=1);

class User
{
    /** @var int */
    private $id;
    /** @var string */
    private $email;
    /** @var string */
    private $password;

    /**
     * User constructor.
     * @param int $id
     * @param string $email
     * @param string $password
     */
    public function __construct(int $id, string $email, string $password)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
