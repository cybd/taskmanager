<?php declare(strict_types=1);

require_once 'Exceptions/NotFoundException.php';
require_once 'Exceptions/UnauthorizedException.php';

class Api {

    private const API_TOKEN_SALT = 'MyLittleSaltHere2019';

    private $tokenArray = [
        'd98bc7701f03aca772b2f00921daa42e8904d87a' => [
            'userId' => 11,
            'expireAt' => 1556369432, // expired
        ],
        'ba66df5d449a1318a16baef6f10be41d0a7c5d3b' => [
            'userId' => 11,
            'expireAt' => 1656369432, // valid
        ],
        '1dcc07cbffafba6122153ddee83855b6120b3ece' => [
            'userId' => 99,
            'expireAt' => 1756369432, // valid
        ],
    ];

    private $taskArray = [
        '11' => [
            [
                'id' => 10,
                'title' => 'Finish Trial task.sql',
                'status' => 'active',
                'priority' => 'high',
                'dueDate' => 1556189059,
            ],
            [
                'id' => 23,
                'title' => 'Fix my watch',
                'status' => 'active',
                'priority' => 'low',
                'dueDate' => 1556199001,
            ],
            [
                'id' => 9,
                'title' => 'Find a new Job',
                'status' => 'done',
                'priority' => 'high',
                'dueDate' => 1553145057,
            ]
        ]
    ];

    public function init(): void
    {
        $this->router();
    }

    private function router(): void
    {
        $parsedUrl = parse_url($_SERVER['REQUEST_URI']);
        $route = $parsedUrl['path'] ?? '';
        $query = $parsedUrl['query'] ?? '';
        $token = $_SERVER['HTTP_TOKEN'] ?? '';
        $email = $_REQUEST['email'] ?? '';
        $password = $_REQUEST['password'] ?? '';
        try {
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
        } catch (\Exception $e) {
            $this->internalServerErrorResponse($e->getMessage());
        }
    }

    /**
     * @param string $email
     * @param string $password
     */
    public function loginAction(string $email, string $password): void
    {
//        try {
//            $userId = $this->getUserId($email, $password);
//            $token = $this->getTokenByUserId($userId);
//            $this->formatResponse(['data' => $token]);
//        } catch (\Exception $e) {
//            $this->internalServerErrorResponse($e->getMessage());
//        }
        $token = $this->getToken($email, $password);
        $this->formatResponse(['data' => $token]);
    }

    public function registerAction(string $email, string $password): void
    {
        // TODO: implement
        $this->formatResponse(['data' => 'register action']);
    }

    public function getMyTasksAction(int $userId): void
    {
        $userTaskArray = $this->taskArray[$userId] ?? [];
        // TODO: implement
        $this->formatResponse(['data' => $userTaskArray]);
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
     * @param string $email
     * @param string $password
     * @return string
     */
    private function getToken(string $email, string $password): string
    {
        return sha1($email . $password . self::API_TOKEN_SALT . microtime());
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

    private function getUserId($email, $password): int
    {
        $data = [
            '' => [
                'password' => '',

            ]
        ];
    }
}
