<?php declare(strict_types=1);

require_once 'Mapper/UserMapper.php';
require_once 'Exception/NotFoundException.php';
require_once 'Exception/DatabaseDuplicateException.php';

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
        $sth->execute([
            ':email' => $email,
            ':password' => $password,
        ]);
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

    /**
     * @param int $id
     * @return User
     */
    public function getById(int $id): User
    {
        $sth = $this->connection->prepare('SELECT * FROM `user` WHERE id = :id');
        $sth->execute([
            ':id' => $id,
        ]);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $this->mapper->fromArray($result);
    }

    /**
     * @param string $email
     * @param string $password
     * @return User
     * @throws Throwable
     */
    public function addUser(string $email, string $password): User
    {
        try {
            $sth = $this->connection->prepare(
                'INSERT INTO `user` (`email`, `password`)
            VALUES (:email, :password)'
            );
            $sth->execute(
                [
                    ':email' => $email,
                    ':password' => $password,
                ]
            );
        } catch (\Throwable $e) {
            if ($e->getCode() === '23000') {
                throw new DatabaseDuplicateException(
                    sprintf(
                        'User with email %s already exists',
                        $email
                    )
                );
            }
            throw $e;
        }
        return $this->getById(
            (int) $this->connection->lastInsertId()
        );
    }
}
