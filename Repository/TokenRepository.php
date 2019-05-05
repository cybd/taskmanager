<?php declare(strict_types=1);

require_once 'Mapper/TokenMapper.php';

class TokenRepository
{
    /** @var MySQLConnection */
    private $connection;
    /** @var TokenMapper */
    private $mapper;

    /**
     * TaskRepository constructor.
     * @param MySQLConnection $connection
     */
    public function __construct(MySQLConnection $connection)
    {
        $this->connection = $connection;
        $this->mapper = new TokenMapper();
    }

    /**
     * @param int $userId
     * @return Token
     */
    public function getByUserId(int $userId): Token
    {
        $now = time();
        $sth = $this->connection->prepare('SELECT * FROM `token` WHERE userId = :userId AND expiredAt < :current');
        $sth->execute([
            ':userId' => $userId,
            ':current' => $now,
        ]);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $this->mapper->fromArray($result);
    }

    /**
     * @param int $id
     * @return Token
     */
    public function getById(int $id): Token
    {
        $sth = $this->connection->prepare('SELECT * FROM `token` WHERE id = :id');
        $sth->execute([
            'id' => $id,
        ]);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $this->mapper->fromArray($result);
    }

    /**
     * @param Token $token
     * @return Token
     */
    public function addToken(Token $token): Token
    {
        $sth = $this->connection->prepare(
            'INSERT INTO `token` (`userId`, `token`, `expireAt`)
            VALUES (:userId, :token, :expireAt)'
        );
        $sth->execute(
            [
                ':userId' => $token->getUserId(),
                ':token' => $token->getToken(),
                ':expireAt' => $token->getExpireAt(),
            ]
        );
        return $this->getById(
            (int) $this->connection->lastInsertId()
        );
    }

    /**
     * @param string $token
     * @return Token
     * @throws NotFoundException
     */
    public function getByToken(string $token): Token
    {
        $sth = $this->connection->prepare('SELECT * FROM `token` WHERE token = :token');
        $sth->execute([
            ':token' => $token,
        ]);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        if ($sth->rowCount() === 0) {
            throw new NotFoundException(
                sprintf(
                    'Token %s',
                    $token
                )
            );
        }
        if ($sth->rowCount() > 1) {
            throw new \LogicException(
                sprintf(
                    'Returned more than one row, Token %s',
                    $token
                )
            );
        }
        return $this->mapper->fromArray($result[0]);
    }
}
