<?php

namespace App\Lead\DoctrineIamRdsAuthBundle\Adapter;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;

/**
 * Class IamAdapter
 * Fetches user token from IAM service
 * @package App\Lead\DoctrineIamRdsAuthBundle\Adapter
 */
class IamAdapter implements AdapterInterface
{
    /**
     * @var AuthTokenGenerator
     */
    private $authTokenGenerator;

    /**
     * @var string
     */
    private $awsRegion;

    /**
     * @var string
     */
    private $awsUser;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * AwsIAMPasswordConnector constructor.
     * @param string $awsRegion
     * @param string $awsUser
     */
    public function __construct(string $awsRegion, string $awsUser)
    {
        $this->awsRegion = $awsRegion;
        $this->awsUser = $awsUser;
        $this->authTokenGenerator = new AuthTokenGenerator($this->getCredentialsProvider());
    }

    /**
     * Using EC2 instance role
     * @return callable
     */
    protected function getCredentialsProvider(): callable
    {
        return CredentialProvider::instanceProfile();
    }

    /**
     * @inheritDoc
     */
    public function getTempToken(): string
    {
        return $this->authTokenGenerator->createToken(
            $this->endpoint,
            $this->awsRegion,
            $this->awsUser
        );
    }

    /**
     * @inheritDoc
     */
    public function setEndpoint(string $endpoint): AdapterInterface
    {
        $this->endpoint = $endpoint;
        return $this;
    }
}