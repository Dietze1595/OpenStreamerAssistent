<?php


namespace OSA\Database;


use OSA\JSONConfig;

class MySQLConfig
{
    private JSONConfig $config;
    public function __construct(string $path) {
        $this->config = JSONConfig::get($path);
    }
    public function getDSN() : string {
        $socket = $this->config->unixsocket ?? null;
        $dbname = $this->config->dbname ?? null;
        $host = $this->config->host ?? null;
        $port = $this->config->port ?? 3306;
        if($host===null&&$socket === null) {
            throw new \Exception('No host/unixsocket given');
        }
        if($dbname === null)
            throw new \Exception('No databasename given');

        if($socket) {
            return sprintf('mysql:unix_socket=%s;dbname=%s', $socket, $dbname);
        }
        return sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $dbname);
    }

    public function getUsername() : string {
        return $this->config->user;
    }

    public function getPassword() : string {
        return $this->config->pass;
    }

}