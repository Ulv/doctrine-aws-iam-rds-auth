<?php

namespace App\Lead\DoctrineIamRdsAuthBundle\Adapter;

interface AdapterInterface
{
    /**
     * @param string $endpoint
     * @return AdapterInterface
     */
    public function setEndpoint(string $endpoint): self;

    /**
     * @return string
     */
    public function getTempToken(): string;
}