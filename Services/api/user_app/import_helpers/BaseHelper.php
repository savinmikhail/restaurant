<?php

namespace app\Services\api\user_app\import_helpers;

use Exception;

class BaseHelper
{
    /**
     * Handles the error that occurs during the execution of the function.
     *
     * @param string $modelName The name of the model being processed.
     * @param mixed $obModel The object representing the model.
     * @throws Exception When the save operation fails.
     */
    protected function handleError(string $modelName, $obModel)
    {
        throw new Exception("$modelName save failed: " . print_r($obModel->errors, true));
    }
}