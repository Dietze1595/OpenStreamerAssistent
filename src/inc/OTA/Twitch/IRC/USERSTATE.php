<?php


namespace OTA\Twitch\IRC;


class USERSTATE extends BaseMessage
{
    private string $channel;
    public function __construct(?TwitchIRCTags $tags, string $from, string $unknown = '')
    {
        parent::__construct('USERSTATE', $tags);
        $this->channel = trim($unknown);
    }

    public function getDisplayName() : string {
        return $this->tags['display-name'];
    }
    public function getChannel() : string {
        return $this->channel;
    }
}