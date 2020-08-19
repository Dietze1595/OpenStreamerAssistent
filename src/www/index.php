<?php
/**
 * @var Swoole\Http\Request $req
 * @var Swoole\Http\Response $resp
 */

$twig = \Plugins\OTA\Core\Web::getTWIG();
$user = \Plugins\OTA\Core\Web::getUser($req);
$twitchConfig = \OSA\Twitch\ChatClient::getSELF()->getConfig();
$webConfig = \OSA\Webserver\Webserver::getInstance()->getConfig();


if($user === null) {
    $clientid = $twitchConfig->getAPIClientID();
    $redirect = sprintf('%s://%s/oauth-twitch.php', $req->server['server_port'] ==  $webConfig->getPort() ? 'http' : 'https', $req->header['host']);
    $resp->redirect(sprintf('https://id.twitch.tv/oauth2/authorize?client_id=%s&redirect_uri=%s&response_type=code&scope=user:read:email',$clientid,$redirect));
    return false;
}