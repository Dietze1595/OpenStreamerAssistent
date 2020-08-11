<?php


namespace OTA\Webserver;


use OTA\JSONConfig;

class WebserverConfig
{
    /**
     * DEFAULT VALUES
     */
    private ?string $cert_file = null;
    private ?string $cert_key = null;
    private int $portSSL = 443;
    private int $port = 80;
    private string $ipv4 = '127.0.0.1';
    private string $ipv6 = '::1';


    private JSONConfig $config;
    public function __construct(string $path)
    {
        $this->config = JSONConfig::get($path);
    }

    /**
     * @return int
     */
    public function getPortSSL(): int
    {
        return $this->config->portSSL ?? $this->portSSL;
    }

    /**
     * @return string|null
     */
    public function getCertKey(): ?string
    {
        return $this->config->cert_key ?? $this->cert_key;
    }

    /**
     * @return string|null
     */
    public function getCertFile(): ?string
    {
        return $this->config->cert_file ?? $this->cert_file;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->config->port ?? $this->port;
    }

    /**
     * @return string
     */
    public function getIpv4(): string
    {
        return $this->config->ipv4 ?? $this->ipv4;
    }

    /**
     * @return string
     */
    public function getIpv6(): string
    {
        return $this->config->ipv6 ?? $this->ipv6;
    }


}