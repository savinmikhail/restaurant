<?php

namespace app\Services\api\user_app\import_helpers;

interface ImportHelperInterface
{
    public function process(array $data);
}