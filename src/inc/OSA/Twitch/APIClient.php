<?php


namespace OSA\Twitch;


class APIClient
{
    private static ?APIClient $SELF = null;

    public static function getInstance() : static {
        if(self::$SELF !== null) return self::$SELF;
        self::$SELF = new static();

        return self::$SELF;
    }
    private function getConfig() : TwitchConfig {
        return ChatClient::getSELF()->getConfig();
    }
    private function __construct()
    {
        if (self::$SELF) {
            throw new \Exception('Only singleton');
        }
    }

    public function getOAUTH2byCode(string $code, string $redirectURI) {
        $url = 'https://id.twitch.tv/oauth2/token';

        $data = array(
            'client_id' => $this->getConfig()->getAPIClientID(),
            'client_secret' => $this->getConfig()->getAPIClientSecret(),
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectURI
        );
        $curl = \curl_init();
        \curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_FOLLOWLOCATION => FALSE,
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => TRUE
        ));
        $resp = \curl_exec($curl);
        \curl_close($curl);

        return json_decode($resp, true);

    }

    public function getUserByOAuth(string $oauth) : ?User {
        $url = 'https://id.twitch.tv/oauth2/validate';
        $curl = \curl_init();

        $headers = [
            'Authorization: OAuth '.$oauth
        ];
        \curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_FOLLOWLOCATION => FALSE,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => $headers
        ));

        $resp = \curl_exec($curl);
        \curl_close($curl);

        $userdata = json_decode($resp, true);
        $username = $userdata['login'];
        $userid = $userdata['user_id'];

        return null;
    }
}