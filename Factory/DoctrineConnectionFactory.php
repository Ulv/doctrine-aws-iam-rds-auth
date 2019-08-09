<?php

namespace App\Lead\DoctrineIamRdsAuthBundle\Factory;

use App\Lead\DoctrineIamRdsAuthBundle\Adapter\CacheAdapter;
use App\Lead\DoctrineIamRdsAuthBundle\Adapter\IamAdapter;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

/**
 * Class DoctrineConnectionFactory
 * Doctrine connection factory
 * @package App\Lead\DoctrineIamRdsAuthBundle\Factory
 */
class DoctrineConnectionFactory extends ConnectionFactory
{
    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var bool
     */
    private $useIamAsPasswordProvider;

    /**
     * Fallback AWS region
     * @var string
     */
    private $awsRegion;

    /**
     * Fallback IAM username
     * @var string
     */
    private $iamUsername;

    /**
     * Cache AWS token locally
     * @var bool
     */
    private $useTokenCache;

    /**
     * Time to keep token in cache, minutes
     * @var int
     */
    private $tokenCacheTimeMinutes;

    /**
     * DoctrineConnectionFactory constructor.
     * @param array $typesConfig
     */
    public function __construct(array $typesConfig)
    {
        parent::__construct($typesConfig);

        $this->useIamAsPasswordProvider = (bool)getenv('DATABASE_USE_IAM');
        $this->awsRegion = getenv('AWS_REGION');
        $this->iamUsername = getenv('IAM_USERNAME');
        $this->useTokenCache = (bool)getenv('IAM_USE_TOKEN_CACHE');
        $this->tokenCacheTimeMinutes = getenv('IAM_TOKEN_CACHE_TIME_MINUTES');
    }

    /**
     * @inheritDoc
     */
    public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null, array $mappingTypes = [])
    {
        if ($this->useIamAsPasswordProvider) {
            $this->endpoint = $this->buildEndpoint($params);
            $params['password'] = $this->fetchTempPasswordFromAws();
        }

        return parent::createConnection($params, $config, $eventManager, $mappingTypes);
    }

    /**
     * Returns temporary IAM token (which is used as db password) for the RDS database
     * @return string
     */
    private function fetchTempPasswordFromAws(): string
    {
        $provider = new IamAdapter($this->awsRegion, $this->iamUsername);
        if ($this->useTokenCache) {
            $provider = new CacheAdapter($provider, $this->tokenCacheTimeMinutes);
        }
        return $provider->setEndpoint($this->endpoint)->getTempToken();
    }

    /**
     * Returns endpoint <host>:<port> to use with AWS token provider
     * @param array $params
     * @return string
     */
    private function buildEndpoint(array $params): string
    {
        return sprintf('%s:%s', $params['host'], $params['port']);
    }
}