<?php


namespace OSA\Webserver;


use OSA\Plugins\PluginSystem;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Webserver
{
    private WebserverConfig $config;
    private Server $server;
    private static Webserver $self;
    public function __construct(WebserverConfig $config)
    {
        self::$self = $this;
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

    /**
     * @return Webserver
     */
    public static function getInstance(): Webserver
    {
        return self::$self;
    }

    public function start() {
        $this->server->on('request', [$this, 'onRequest']);  //used for normal http requests
        $this->server->on('message', [$this, 'onMessage']);  //used for websocket messages
        $this->server->on('start', [$this, 'onStart']);

        $this->server->start();
    }

    public function onStart() {
        PluginSystem::init();
        array_map([$this, 'startCoroutine'], $this->startCoroutines);


    }
    public function onMessage(Server $srv, Frame $frame) {
        //@TODO: handle websocket messages
    }


    private function isValidRoute(string $req_uri, string $r) : bool {
        if($req_uri === $r) return true;
        $regex = sprintf('#^%s$#', str_replace('*','(.*)', $r));
        $result = preg_match($regex, $req_uri);
        return $result !== false && $result > 0;
    }

    private array $routes = [];
    public function onRequest(Request $req, Response $resp) {
        $resp->header('Server', 'OpenStreamerAssistent');

        $sessionid = $req->cookie['otasession'] ?? null;
        if($sessionid == null) {
            $sessionid = session_create_id();
            $resp->cookie('otasession', $sessionid);
            $req->cookie['otasession'] = $sessionid;
        }

        $_GET = $req->get;
        $_POST = $req->post;
        $_BODY = $req->rawContent();


        $req_uri = $req->server['request_uri'];
        $exploded = explode('/', $req_uri);
        $req_uri = implode('/', array_filter($exploded, fn($r) => !empty($r)));

        $req->server['request_uri'] = $req_uri;
        $openRoutes = array_filter(array_keys($this->routes), fn($r) => $this->isValidRoute($req_uri, $r));

        if(count($openRoutes) > 0) {
            usort($openRoutes, fn($k1,$k2) => strlen($k2) <=> strlen($k1));
            call_user_func($this->routes[$openRoutes[0]], $req, $resp);
            return;
        }


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

    public function addRoute(string $route, \Closure $fromCallable, bool $overwrite = false)
    {
        if(isset($this->routes[$route]) && !$overwrite) {
            throw new \Exception('Route already exists');
        }
        $this->routes[$route] = $fromCallable;
    }
    public function removeRoute(string $route, \Closure $fromCallable)
    {
        unset($this->routes[$route]);
    }

    /**
     * @return WebserverConfig
     */
    public function getConfig(): WebserverConfig
    {
        return $this->config;
    }
}