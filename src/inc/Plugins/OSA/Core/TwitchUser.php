<?php


namespace Plugins\OSA\Core;


use Closure;
use OSA\Database\PDO;
use OSA\Plugins\CorePlugin;
use OSA\Plugins\Plugin;
use OSA\Twitch\ChatClient;
use OSA\Twitch\IRC\BaseMessage;
use OSA\Twitch\IRC\PRIVMSG;
use OSA\User\User;

class TwitchUser extends Plugin
{
    use CorePlugin;

    public function getPluginName(): string
    {
        return 'TwitchUser';
    }

    public function getAuthorName(): string
    {
        return 'Dennis <Kapsonfire> Kaspar';
    }

    public static function checkPluginDependencies(): bool
    {
        return TRUE;
    }

    function onActivated(): void
    {
        ChatClient::getSELF()->on('message', Closure::fromCallable([$this, 'onTwitchMessage']));
    }

    function onDeactivated(): void
    {
        // TODO: Implement onDeactivated() method.
    }

    public function update(float $from)
    {
        if ($from == 0) {
            if (!PDO::getInstance()->tableExists('users')) {
                $db = PDO::getInstance()->get();
                $db->query('CREATE TABLE `users` (
                `id` int,
                `name` varchar(50),
                PRIMARY KEY (`id`)
            )');
                DEBUG_LOG('CREATING TABLE: users');

            }
            $this->setVersion(0.1);
            return;
        }
    }

    private array $cache = [];

    public function getUserByID(int $uid): ?User
    {
        if (isset($this->cache[$uid])) {
            DEBUG_LOG('GET USER BY CACHE: ' . $uid);
            $this->cache[$uid]['time'] = time();
            return $this->cache[$uid]['user'];
        }
        try {
            $con = PDO::getInstance()->get();
            $stmt = $con->prepare('SELECT * FROM `users` WHERE `id` = :uid');
            $stmt->bindValue(':uid', $uid);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                return NULL;
            }
            $user = new User($stmt->fetch(\PDO::FETCH_ASSOC));
            $this->cache[$uid] = ['time' => time(), 'user' => $user];
            return $user;
        } catch (\Exception $exception) {

        } finally {
            isset($con) && PDO::getInstance()->put($con);
        }
        return NULL;
    }

    public function createOrGetUser(string $username, int $uid): User
    {
        DEBUG_LOG('CREATE OR GET: ' . $username . ' (' . $uid . ')');
        $user = $this->getUserByID($uid);
        if ($user !== NULL) return $user;
        try {
            $con = PDO::getInstance()->get();
            $stmt = $con->prepare('INSERT INTO `users` SET `id` = :uid, `name` = :name');
            $stmt->bindValue(':name', $username);
            $stmt->bindValue(':uid', $uid);
            $stmt->execute();
            return $this->getUserByID($uid);
        } catch (\Exception $ex) {
        } finally {
            isset($con) && PDO::getInstance()->put($con);
        }

    }

    public function getVersion(): float
    {
        return 0.1;
    }


    public function onTwitchMessage(string $event, ChatClient $client, BaseMessage $msg): void
    {
        if ($msg instanceof PRIVMSG) {
            $this->onPrivMSG($msg);
        }
    }

    public function onPrivMSG(\OSA\Twitch\IRC\PRIVMSG $msg): void
    {
        $uid = $msg->getUserid();
        $name = $msg->getFrom();
        $this->createOrGetUser($name, $uid);
    }
}