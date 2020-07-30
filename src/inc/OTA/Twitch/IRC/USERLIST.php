<?php


namespace OTA\Twitch\IRC;


class USERLIST extends BaseMessage
{

    private string $channel;
    /**
     * @var string[]
     */
    private array $users;
    public function __construct(?TwitchIRCTags $tags, string $from, string $unknown = '')
    {
        parent::__construct('353', $tags);

        $firstEqual = strpos($unknown, '=');
        $unknown = substr($unknown, $firstEqual + 2);
        [$channel, $userlistString] = explode(' :', $unknown, 2);
        $this->users = array_map('trim', explode(' ',$userlistString));
        $this->channel = $channel;

    }

    /**
     * @return string
     */
    public function getChannel(): string
    {
        return $this->channel;
    }

    /**
     * @return string[]
     */
    public function getUsers(): array
    {
        return $this->users;
    }
}