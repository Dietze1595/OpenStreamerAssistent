<?php


namespace OTA\Twitch;


use OTA\JSONConfig;

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