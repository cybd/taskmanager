<?php declare(strict_types=1);


class Api {

    private const API_TOKEN_SALT = 'MyLittleSaltHere2019';

    public function init(): void
    {
        $this->router();
    }

    private function router(): void
    {
        $route = $_SERVER['PATH_INFO'] ?? '';
        $token = $_REQUEST['token'] ?? '';
        switch ($route) {
            case '/v1/login':
                $email = $_REQUEST['email'] ?? '';
                $password = $_REQUEST['password'] ?? '';
                $this->loginAction($email, $password);
                break;
            case '/v1/register':
                $email = $_REQUEST['email'] ?? '';
                $password = $_REQUEST['password'] ?? '';
                $this->registerAction($email, $password);
                break;
            case '/v1/myTasks':
                $this->validateToken($token);
                $this->getMyTasksAction();
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
        $token = $this->getToken($email, $password);
        $this->formatResponse(['data' => $token]);
    }

    public function registerAction(string $email, string $password): void
    {
        // TODO: implement
        $this->formatResponse(['data' => 'register action']);
    }

    public function getMyTasksAction(): void
    {
        // TODO: implement
        $this->formatResponse(['data' => 'get my tasks action']);
    }

    public function createTaskAction(): void
    {
        // TODO: implement
        $this->formatResponse(['data' => 'create task action']);
    }

    public function markAsDoneAction(): void
    {
        // TODO: implement
        $this->formatResponse(['data' => 'mark as done action']);
    }

    public function deleteTaskAction(): void
    {
        // TODO: implement
        $this->formatResponse(['data' => 'delete task action']);
    }

    /**
     * @param string $token
     * @return bool
     */
    private function validateToken(string $token): bool
    {
        // TODO: implement
        return true;
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
}
