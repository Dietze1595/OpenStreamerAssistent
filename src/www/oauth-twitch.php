<?php

/**
 * @var Swoole\Http\Request $req
 * @var Swoole\Http\Response $resp
 */

$user = \Plugins\OTA\Core\Web::getUser($req);
$twitchConfig = \OSA\Twitch\ChatClient::getSELF()->getConfig();
$webConfig = \OSA\Webserver\Webserver::getInstance()->getConfig();
$redirect = sprintf('%s://%s/oauth-twitch.php', $req->server['server_port'] ==  $webConfig->getPort() ? 'http' : 'https', $req->header['host']);
if($user !== null) {
    $resp->redirect('index.php');
    return false;
}

$code = \OSA\Twitch\APIClient::getInstance()->getOAUTH2byCode($_GET['code'], $redirect);
if(!isset($code['access_token'])) {
    $resp->redirect('index.php');
    return false;
}
$user = \OSA\Twitch\APIClient::getInstance()->getUserByOAuth($code['access_token']);


var_dump($user);
//$resp->redirect('index.php');
return false;