<?php declare(strict_types=1);


class MySQLConnection extends \PDO
{
    /**
     * MySQLConnection constructor.
     * @param string $host
     * @param string $dbName
     * @param string $user
     * @param string $password
     * @param array $options
     */
    public function __construct(string $host, string $dbName, string $user, string $password, array $options)
    {
        $dsn = "mysql:host=$host;dbname=$dbName";
        parent::__construct($dsn, $user, $password, $options);
    }
}
