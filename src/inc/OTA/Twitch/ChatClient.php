<?php


namespace OTA\Twitch;

use Closure;
use OTA\EventEmitter;
use OTA\Swoole\WebsocketClient;
use OTA\Twitch\IRC\BaseMessage;
use OTA\Twitch\IRC\IRCParser;
use OTA\Twitch\IRC\JOIN;
use OTA\Twitch\IRC\PART;
use OTA\Twitch\IRC\USERLIST;
use Swoole\WebSocket\Frame;

class ChatClient
{
    static private ?ChatClient $SELF = null;
    private ?WebsocketClient $client;
    private ChatClientConfig $config;
    private bool $reconnect = true;
    private EventEmitter $events;

    private array $userlist = [];

    private function removeUser(string $user)
    {
        $name = trim(strtolower($user));
        unset($this->userlist[$user]);
    }

    private function addUser(string $user)
    {
        $name = trim(strtolower($user));
        $this->userlist[$user] = time();
    }

    public function __construct(ChatClientConfig $config)
    {
        if (self::$SELF) {
            throw new \Exception('Only singleton');
        }
        $this->config = $config;
        $this->events = new EventEmitter('connect', 'message', 'close');
        $this->client = null;
        $this->on('message', Closure::fromCallable([$this, 'onIRCMessage']));




        self::$SELF = $this;
    }

    /**
     * @return ChatClient|null
     */
    public static function getSELF(): ?ChatClient
    {
        return self::$SELF;
    }

    public function close()
    {
        $this->reconnect = false;
        $this->client->close();
    }

    /**
     * @return string[]
     */
    public function getUserlist(): array
    {
        return $this->userlist;
    }

    private function onClose(string $event, int $errCode)
    {
        $this->events->emit('close');
        if ($this->reconnect) {
            $this->client->connect();
        }
    }

    public function send(string $data)
    {
        if (!str_starts_with($data, 'PASS ')) {
            DEBUG_LOG('[OUT] ' . $data);
        } else {
            //hide password inside the log
            DEBUG_LOG('[OUT] PASS *****************');
        }

        $this->client->send($data);
    }

    public function connect(bool $reconnect = true)
    {
        $this->reconnect = $reconnect;
        if ($this->client != NULL) {
            $this->client->isConnected() && $this->client->close();
            $this->client = NULL;
        }
        $this->client = new WebsocketClient('wss://irc-ws-r.chat.twitch.tv:443');


        $this->client->on('connect', Closure::fromCallable([$this, 'onConnected']));
        $this->client->on('close', Closure::fromCallable([$this, 'onClose']));
        $this->client->on('message', Closure::fromCallable([$this, 'onMessage']));


        $this->client->connect();
    }


    private function onIRCMessage(string $event, ChatClient $client, BaseMessage $msg)
    {
        if ($msg instanceof JOIN) {
            $this->addUser($msg->getUsername());
        } else if ($msg instanceof PART) {
            $this->removeUser($msg->getUsername());
        } else if ($msg instanceof USERLIST) {
            array_map(fn($user) => $this->addUser($user), $msg->getUsers());
        }
    }

    public function on(string $event, callable $cb)
    {
        $this->events->on($event, $cb);
    }

    private function readLineFromString(string &$msg)
    {
        $pos = strpos($msg, "\n");
        if ($pos === -1 || $pos === false) return null;
        $left = substr($msg, 0, $pos);
        $right = substr($msg, $pos + 1);
        $msg = $right;
        return $left;
    }

    private function onMessage(string $event, WebsocketClient $client, Frame $frame)
    {
        static $messageStack = '';
        $messageStack .= $frame->data;
        while (($msg = $this->readLineFromString($messageStack)) !== null) {
            DEBUG_LOG('[INC] ' . $msg);
            if (str_starts_with($msg, 'PING ')) { //answer PING
                $this->send('PONG');
            }
            $msg = IRCParser::parse($msg);
            $this->events->emit('message', $this, $msg);
        }
    }

    private function onConnected(string $event)
    {
        $this->userlist = [];

        $this->send('CAP REQ :twitch.tv/commands');
        $this->send('CAP REQ :twitch.tv/membership');
        $this->send('CAP REQ :twitch.tv/tags');
        $this->send('PASS oauth:' . $this->config->getAuthtoken());
        $this->send('NICK ' . $this->config->getUsername());
        $this->send('JOIN #' . $this->config->getChannel());


        $this->events->emit('connect');
    }

}