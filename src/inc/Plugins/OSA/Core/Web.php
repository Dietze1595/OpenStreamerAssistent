<?php


namespace Plugins\OSA\Core;


use OSA\Plugins\CorePlugin;
use OSA\Plugins\Plugin;
use OSA\User\User;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Twig\Environment;

class Web extends Plugin
{
    use CorePlugin;

    private static ?Environment $TWIG = null;

    /**
     * @return Environment
     */
    public static function getTWIG(): Environment
    {
        if(self::$TWIG === null) {
            $loader = new \Twig\Loader\FilesystemLoader(self::getTemplateDIR());
            $twig = new \Twig\Environment($loader,
                [
                    'cache' => self::getTemplateDIR().'/_cache/',
                ]);
            self::$TWIG = $twig;
        }
        return self::$TWIG;
    }

    public static function getUser(Request $req) : User|null
    {
        $sessionid = $req->cookie['otasession'] ?? null;
        return null;
    }

    public function getPluginName(): string
    {
        return 'Web';
    }

    public function getAuthorName(): string
    {
        return 'Dennis <Kapsonfire> Kaspar';
    }

    public static function checkPluginDependencies(): bool
    {

    }

    public static function getTemplateDIR() : string {
        return realpath(ROOT. '/templates/');
    }

    public static function getWWWDIR() : string {
        return realpath(ROOT . '/www/');
    }

    public function sendStaticFile($path, Request $req, Response $resp) {
        $etag = md5_file($path);
        $lmd = filemtime($path);

        $resp->header('Last-Modified', gmdate('D, d M Y H:i:s', $lmd)." GMT");
        $resp->header('Etag',$etag);


        if(
            (isset($req->header['if-none-match']) && $req->header['if-none-match'] === $etag)
            or
            (isset($req->header['if-modified-since']) && $lmd == strtotime($req->header['if-modified-since']))
        ) {
            $resp->status('304');
            $resp->end();
            return;
        }

        $resp->sendFile($path);
    }

    protected function onRequest(Request $req, Response $resp) {
        $req_uri = $req->server['request_uri'];
        if($req_uri === '') {
            $req_uri = 'index.php';
        }
        $filepath = self::getWWWDIR().'/'.$req_uri;
        if(file_exists($filepath)) {
            $resp->status(200);
            if(str_ends_with($filepath,'.php')) {
                ob_start();
                $ret = include($filepath);


                if($ret === false) {
                    @ob_end_clean();
                    return;
                }
                $ret  = ob_get_clean();
                $resp->end($ret);
                ob_end_clean();
            } else {
                $this->sendStaticFile($filepath, $req, $resp);
            }

            return;
        }

        $resp->status('404');
        $resp->end('');

    }


    function onActivated(): void
    {
        $this->addRoute('*', [$this, 'onRequest']);
    }

    function onDeactivated(): void
    {
        // TODO: Implement onDeactivated() method.
    }

    public function update(float $from)
    {
        $this->setVersion($this->getVersion());
    }

    public function getVersion(): float
    {
        return 0.1;
    }
}