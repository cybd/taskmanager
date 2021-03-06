<?php declare(strict_types=1);

require_once 'Exception/NotFoundException.php';
require_once 'Exception/UnauthorizedException.php';
require_once 'MySQLConnection.php';
require_once 'Repository/TaskRepository.php';
require_once 'Repository/TokenRepository.php';
require_once 'Repository/UserRepository.php';
require_once 'Mapper/TaskMapper.php';
require_once 'Enum/TaskPriority.php';
require_once 'Enum/TaskStatus.php';

class Api {

    private const API_TOKEN_SALT = 'MyLittleSaltHere2019';
    private const API_TOKEN_LIFETIME = 60 * 15; // 15 min
    private const API_USER_PASSWORD_SALT = 'UserPasswordHash2019';

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
     * @throws UnauthorizedException
     * @throws Throwable
     */
    private function router(): void
    {
        $parsedUrl = parse_url($_SERVER['REQUEST_URI']);
        $route = $parsedUrl['path'] ?? '';
        $queryParams = [];
        parse_str($parsedUrl['query'] ?? '', $queryParams);
        $token = $_SERVER['HTTP_TOKEN'] ?? '';
        $email = $_REQUEST['email'] ?? '';
        $postBodyRaw = file_get_contents('php://input');
        $postBody = json_decode($postBodyRaw, true);
        $password = $_REQUEST['password'] ?? '';
        switch ($route) {
            case '/v1/login':
                $this->loginAction($email, $password);
                break;
            case '/v1/register':
                $this->registerAction($email, $password);
                break;
            case '/v1/myTasks':
                $tokenData = $this->validateToken($token);
                $this->getMyTasksAction($tokenData->getUserId(), $queryParams);
                break;
            case '/v1/createTask':
                $tokenData = $this->validateToken($token);
                $this->createTaskAction($tokenData->getUserId(), $postBody);
                break;
            case '/v1/markAsDone':
                $tokenData = $this->validateToken($token);
                $this->markAsDoneAction($tokenData->getUserId(), $postBody);
                break;
            case '/v1/deleteTask':
                $tokenData = $this->validateToken($token);
                $this->deleteTaskAction($tokenData->getUserId(), $postBody);
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
            $user = $userRepository->getUserByEmailAndPassword(
                $email,
                $this->getUserPasswordHash($password)
            );
            $tokenRepository = new TokenRepository($this->getConnection());
            $expireAt = time() + self::API_TOKEN_LIFETIME;
            $token = $tokenRepository->addToken(
                new Token(
                    0,
                    $user->getId(),
                    $this->generateTokenByUser($user),
                    $expireAt
                )
            );
            $data = [
                'token' => $token->getToken(),
                'expireAt' => $token->getExpireAt(),
            ];
            $this->formatResponse(['data' => $data]);
        } catch (NotFoundException $e) {
            $this->internalServerErrorResponse('Not found. ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->internalServerErrorResponse($e->getMessage());
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @throws Throwable
     */
    public function registerAction(string $email, string $password): void
    {
        $userRepository = new UserRepository($this->getConnection());
        $user = $userRepository->addUser(
            new User(
                0,
                $email,
                $this->getUserPasswordHash($password)
            )
        );
        $tokenRepository = new TokenRepository($this->getConnection());
        $expireAt = time() + self::API_TOKEN_LIFETIME;
        $token = $tokenRepository->addToken(
            new Token(
                0,
                $user->getId(),
                $this->generateTokenByUser($user),
                $expireAt
            )
        );
        $data = [
            'token' => $token->getToken(),
            'expireAt' => $token->getExpireAt(),
        ];
        $this->formatResponse(['data' => $data]);
    }

    /**
     * @param int $userId
     * @param array $params
     */
    public function getMyTasksAction(int $userId, array $params): void
    {
        $repository = new TaskRepository($this->getConnection());
        $taskCollection = $repository->getListByUserId(
            $userId,
            $params['sort'] ?? 'id',
            array_key_exists('sortDesc', $params),
            (int) ($params['page'] ?? 1),
            (int) ($params['perPage'] ?? 10)
        );
        $result = [];
        if (\count($taskCollection) > 0) {
            /** @var Task $task */
            $mapper = new TaskMapper();
            foreach ($taskCollection as $task) {
                $result[] = $mapper->toArray($task);
            }
        }
        $this->formatResponse(['data' => $result]);
    }

    /**
     * @param int $userId
     * @param array $postBody
     * @throws ReflectionException
     * @throws NotFoundException
     */
    public function createTaskAction(int $userId, array $postBody): void
    {
        $title = $postBody['title'] ?? '';
        $priority = new TaskPriority($postBody['priority']);
        $status = new TaskStatus($postBody['status']);
        $dueDate = (int)($postBody['dueDate'] ?? 0);
        $taskRepository = new TaskRepository($this->getConnection());
        $task = $taskRepository->addTask(
            new Task(
                0,
                $title,
                $userId,
                $status,
                $priority,
                $dueDate
            )
        );
        $mapper = new TaskMapper();
        $this->formatResponse(['data' => $mapper->toArray($task)], 201);
    }

    /**
     * @param int $userId
     * @param array $queryData
     * @throws UnauthorizedException
     * @throws ReflectionException
     * @throws NotFoundException
     */
    public function markAsDoneAction(int $userId, array $queryData): void
    {
        $id = (int)($queryData['id'] ?? 0);
        $taskRepository = new TaskRepository($this->getConnection());
        $task = $taskRepository->getById($id);
        $this->canModifyTask($userId, $task);
        $task = $taskRepository->updateTask(
            new Task(
                $task->getId(),
                $task->getTitle(),
                $task->getUserId(),
                new TaskStatus(TaskStatus::DONE),
                $task->getPriority(),
                $task->getDueDate()
            )
        );
        $mapper = new TaskMapper();
        $this->formatResponse(['data' => $mapper->toArray($task)]);
    }

    /**
     * @param int $userId
     * @param array $queryData
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws UnauthorizedException
     */
    public function deleteTaskAction(int $userId, array $queryData): void
    {
        $id = (int)($queryData['id'] ?? 0);
        $taskRepository = new TaskRepository($this->getConnection());
        $task = $taskRepository->getById($id);
        $this->canModifyTask($userId, $task);
        $taskRepository->deleteTask($task);
        $this->formatResponse([
        ],
            204
        );
    }

    /**
     * @param string $token
     * @return Token
     * @throws UnauthorizedException
     */
    private function validateToken(string $token): Token
    {
        if ($token === '') {
            throw new UnauthorizedException('Token is empty');
        }
        try {
            $tokenRepository = new TokenRepository($this->getConnection());
            $tokenData = $tokenRepository->getByToken($token);
            if (time() > $tokenData->getExpireAt()) {
                throw new UnauthorizedException('Token has been expired');
            }
        } catch (NotFoundException $e) {
            throw new UnauthorizedException('Token was not found');
        }
        return $tokenData;
    }

    /**
     * @param int $userId
     * @param Task $task
     * @return void
     * @throws UnauthorizedException
     */
    private function canModifyTask(int $userId, Task $task): void
    {
        if ($userId !== $task->getUserId()) {
            throw new UnauthorizedException(
                sprintf('User (id=%s) cannot modify task (id=%s)',
                    $userId,
                    $task->getId()
                )
            );
        }
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

    /**
     * @param string $value
     * @return string
     */
    private function getUserPasswordHash(string $value): string
    {
        return sha1($value . self::API_USER_PASSWORD_SALT);
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
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]
            );
        }
        return $this->connection;
    }
}
