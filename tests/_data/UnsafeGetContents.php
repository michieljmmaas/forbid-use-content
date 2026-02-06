<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;

function test(ResponseInterface $response): void
{
    $body = $response->getBody();
    $body->getContents();
}
