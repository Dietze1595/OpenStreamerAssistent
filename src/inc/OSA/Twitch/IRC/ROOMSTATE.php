<?php


namespace OSA\Twitch\IRC;


class ROOMSTATE extends BaseMessage
{
    private string $channel;
    public function __construct(?TwitchIRCTags $tags, string $from, string $unknown = '')
    {
        parent::__construct('ROOMSTATE', $tags);
        $this->channel = trim($unknown);
    }

    public function getDisplayName() : string {
        return $this->tags['display-name'];
    }
    public function getChannel() : string {
        return $this->channel;
    }
}