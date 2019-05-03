<?php declare(strict_types=1);

require_once 'Exception/NotFoundException.php';
require_once 'Exception/UnauthorizedException.php';
require_once 'MySQLConnection.php';
require_once 'Repository/TaskRepository.php';
require_once 'Repository/TokenRepository.php';
require_once 'Repository/UserRepository.php';

class Api {

    private const API_TOKEN_SALT = 'MyLittleSaltHere2019';
    private const API_TOKEN_LIFETIME = 60 * 15; // 15 min

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
                $tokenData = $this->validateToken($token);
                $this->getMyTasksAction($tokenData->getUserId());
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
     */
    public function registerAction(string $email, string $password): void
    {
        $userRepository = new UserRepository($this->getConnection());
        $user = $userRepository->addUser($email, $password);
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
