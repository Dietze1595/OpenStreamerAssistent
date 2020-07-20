<?php

use OTA\Twitch\ChatClient;
use OTA\Twitch\ChatClientConfig;
use OTA\Webserver\WebserverConfig;

define('ROOT', __DIR__);
define('DEBUG', TRUE);
require ROOT . '/vendor/autoload.php';


Swoole\Runtime::enableCoroutine();

$webserverJSON = json_decode(file_get_contents(ROOT . '/config/Webserver.json'), TRUE);
$config = new WebserverConfig($webserverJSON);
$webserver = new OTA\Webserver\Webserver($config);

//since we have eventloop inside the webserver we need the webserver to start the coroutine for our other processes
$webserver->addStartCoroutine(function() {
    $twitchJSON = json_decode(file_get_contents(ROOT.'/config/TwitchChatClient.json'), true);
    $config = new ChatClientConfig($twitchJSON);
    $client = new ChatClient($config);
    $client->connect();
});

function DEBUG_LOG(string $log)
{
    if (defined('DEBUG'))
        echo sprintf('%s %s', date('H:i:s'), $log) . PHP_EOL;
}


$webserver->start();
