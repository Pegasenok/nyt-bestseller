<?php

namespace App\DTO;

interface HttpAwareDtoInterface
{
    public function getHttpEndpoint(): string;

    public function getHttpParameters(): array;

    public function getEndpointJsonSchema();
}
