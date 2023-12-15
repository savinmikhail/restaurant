<?php

namespace app\Services\api;

class BaseService
{
    protected const HTTP_OK = 200;
    protected const HTTP_CREATED = 201;
    protected const HTTP_BAD_REQUEST = 400;
    protected const HTTP_UNAUTHORIZED = 401;
    protected const HTTP_FORBIDDEN = 403;
    protected const HTTP_NOT_FOUND = 404;
}
