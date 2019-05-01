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
    /** @var int */
    private $priority;
    /** @var int */
    private $dueDate;

    /**
     * Task constructor.
     * @param $title
     * @param $userId
     * @param $status
     * @param $priority
     * @param $dueDate
     */
//    public function __construct(string $title, int $userId, int $status, int $priority, int $dueDate)
//    {
//        $this->title = $title;
//        $this->userId = $userId;
//        $this->status = $status;
//        $this->priority = $priority;
//        $this->dueDate = $dueDate;
//    }

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
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getPriority(): int
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