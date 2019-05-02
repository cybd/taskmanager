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
        $sth->bindParam(':userId', $userId, PDO::PARAM_INT);
        $sth->bindParam(':current', $now, PDO::PARAM_INT);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $this->mapper->fromArray($result);
    }
}
