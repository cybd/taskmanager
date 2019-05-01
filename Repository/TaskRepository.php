<?php declare(strict_types=1);

class TaskRepository
{
    /**
     * @var MySQLConnection
     */
    private $connection;
    /** @var array */
    private $ctorArgs = [
        '', 0, 0, 0, 0
    ];

    /**
     * TaskRepository constructor.
     * @param MySQLConnection $connection
     */
    public function __construct(MySQLConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getListByUserId(int $userId): array
    {
        $sth = $this->connection->prepare('SELECT * FROM task WHERE userId = :userId');
        $sth->bindParam(':userId', $userId, PDO::PARAM_INT);
        $sth->execute();
        $sth->setFetchMode(PDO::FETCH_CLASS, 'Task', $this->ctorArgs);
        return $sth->fetchAll();
    }
}
