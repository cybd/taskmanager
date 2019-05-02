<?php declare(strict_types=1);

require_once 'Mapper/TaskMapper.php';

class TaskRepository
{
    /** @var MySQLConnection */
    private $connection;
    /** @var TaskMapper */
    private $mapper;

    /**
     * TaskRepository constructor.
     * @param MySQLConnection $connection
     */
    public function __construct(MySQLConnection $connection)
    {
        $this->connection = $connection;
        $this->mapper = new TaskMapper();
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
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($row) {
            return $this->mapper->fromArray($row);
        }, $result);
    }
}
