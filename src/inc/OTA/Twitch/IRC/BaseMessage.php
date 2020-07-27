<?php


namespace OTA\Twitch\IRC;


class BaseMessage
{
    protected int $timestamp;
    protected TwitchIRCTags $tags;
    protected string $type;
    protected string $unknown;

    public function __construct(string $type, ?TwitchIRCTags $tags, string $unknown = '') {
        $this->timestamp = floor(microtime(true)*1000/1000);
        $this->type = $type;
        $this->tags = $tags ?? new TwitchIRCTags();
        $this->unknown = $unknown;
    }

    /**
     * @return TwitchIRCTags
     */
    public function getTags(): TwitchIRCTags
    {
        return $this->tags;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->tags['tmi-sent-ts'] ?: $this->timestamp;
    }


}