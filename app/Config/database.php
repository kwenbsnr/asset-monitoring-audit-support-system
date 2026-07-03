<?php
namespace App\Config;

if (!defined('APP_START')) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

class Database {
    /** @var Database|null */
    private static $instance = null;
    /** @var \mysqli */
    private $connection;

    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'nia_schema_v1';

    private function __construct() {
        $this->connection = new \mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->dbname
        );
        if ($this->connection->connect_error) {
            die('Database connection failed: ' . $this->connection->connect_error);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}