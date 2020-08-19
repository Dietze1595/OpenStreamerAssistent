<?php


namespace OSA\Twitch\IRC;


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



        $type = trim($type);
        $classname = 'OSA\\Twitch\\IRC\\'.$type;
        if(class_exists($classname, true)) {
            return new $classname($tags, $from, $msg);
        }
        switch(trim($type)) {
            case '001': //welcome
            case '002': //hostname
            case '003': //server new
            case '004': //-
            case '372': //>
            case '375': //-
            case '376': //>
            case '366': //end of userlist
                return null;

           case '353': //userlist
                return new USERLIST($tags, $from, $msg);

        }

        DEBUG_LOG('UNKNOWN IRC TYPE: '.$type);
        return new BaseMessage($type, $tags, $msg);
    }
}