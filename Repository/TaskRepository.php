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
     * @param string $sort
     * @param bool $sortDesc
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getListByUserId(
        int $userId,
        string $sort,
        bool $sortDesc = false,
        int $page = 1,
        int $perPage = 10
    ): array {
        $page = $page < 1 ? 1 : $page;
        $sort = in_array($sort, ['title', 'dueDate', 'priority']) ? $sort : 'id';
        $sortQuery = sprintf(' ORDER BY `%s` %s', $sort, $sortDesc ? 'DESC' : '');
        $limitQuery = sprintf(' LIMIT %s, %s', ($page - 1) * $perPage, $perPage);
        $sth = $this->connection->prepare('SELECT * FROM task WHERE userId = :userId' . $sortQuery . $limitQuery);
        $sth->execute([
            ':userId' => $userId,
        ]);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($row) {
            return $this->mapper->fromArray($row);
        }, $result);
    }

    /**
     * @param int $id
     * @return Task
     */
    public function getById(int $id): Task
    {
        $sth = $this->connection->prepare('SELECT * FROM `task` WHERE id = :id');
        $sth->execute([
            ':id' => $id,
            ]
        );
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $this->mapper->fromArray($result);
    }

    /**
     * @param Task $task
     * @return Task
     */
    public function addTask(Task $task): Task
    {
        $sth = $this->connection->prepare(
            'INSERT INTO `task` (`title`, `userId`, `status`, `priority`, `dueDate`)
            VALUES (:title, :userId, :status, :priority, :dueDate)'
        );
        $sth->execute(
            [
                ':title' => $task->getTitle(),
                ':userId' => $task->getUserId(),
                ':status' => $task->getStatus(),
                ':priority' => $task->getPriority(),
                ':dueDate' => $task->getDueDate(),
            ]
        );
        return $this->getById(
            (int) $this->connection->lastInsertId()
        );
    }

    /**
     * @param Task $task
     * @return Task
     */
    public function updateTask(Task $task): Task
    {
        $sth = $this->connection->prepare(
            'UPDATE `task` SET 
          `title` = :title,
          `userId` = :userId,
          `status` = :status,
          `priority` = :priority,
          `dueDate` = :dueDate
          WHERE `id` = :id'
        );
        $sth->execute(
            [
                ':title' => $task->getTitle(),
                ':userId' => $task->getUserId(),
                ':status' => $task->getStatus(),
                ':priority' => $task->getPriority(),
                ':dueDate' => $task->getDueDate(),
                ':id' => $task->getId(),
            ]
        );
        return $this->getById($task->getId());
    }

    /**
     * @param Task $task
     * @return bool
     */
    public function deleteTask(Task $task): bool
    {
        $sth = $this->connection->prepare(
            'DELETE FROM `task` WHERE `id` = :id'
        );
        $sth->execute(
            [
                ':id' => $task->getId(),
            ]
        );
        return ($sth->rowCount() === 1);
    }
}
