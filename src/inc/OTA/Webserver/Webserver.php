<?php


namespace OTA\Webserver;


use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Webserver
{
    private WebserverConfig $config;
    private Server $server;

    public function __construct(WebserverConfig $config)
    {
        $this->config = $config;

        $this->server = new Server($config->getIpv6(), $config->getPort(), SWOOLE_BASE, SWOOLE_SOCK_TCP6);
        $this->server->addListener($config->getIpv4(), $config->getPort(), SWOOLE_SOCK_TCP);
        if($config->getCertFile() != null) {
            $this->server->addListener($config->getIpv6(), $config->getPortSSL(), SWOOLE_SOCK_TCP6 | SWOOLE_SSL);
            $this->server->addListener($config->getIpv4(), $config->getPortSSL(), SWOOLE_SOCK_TCP | SWOOLE_SSL);
            $this->server->set([
                'ssl_cert_file' => $config->getCertFile(),
                'ssl_key_file'  => $config->getCertKey()
            ]);
        }
    }

    public function start() {
        $this->server->on('request', [$this, 'onRequest']);  //used for normal http requests
        $this->server->on('message', [$this, 'onMessage']);  //used for websocket messages
        $this->server->on('start', [$this, 'onStart']);

        $this->server->start();
    }

    public function onStart() {

        array_map([$this, 'startCoroutine'], $this->startCoroutines);
    }
    public function onMessage(Server $srv, Frame $frame) {
        //@TODO: handle websocket messages
    }
    public function onRequest(Request $req, Response $resp) {



        //request cant be handled so throw 404
        $resp->status(404);
        $resp->end();
    }




    private array $startCoroutines = [];
    public function addStartCoroutine(callable $call) {
        $this->startCoroutines[] = $call;
    }
    public function startCoroutine(callable $call) {
        go($call);
    }
}