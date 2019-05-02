<?php declare(strict_types=1);

require_once 'Mapper/UserMapper.php';
require_once 'Exception/NotFoundException.php';

class UserRepository
{
    /** @var MySQLConnection */
    private $connection;
    /** @var UserMapper */
    private $mapper;

    /**
     * TaskRepository constructor.
     * @param MySQLConnection $connection
     */
    public function __construct(MySQLConnection $connection)
    {
        $this->connection = $connection;
        $this->mapper = new UserMapper();
    }

    /**
     * @param string $email
     * @param string $password
     * @return User
     * @throws NotFoundException
     */
    public function getUserByEmailAndPassword(string $email, string $password): User
    {
        $sth = $this->connection->prepare('SELECT * FROM `user` WHERE email = :email AND password = :password');
        $sth->bindParam(':email', $email, PDO::PARAM_STR);
        $sth->bindParam(':password', $password, PDO::PARAM_STR);
        $sth->execute();
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        if ($sth->rowCount() === 0) {
            throw new NotFoundException(
                sprintf(
                    'User by email %s',
                    $email
                )
            );
        }
        if ($sth->rowCount() > 1) {
            throw new \LogicException(
                sprintf(
                    'Returned more than one row, User email %s',
                    $email
                )
            );
        }
        return $this->mapper->fromArray($result[0]);
    }
}
