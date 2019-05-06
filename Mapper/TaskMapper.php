<?php declare(strict_types=1);

require_once 'Model/Task.php';

class TaskMapper
{
    /**
     * @param array $map
     * @return Task
     * @throws ReflectionException
     */
    public function fromArray(array $map): Task
    {
        return new Task(
            (int) $map['id'],
            $map['title'],
            (int) $map['userId'],
            new TaskStatus((int) $map['status']),
            new TaskPriority((int) $map['priority']),
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
            'status' => $task->getStatus()->getTextValue(),
            'priority' => $task->getPriority()->getTextValue(),
            'dueDate' => $task->getDueDate(),
        ];
    }
}
