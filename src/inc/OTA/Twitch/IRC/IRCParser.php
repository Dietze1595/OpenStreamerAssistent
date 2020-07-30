<?php


namespace OTA\Twitch\IRC;


class IRCParser
{
    public static function parse(string $msg) :?BaseMessage {
        if(str_starts_with($msg, 'PING ')) {
            $unknown = substr($msg, 5);
            return new BaseMessage('PING', null, $unknown);
        }
        if(str_ends_with($msg, PHP_EOL)) {
            $msg = substr($msg, 0, -1);
        }


        $tags = new TwitchIRCTags();
        if(str_starts_with($msg, '@')) {
            [$tagsStr, $msg] = explode(' ', $msg, 2);
            $explodedTags = explode(';', substr($tagsStr,1)); //skip @
            $tagsArr = [];
            foreach ($explodedTags as $tagPair) {
                [$k, $v] = explode('=', $tagPair);
                $tagsArr[$k] = $v;
            }
            $tags->addData($tagsArr);
        }


        $parts = explode(' ', $msg, 3);
        [$from, $type, $msg] = [$parts[0]??'',$parts[1]??'',$parts[2]??''];

        switch($type) {
            case 'JOIN':
                return new JOIN($tags, $from, $msg);
            case 'PRIVMSG':
                return new PRIVMSG($tags, $from, $msg);
            case '353': //userlist
                return new USERLIST($tags, $from, $msg);
        }


        return new BaseMessage($type, $tags, $msg);
    }
}