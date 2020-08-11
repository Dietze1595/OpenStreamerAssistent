<?php

use OTA\Twitch\ChatClient;
use OTA\Twitch\TwitchConfig;
use OTA\Twitch\IRC\BaseMessage;
use OTA\Webserver\WebserverConfig;

define('ROOT', __DIR__);
define('DEBUG', TRUE);
require ROOT . '/vendor/autoload.php';


Swoole\Runtime::enableCoroutine();


$config = new WebserverConfig(ROOT . '/config/Webserver.json');
$webserver = new OTA\Webserver\Webserver($config);

//since we have eventloop inside the webserver we need the webserver to start the coroutine for our other processes
$webserver->addStartCoroutine(function() {
    $config = new TwitchConfig(ROOT.'/config/Twitch.json');
    $client = new ChatClient($config);
    $client->connect();
});

function DEBUG_LOG(string $log)
{
    if (defined('DEBUG'))
        echo sprintf('%s %s', date('H:i:s'), $log) . PHP_EOL;
}


$webserver->start();
