<?php declare(strict_types=1);


class Api {

    private const API_TOKEN_SALT = 'MyLittleSaltHere2019';

    public function init(): void
    {
        $this->router();
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

    private function router(): void
    {
        $route = $_SERVER['PATH_INFO'] ?? '';
        switch ($route) {
            case '/v1/login':
                $email = $_REQUEST['email'] ?? '';
                $password = $_REQUEST['password'] ?? '';
                $this->loginAction($email, $password);
                break;
            default:
                $this->unknownAction();
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
