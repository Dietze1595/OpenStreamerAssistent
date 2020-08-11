<?php


namespace OTA\Twitch;


class APIClient
{
    private TwitchConfig $config;
    public function __construct(TwitchConfig $config)
    {
        if (self::$SELF) {
            throw new \Exception('Only singleton');
        }
        $this->config = $config;
    }
}