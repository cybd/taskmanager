<?php declare(strict_types=1);

class Token
{
    /** @var int */
    private $id;
    /** @var int */
    private $userId;
    /** @var string */
    private $token;
    /** @var int */
    private $expireAt;

    /**
     * @param int $id
     * @param int $userId
     * @param string $token
     * @param int $expireAt
     */
    public function __construct(int $id, int $userId, string $token, int $expireAt)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->token = $token;
        $this->expireAt = $expireAt;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getExpireAt(): int
    {
        return $this->expireAt;
    }
}
