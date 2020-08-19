<?php


namespace Plugins\OTA\Core;


use OSA\Database\PDO;
use OSA\Plugins\CorePlugin;
use OSA\Plugins\Plugin;

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
        return true;
    }

    function onActivated(): void
    {

    }

    function onDeactivated(): void
    {
        // TODO: Implement onDeactivated() method.
    }

    public function update(float $from)
    {
        if($from == 0) {
            if(!PDO::getInstance()->tableExists('users')) {
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

    public function getVersion(): float
    {
        return 0.1;
    }
}