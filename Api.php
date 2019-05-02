<?php declare(strict_types=1);

require_once 'Exception/NotFoundException.php';
require_once 'Exception/UnauthorizedException.php';
require_once 'MySQLConnection.php';
require_once 'Repository/TaskRepository.php';
require_once 'Repository/TokenRepository.php';
require_once 'Repository/UserRepository.php';

class Api {

    private const API_TOKEN_SALT = 'MyLittleSaltHere2019';

    private $tokenArray = [
        'd98bc7701f03aca772b2f00921daa42e8904d87a' => [
            'userId' => 1,
            'expireAt' => 1556369432, // expired
        ],
        'ba66df5d449a1318a16baef6f10be41d0a7c5d3b' => [
            'userId' => 1,
            'expireAt' => 1656369432, // valid
        ],
        '1dcc07cbffafba6122153ddee83855b6120b3ece' => [
            'userId' => 99,
            'expireAt' => 1756369432, // valid
        ],
    ];

    /** @var MySQLConnection */
    private $connection;

    public function init(): void
    {
        try {
            $this->router();
        } catch (\Throwable $e) {
            $this->internalServerErrorResponse($e->getMessage());
        }
    }

    /**
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    private function router(): void
    {
        $parsedUrl = parse_url($_SERVER['REQUEST_URI']);
        $route = $parsedUrl['path'] ?? '';
        $query = $parsedUrl['query'] ?? '';
        $token = $_SERVER['HTTP_TOKEN'] ?? '';
        $email = $_REQUEST['email'] ?? '';
        $password = $_REQUEST['password'] ?? '';
        switch ($route) {
            case '/v1/login':
                $this->loginAction($email, $password);
                break;
            case '/v1/register':
                $this->registerAction($email, $password);
                break;
            case '/v1/myTasks':
                $this->validateToken($token);
                $userId = $this->getUserIdByToken($token);
                $this->getMyTasksAction($userId);
                break;
            case '/v1/createTask':
                $this->validateToken($token);
                $this->createTaskAction();
                break;
            case '/v1/markAsDone':
                $this->validateToken($token);
                $this->markAsDoneAction();
                break;
            case '/v1/deleteTask':
                $this->validateToken($token);
                $this->deleteTaskAction();
                break;
            default:
                $this->unknownAction();
        }
    }

    /**
     * @param string $email
     * @param string $password
     */
    public function loginAction(string $email, string $password): void
    {
        try {
            $userRepository = new UserRepository($this->getConnection());
            $user = $userRepository->getUserByEmailAndPassword($email, $password);
            $token = $this->generateTokenByUser($user);
            $this->formatResponse(['data' => $token]);
        } catch (NotFoundException $e) {
            $this->internalServerErrorResponse('Not found. ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->internalServerErrorResponse($e->getMessage());
        }
    }

    public function registerAction(string $email, string $password): void
    {
        // TODO: implement
        $this->formatResponse(['data' => 'register action']);
    }

    public function getMyTasksAction(int $userId): void
    {
        $repository = new TaskRepository($this->getConnection());
        $taskCollection = $repository->getListByUserId($userId);
        $result = [];
        if (\count($taskCollection) > 0) {
            /** @var Task $task */
            foreach ($taskCollection as $task) {
                $result[] = [
                    'id' => $task->getId(),
                    'title' => $task->getTitle(),
                    'status' => $task->getStatus(),
                    'priority' => $task->getPriority(),
                    'dueDate' => $task->getDueDate(),
                ];
            }
        }
        $this->formatResponse(['data' => $result]);
    }

    public function createTaskAction(): void
    {
        // TODO: implement
        $this->formatResponse(['data' => 'create task.sql action']);
    }

    public function markAsDoneAction(): void
    {
        // TODO: implement
        $this->formatResponse(['data' => 'mark as done action']);
    }

    public function deleteTaskAction(): void
    {
        // TODO: implement
        $this->formatResponse(['data' => 'delete task.sql action']);
    }

    /**
     * @param string $token
     * @return void
     * @throws UnauthorizedException
     */
    private function validateToken(string $token): void
    {
        if ($token === '') {
            throw new UnauthorizedException('Token is empty');
        }
        $tokenData = $this->tokenArray[$token] ?? [];
        if (\count($tokenData) === 0) {
            throw new UnauthorizedException('Token was not found');
        }
        $expireAt = $tokenData['expireAt'] ?? 0;
        if (time() > $expireAt) {
            throw new UnauthorizedException('Token has been expired');
        }
    }

    /**
     * @param string $token
     * @return int
     * @throws NotFoundException
     */
    private function getUserIdByToken(string $token): int
    {
        $tokenData = $this->tokenArray[$token] ?? '';
        if ($tokenData !== '') {
            return $tokenData['userId'];
        }
        throw new NotFoundException(
            sprintf(
                'User not found by token %s',
                $token
            )
        );
    }

    /**
     * @param array $data
     * @param int $code
     * @return void
     */
    private function formatResponse(array $data, int $code = 200): void
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data);
    }

    /**
     * @param User $user
     * @return string
     */
    private function generateTokenByUser(User $user): string
    {
        return sha1($user->getEmail() . $user->getId() . self::API_TOKEN_SALT . microtime());
    }

    public function unknownAction(): void
    {
        $this->formatResponse([
            'error' => 'Unknown action'
        ],
            404
        );
    }

    /**
     * @param string $message
     */
    public function internalServerErrorResponse(string $message = ''): void
    {
        $data = [
            'error' => 'Internal Server Error'
        ];
        if ($message !== '') {
            $data['message'] = $message;
        }
        $this->formatResponse(
            $data,
            500
        );
    }

    /**
     * @return MySQLConnection
     */
    private function getConnection(): MySQLConnection
    {
        if ($this->connection === null) {
            $this->connection = new MySQLConnection(
                'localhost',
                'taskmanager',
                'taskmanager_u',
                'N11mKZaBs9wL7u6Y',
                [
                    \PDO::ATTR_PERSISTENT => true,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]
            );
        }
        return $this->connection;
    }
}
