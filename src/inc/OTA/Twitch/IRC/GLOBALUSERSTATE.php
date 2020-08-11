<?php


namespace OTA\Twitch\IRC;


class GLOBALUSERSTATE extends BaseMessage
{
    public function __construct(?TwitchIRCTags $tags, string $from, string $unknown = '')
    {
        parent::__construct('GLOBALUSERSTATE', $tags);
    }

    public function getDisplayName() : string {
        return $this->tags['display-name'];
    }
}