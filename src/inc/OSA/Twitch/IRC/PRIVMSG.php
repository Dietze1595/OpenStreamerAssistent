<?php


namespace OSA\Twitch\IRC;


class PRIVMSG extends BaseMessage
{
    private string $from;
    private int $userid;
    private string $channel;
    private bool $isAction = false;
    private string $message;
    public function __construct(?TwitchIRCTags $tags, string $from, string $unknown = '')
    {
        parent::__construct('PRIVMSG', $tags);
        $this->from = $tags['display-name'];
        $this->userid = intval($tags['user-id']);
        [$channel, $message] = explode(' :', $unknown, 2);


        $actionBeginning = chr(1).'ACTION';
        $this->isAction = str_starts_with($message, $actionBeginning);

        if($this->isAction) {
            $message = substr($message, strlen($actionBeginning) + 1, -2);
        }

        $this->message = rtrim($message);
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function getFrom() : string
    {
        return $this->from;
    }

    /**
     * @return int
     */
    public function getUserid(): int
    {
        return $this->userid;
    }

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isAction(): bool
    {
        return $this->isAction;
    }

    /**
     * @return string
     */
    public function getChannel() : string
    {
        return $this->channel;
    }
}