<?php


namespace OTA\Twitch\IRC;


class JOIN extends BaseMessage
{
    private string $channel;
    private string $username;
    public function __construct(?TwitchIRCTags $tags, string $from, string $channel)
    {
        parent::__construct('JOIN', $tags);
        $this->channel = $channel;
        $this->username = substr(explode('!',$from)[0], 1);
    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getUsername(): string {
        return $this->username;
    }
}