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
    private WeakMap $pointsCache;

    public function __construct()
    {
        parent::__construct();
        $this->lastactives = new WeakMap();
        $this->pointsCache = new WeakMap();
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
        $this->loyalityTimer = Timer::tick(60 * 1000, Closure::fromCallable([$this, 'addLoyalityPointsByTimer']));
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
        if(!$this->isActivated())
            return;
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

    private function usePointsByMap(array $addedPoints)
    {
        try {
            $con = PDO::getInstance()->get();
            $con->beginTransaction();
            $stmt = $con->prepare('INSERT INTO `loyality` (`id`, `points`) VALUES (:uid, :points) ON DUPLICATE KEY UPDATE `points`=`points`+VALUES(`points`);');
            foreach ($addedPoints as $k => $userids) {
                $points = floatval(substr($k, 3));
                $stmt->bindValue(':points', $points);
                foreach ($userids as $uid) {
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

    private function getStartPoints() :float {
        return 0;
    }

    public function getUserPoints(User $user) : int {
        if(isset($this->pointsCache[$user])) {
            return $this->pointsCache[$user];
        }
        $this->pointsCache[$user] = $this->_getUserPoints($user);
        return $this->pointsCache[$user];
    }
    private function _getUserPoints(User $user): int
    {
        try {
            $con = PDO::getInstance()->get();
            $stmt = $con->prepare('SELECT * FROM `loyality` WHERE `id` = :id');

            $stmt->bindValue(':id', $user->getUid());
            $stmt->execute();
            if($stmt->rowCount() === 0) {
                $points = $this->getStartPoints();
                $con->prepare('INSERT INTO `loyality` (`id`, `points`) VALUES (?, ?)')->execute([$user->getUid(), $points]);
                return $points;
            }
            return $stmt->fetch(\PDO::FETCH_ASSOC)['points'];


        } catch (\Exception $ex) {
            throw new \Exception('db error getting points');
        } finally {
            isset($con) && PDO::getInstance()->put($con);
        }


    }

    public function addPointsToUser(User $user, float $points): bool
    {
        $pts = $this->getUserPoints($user);
        if($points < 0 && ($pts - $points) < 0)
            return false;
        $newPoints = $points + $pts;


        try {
            $con = PDO::getInstance()->get();
            $stmt = $con->prepare('UPDATE `loyality` SET `points` = :pts WHERE `id` = :id');

            $stmt->bindValue(':id', $user->getUid());
            $stmt->bindValue(':pts', $newPoints);
            $stmt->execute();
            $this->pointsCache[$user] = $newPoints;
        } catch (\Exception $ex) {
            throw new \Exception('db error setting points');
        } finally {
            isset($con) && PDO::getInstance()->put($con);
        }

        return true;

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
        $this->usePointsByMap($addedPoints);
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