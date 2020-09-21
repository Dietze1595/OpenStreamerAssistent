<?php


namespace Plugins\OSA\Free;

use OSA\User\User;
use OSA\Webserver\Webserver;
use Swoole\Timer;
use WeakMap;
use Closure;
use OSA\Database\PDO;
use OSA\Plugins\CorePlugin;
use OSA\Plugins\Plugin;
use OSA\Twitch\ChatClient;
use OSA\Twitch\IRC\BaseMessage;
use OSA\Twitch\IRC\PRIVMSG;
use Plugins\OSA\Core\TwitchUser;

class Loyality extends Plugin
{
    use CorePlugin;


    private WeakMap $lastactives;

    public function __construct()
    {
        parent::__construct();
        $this->lastactives = new WeakMap();
    }

    public function getPluginName(): string
    {
        return 'Loyality';
    }

    public function getAuthorName(): string
    {
        return 'Dennis <Kapsonfire> Kaspar';
    }

    public static function checkPluginDependencies(): bool
    {
        if (!class_exists(TwitchUser::class)) {
            return FALSE;
        }
        return TRUE;
    }

    private $loyalityTimer = 0;

    function onActivated(): void
    {
        ChatClient::getSELF()->on('message', Closure::fromCallable([$this, 'onTwitchMessage']));
        $this->loyalityTimer = Timer::tick(15000, Closure::fromCallable([$this, 'addLoyalityPointsByTimer']));
    }

    function onDeactivated(): void
    {
        Timer::clear($this->loyalityTimer);
    }

    public function update(float $from)
    {
        if ($from == 0) {
            if (!PDO::getInstance()->tableExists('loyality')) {
                $db = PDO::getInstance()->get();
                $db->query('CREATE TABLE `loyality` (
                `id` int,
                `points` double,
                PRIMARY KEY (`id`)
            )');
                DEBUG_LOG('CREATING TABLE: loyality');

            }
            $this->setVersion(0.1);
            return;
        }
    }

    private array $cache = [];


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


    private function getActiveTreshhold(): int
    {
        return 120;
    }

    private function getMinutesPoints(): int|float
    {
        return 1;
    }

    private function getActiveMulti(): int|float
    {
        return 1.5;
    }

    private function addLoyalityPointsByTimer(): void
    {
        $this->deleteOldActives();

        /**
         * @type User[] $users
         */
        $users = [];
        $userNames = array_keys(ChatClient::getSELF()->getUserlist());
        foreach ($userNames as $username) {
            $tmpUser = TwitchUser::getInstance()->getUserByName($username);
            if ($tmpUser)
                $users[] = $tmpUser;
        }

        $addedPoints = [];
        $basePoints = $this->getMinutesPoints();
        $activeMulti = $this->getActiveMulti();


        foreach ($users as $user) {
            $points = $basePoints;
            if (isset($this->lastactives[$user])) {
                $points *= $activeMulti;
            }

            $addedPoints['add' . $points][] = $user->getUid();
        }

        try {
            $con = PDO::getInstance()->get();
            $con->beginTransaction();
            $stmt = $con->prepare('INSERT INTO `loyality` (`id`, `points`) VALUES (:uid, :points) ON DUPLICATE KEY UPDATE `points`=`points`+VALUES(`points`);');
            foreach ($addedPoints as $k => $userids) {
                $points = floatval(substr($k, 3));
                $stmt->bindValue(':points', $points);
                foreach($userids as $uid) {
                    $stmt->bindValue(':uid', $uid);
                    $stmt->execute();
                }
            }
            $con->commit();
        } catch (\Exception $ex) {
            isset($con) && $con->inTransaction() && $con->rollBack();
        } finally {
            isset($con) && PDO::getInstance()->put($con);
        }
    }

    private function deleteOldActives()
    {
        foreach ($this->lastactives as $k => $v) {
            if ((time() - $v['time']) > $this->getActiveTreshhold()) {
                unset($this->lastactives[$k]);
            }
        }
    }

    public function onPrivMSG(\OSA\Twitch\IRC\PRIVMSG $msg): void
    {
        $user = TwitchUser::getInstance()->createOrGetUser($msg->getFrom(), $msg->getUserid());
        $this->lastactives[$user] = ['time' => time()];
        $this->deleteOldActives();
    }
}