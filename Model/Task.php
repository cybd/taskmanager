<?php declare(strict_types=1);

class Task {
    /** @var int */
    private $id;
    /** @var string string */
    private $title;
    /** @var int */
    private $userId;
    /** @var int */
    private $status;
    /** @var TaskPriority */
    private $priority;
    /** @var int */
    private $dueDate;

    /**
     * Task constructor.
     * @param int $id
     * @param string $title
     * @param int $userId
     * @param TaskStatus $status
     * @param TaskPriority $priority
     * @param int $dueDate
     */
    public function __construct(
        int $id,
        string $title,
        int $userId,
        TaskStatus $status,
        TaskPriority $priority,
        int $dueDate
    ) {
        $this->title = $title;
        $this->userId = $userId;
        $this->status = $status;
        $this->priority = $priority;
        $this->dueDate = $dueDate;
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return TaskStatus
     */
    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    /**
     * @return TaskPriority
     */
    public function getPriority(): TaskPriority
    {
        return $this->priority;
    }

    /**
     * @return int
     */
    public function getDueDate(): int
    {
        return $this->dueDate;
    }
}
