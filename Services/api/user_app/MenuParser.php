<?php

namespace app\Services\api\user_app;

use app\models\tables\Price;
use app\models\tables\ProductsImages;
use app\models\tables\SizePrice;
use app\Services\api\user_app\import_helpers\CategoryHelper;
use app\Services\api\user_app\import_helpers\GroupHelper;
use app\Services\api\user_app\import_helpers\ProductHelper;
use app\Services\api\user_app\import_helpers\SizeHelper;
use Exception;

class MenuParser
{
    private array $helpers;
    public function __construct(GroupHelper $groupHelper, CategoryHelper $categoryHelper, ProductHelper $productHelper, SizeHelper $sizeHelper)
    {
        $this->helpers = [
            $groupHelper,
            $categoryHelper,
            $productHelper,
            $sizeHelper,
        ];
    }

    public function parse(array $data)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            Price::deleteAll();
            SizePrice::deleteAll();
            ProductsImages::deleteAll();

            foreach ($this->helpers as $helper) {
                $helper->process($data);
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

}
