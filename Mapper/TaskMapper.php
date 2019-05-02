<?php
require_once 'Model/Task.php';

class TaskMapper
{
    /**
     * @param array $map
     * @return Task
     */
    public function fromArray(array $map): Task
    {
        return new Task(
            (int) $map['id'],
            $map['title'],
            (int) $map['userId'],
            (int) $map['status'],
            (int) $map['priority'],
            (int) $map['dueDate']
        );
    }

    /**
     * @param Task $task
     * @return array
     */
    public function toArray(Task $task): array
    {
        return [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'userId' => $task->getUserId(),
            'status' => $task->getStatus(),
            'priority' => $task->getPriority(),
            'dueDate' => $task->getDueDate(),
        ];
    }
}
