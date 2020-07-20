<?php


namespace OTA\Twitch;


use OTA\EventEmitter;
use OTA\Swoole\WebsocketClient;
use Swoole\WebSocket\Frame;

class ChatClient
{
    private ?WebsocketClient $client;
    private ChatClientConfig $config;
    private bool $reconnect = true;
    private EventEmitter $events;

    public function __construct(ChatClientConfig $config)
    {
        $this->config = $config;
        $this->events = new EventEmitter('connect', 'message', 'close');
        $this->client = null;
    }

    public function close() {
        $this->reconnect = false;
        $this->client->close();
    }

    public function onClose(string $event, int $errCode) {
        $this->events->emit('close');
        if($this->reconnect) {
            $this->client->connect();
        }
    }

    public function send(string $data) {
        if(!str_starts_with($data, 'PASS ')) {
            DEBUG_LOG('[OUT] '.$data);
        } else {
            //hide password inside the log
            DEBUG_LOG('[OUT] PASS *****************');
        }

        $this->client->send($data);
    }

    public function connect(bool $reconnect = true) {
        $this->reconnect = $reconnect;
        if ($this->client != NULL) {
            $this->client->isConnected() && $this->client->close();
            $this->client = NULL;
        }
        $this->client = new WebsocketClient('wss://irc-ws-r.chat.twitch.tv:443');


        $this->client->on('connect', [$this, 'onConnected']);
        $this->client->on('close', [$this, 'onClose']);
        $this->client->on('message', [$this, 'onMessage']);


        $this->client->connect();
    }

    private function readLineFromString(string &$msg) {
        $pos = strpos($msg, "\n");
        if($pos === -1 || $pos === false) return null;
        $left = substr($msg, 0, $pos);
        $right = substr($msg, $pos+1);
        $msg = $right;
        return $left;
    }

    public function onMessage(string $event, WebsocketClient $client, Frame $frame) {
        static $messageStack = '';
        $messageStack .= $frame->data;
        while(($msg = $this->readLineFromString($messageStack)) !== null) {
            DEBUG_LOG('[INC] '.$msg);
            if(str_starts_with($msg, 'PING ')) { //answer PING
                $this->send('PONG');
            }
            //@TODO: Parse message and add event handlers
        }
    }

    public function onConnected(string $event) {
        $this->send('CAP REQ :twitch.tv/commands');
        $this->send('CAP REQ :twitch.tv/membership');
        $this->send('CAP REQ :twitch.tv/tags');
        $this->send('PASS oauth:'.$this->config->getAuthtoken());
        $this->send('NICK '.$this->config->getUsername());
        $this->send('JOIN #'.$this->config->getChannel());


        $this->events->emit('connect');
    }

}