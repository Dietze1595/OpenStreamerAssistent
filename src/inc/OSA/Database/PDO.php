<?php


namespace OSA\Database;


use Swoole\Coroutine\Channel;

class PDO
{
    private static ?PDO $self = null;
    private MySQLConfig $config;
    private Channel $pool;
    public static function getInstance() : PDO {

        if(PDO::$self === null) {
            PDO::$self = new PDO();
        }
        return PDO::$self;
    }

    public function tableExists(string $tablename) : bool {
        $db = $this->get();
        try {
            $rc = $db->query(sprintf('SELECT 1 FROM `%s` LIMIT 1', str_replace('`','``', $tablename)))->rowCount();
            return true;
        } catch(\Exception $ex) {
            return false;
        } finally {
            $this->put($db);
        }
    }

    public function __construct() {
        $this->config = new MySQLConfig(ROOT.'/config/MySQL.json');
        $this->pool = new Channel(10);
        var_dump($this->config->getDSN());

        try {
            for ($i = 0; $i < $this->pool->capacity; $i++) {
                $pdo = new \PDO($this->config->getDSN(), $this->config->getUsername(), $this->config->getPassword());
                $this->pool->push($pdo);
            }
        } catch (\Exception $ex) {
            throw new \Exception('MySQL could not established: '.$ex);
        }
    }

    public function get(): \PDO {
        return $this->pool->pop();
    }
    public function put(\PDO $pdo) {
        $this->pool->push($pdo);
    }


}