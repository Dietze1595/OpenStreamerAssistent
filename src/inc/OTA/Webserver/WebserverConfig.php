<?php


namespace OTA\Webserver;


class WebserverConfig
{
    private ?string $cert_file = null;
    private ?string $cert_key = null;
    private int $portSSL = 443;
    private int $port = 80;
    private string $ipv4 = '127.0.0.1';
    private string $ipv6 = '::1';


    public function __construct(array $config)
    {
        foreach ($config as $k => $v) {
            if(property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }

    /**
     * @return int
     */
    public function getPortSSL(): int
    {
        return $this->portSSL;
    }

    /**
     * @return string|null
     */
    public function getCertKey(): ?string
    {
        return $this->cert_key;
    }

    /**
     * @return string|null
     */
    public function getCertFile(): ?string
    {
        return $this->cert_file;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getIpv4(): string
    {
        return $this->ipv4;
    }

    /**
     * @return string
     */
    public function getIpv6(): string
    {
        return $this->ipv6;
    }


}