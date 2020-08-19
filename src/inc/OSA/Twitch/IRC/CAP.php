<?php


namespace OSA\Twitch\IRC;


class CAP extends BaseMessage
{
    public function __construct(?TwitchIRCTags $tags, string $from, string $unknown = '')
    {
        parent::__construct('CAP', $tags, $unknown);
    }
}