<?php


namespace OTA\Twitch;


class ChatClientConfig
{
    /**
     * @var string the oauth token from twitch - visit: https://twitchapps.com/tmi/
     */
    private string $authtoken;
    /**
     * @var string the username of the bot
     */
    private string $username;
    /**
     * @var string The Channel the bot joins on connect
     */
    private string $channel;

    /**
     * @return string
     */
    public function getAuthtoken(): string
    {
        return $this->authtoken;
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    public function __construct(array $config)
    {
        foreach ($config as $k => $v) {
            if(property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }
}