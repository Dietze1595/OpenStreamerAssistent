<?php


namespace OTA\Swoole;

use co;
use Exception;
use OTA\EventEmitter;
use Swoole\Coroutine\Http\Client;

class WebsocketClient
{
    private ?Client $client;
    private string $host;
    private int $port;
    private string $path;
    private bool $ssl;
    private bool $connected = FALSE;
    private EventEmitter $events;


    public function __construct(string $url)
    {
        $host = parse_url($url);
        if (!$host) {
            throw new Exception('Malformed URL');
        }
        $this->host = $host['host'];
        $this->path = $host['path'] ?? '/';
        $this->ssl = in_array(strtolower($host['scheme']), ['wss', 'https']);
        $this->port = $host['port'] ?? $this->ssl ? 443 : 80;
        $this->client = NULL;

        $this->events = new EventEmitter('connect', 'message', 'close');
    }

    public function on(string $event, callable $callback)
    {
        $this->events->on($event, $callback);
    }

    private function emit(string $event, ...$args)
    {
        $this->events->emit($event, ...$args);
    }

    public function connect()
    {
        $this->client = new Client($this->host, $this->port, $this->ssl);
        $this->client->setHeaders([
            'User-Agent' => 'OpenTwitchAssistent/0.0.0.1',
        ]);
        $this->client->upgrade($this->path);
        $this->connected = TRUE;
        $this->emit('connect');


        $this->reader();
    }

    public function send(string $data)
    {
        $this->client->push($data);
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function close()
    {
        $this->client->close();
    }

    public function reader()
    {
        while (TRUE) {
            if (!$this->isConnected()) {
                $this->events->emit('close', $this->client->errCode);
                break;
            }
            $msg = $this->client->recv();
            if($msg) {
                $this->emit('message', $this, $msg);
            } else {
                $this->client->close();
                $this->emit('close', $this->client->errCode);
            }

            co::sleep(0.001);
        }
    }
}