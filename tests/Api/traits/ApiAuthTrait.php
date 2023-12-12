<?php

namespace tests\Api\traits;

use ApiTester;

trait ApiAuthTrait
{
    public function setAuthorizationHeader(ApiTester $I)
    {
        $authHeader = 'Basic ' . base64_encode("admin:" . $_ENV['API_PASSWORD']);
        $I->haveHttpHeader('Authorization', $authHeader);
    }
}