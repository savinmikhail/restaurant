<?php

namespace app\controllers\api\user_app;

use app\models\tables\Basket;
use Yii;
use app\controllers\api\ApiController;
use app\models\tables\Table;

class OrderableController extends ApiController
{
    protected $basket = null;

    public function getUser()
    {
        return Yii::$app->user->identity;
    }

    protected function getBasketItems()
    {
        $obTable= Table::getTable();

        $filter = [
            'table_id' => $obTable->id,
        ];
        $result = $this->getBasketItemsByFilter($filter);
        $total = 0;
        if ($result && isset($result['items'][0]['basket_id'])) {
            $obBasket = Basket::find()->where(['id' => $result['items'][0]['basket_id']])->one();
            $total = $obBasket ? $obBasket->basket_total : 0;
        }
        return [$result, $total];
    }

    private function getBasketItemsByFilter($arFilter)
    {
        return Basket::find()
            ->joinWith('items')
            ->joinWith('items.product')
            ->joinWith('items.product.categories')
            ->joinWith('items.product.productSizePrices.price')
            ->joinWith('items.product.productSizePrices.size')

            ->where($arFilter)->cache(false)->asArray()->one();
    }

    protected function getBasketItemsFromOrder($orderId)
    {
        $result = $this->getBasketItemsByFilter(['order_id' => $orderId]);
        $total = 0;
        if (isset($result['items'])) {
            foreach ($result['items'] as &$item) {
                $item['price'] = null;
            }
            unset($item);
        }

        return [$result, $total];
    }

    protected function getBasket(): Basket
    {
        $obTable = Table::getTable();
        if(!$obTable){
            die(json_encode(['success' => false, 'data' => 'Unable to define table']));
        }
        if (!$this->basket) {
            $obBasket = Basket::find()->where(['table_id' => $obTable->id])->one();
            if (!$obBasket) {
                $obBasket = new Basket();
                $obBasket->table_id = $obTable->id;
                $obBasket->created_at = date('Y-m-d H:i:s');
                $obBasket->updated_at = date('Y-m-d H:i:s');
                if(!$obBasket->save()){
                    throw new \Exception("Failed to save Basket: " . print_r($obBasket->errors, true));
                }
            }
            $this->basket = $obBasket;
        }
        return $this->basket;
    }
}
