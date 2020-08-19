<?php


namespace OSA\Twitch;


use OSA\JSONConfig;

class TwitchConfig
{
    private JSONConfig $config;

    /**
     * returns the oauth token from twitch - visit: https://twitchapps.com/tmi/
     * @return string
     */
    public function getAuthtoken(): string
    {
        return $this->config->authtoken;
    }

    /**
     * returns The Channel the bot joins on connect
     * @return string
     */
    public function getChannel(): string
    {
        return $this->config->channel;
    }

    /**
     * returns the username of the bot
     * @return string
     */
    public function getUsername(): string
    {
        return $this->config->username;
    }

    /**
     * returns the clientid of the app for oauth
     * @return string
     */
    public function getAPIClientID(): string
    {
        return $this->config->appid;
    }

    /**
     * returns the clientid of the app for oauth
     * @return string
     */
    public function getAPIClientSecret(): string
    {
        return $this->config->appsecret;
    }

    /**
     * returns the username of the bot
     * @param string $name
     * @return TwitchConfig
     */
    public function setUsername(string $name): static
    {
        $this->config->username = $name;
        return $this;
    }

    public function __construct(string $path)
    {
        $this->config = JSONConfig::get($path);
    }

}