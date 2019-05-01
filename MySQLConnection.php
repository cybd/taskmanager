<?php declare(strict_types=1);


class MySQLConnection
{
    /**
     * MySQLConnection constructor.
     * @param string $dsn
     * @param string $user
     * @param string $password
     * @param array $options
     * @return PDO
     */
    public static function getConnection(string $dsn, string $user, string $password, array $options)
    {
        try {
            return new PDO($dsn, $user, $password, $options);
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }
}