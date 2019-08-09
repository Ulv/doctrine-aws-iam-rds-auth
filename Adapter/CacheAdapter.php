<?php

namespace App\Lead\DoctrineIamRdsAuthBundle\Adapter;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class CacheAdapter
 * Class for cache AWS token
 * @package App\Lead\DoctrineIamRdsAuthBundle\Adapter
 */
class CacheAdapter implements AdapterInterface
{
    /**
     * @var AdapterInterface
     */
    private $next;

    /**
     * @var int
     */
    private $cacheTimeSec = 600;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * AwsIAMCachePasswordConnector constructor.
     * @param AdapterInterface $next
     * @param int $cacheTimeMin
     */
    public function __construct(AdapterInterface $next, int $cacheTimeMin)
    {
        $this->next = $next;
        $this->cache = $this->getCacheInstance();
        $this->cacheTimeSec = $cacheTimeMin * 60;
    }

    /**
     * @return CacheInterface
     */
    protected function getCacheInstance(): CacheInterface
    {
        return new FilesystemAdapter();
    }

    /**
     * @inheritDoc
     */
    public function getTempToken(): string
    {
        return $this->cache->get($this->getKey(), function (ItemInterface $item) {
            $item->expiresAfter($this->cacheTimeSec);
            return $this->next->getTempToken();
        });
    }

    /**
     * @return string
     */
    private function getKey(): string
    {
        return 'RDS_auth_token';
    }

    /**
     * @inheritDoc
     */
    public function setEndpoint(string $endpoint): AdapterInterface
    {
        $this->next->setEndpoint($endpoint);
        return $this;
    }
}