<?php

namespace app\controllers\api;

use app\controllers\api\OrderableController;
use app\models\OrderForm;
use app\models\tables\Order;
use yii\filters\AccessControl;

class OrderController extends OrderableController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['index',  'list', 'view'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'list', 'view'],
                        'roles' => ['@'],
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    return $this->asJson(['result' => false, 'errors' => ['user' => ['message' => 'authorization required', 'code' => '401']]]);
                },
            ],
        ];
    }

    /**
     * @SWG\Post(path="/api/order",
     *     tags={"Order"},
     *      @SWG\Parameter(
     *      name="address_id",
     *      in="formData",
     *      type="string",
     *      description="Ид адреса"
     *      ),
     *      @SWG\Parameter(
     *      name="basket",
     *      in="formData",
     *      type="string",
     *      description="Ид корзины"
     *      ),
     *      @SWG\Parameter(
     *      name="return_bottles",
     *      in="formData",
     *      type="string",
     *      description="Количество бутылей к возврату"
     *      ),
     *      @SWG\Parameter(
     *      name="bonus_remove",
     *      in="formData",
     *      type="string",
     *      description="количество бонусов на списание"
     *      ),
     *      @SWG\Parameter(
     *      name="bonus_add",
     *      in="formData",
     *      type="string",
     *      description="количество бонусов на начисление"
     *      ),
     *      @SWG\Parameter(
     *      name="period",
     *      in="formData",
     *      type="string",
     *      description="выбранный интервал. ассоциативный массив как я его шлю"
     *      ),
     *      @SWG\Parameter(
     *      name="period_comment",
     *      in="formData",
     *      type="string",
     *      description="комментарий к интервалу"
     *      ),
     *      @SWG\Parameter(
     *      name="payment_method",
     *      in="formData",
     *      type="string",
     *      description="метод оплаты справочник"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Результат добавления заказа",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionIndex()
    {

        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        $orderForm = new OrderForm();
        $orderForm->load($request->post(), '');
        if (!$orderForm->validate()) {
            return $this->asJson(['success' => false, 'errors' => $orderForm->errors]);
        }
        $order = new Order();
        if ($order->make($orderForm->toArray())) {
            if (isset($order->id) && $order->id) {
                return $this->finalAction($order,$request);
            } 
        }
        $result = ['success' => false, 'errors' => $order->errors];
        return $this->asJson($result);
    }

  


    /**
     * @SWG\Post(path="/api/order/fastcopy",
     *     tags={"Order"},
     *      @SWG\Parameter(
     *      name="id",
     *      in="formData",
     *      type="string",
     *      description="Ид заказа"
     *      ),
     *      @SWG\Parameter(
     *      name="address",
     *      in="formData",
     *      type="string",
     *      description="Ид адреса"
     *      ),
     *     description="Копирование заказа (быстрое)",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionFastcopy()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        $quantities = null;
        if ($request->post('products')) {
            $quantities = $this->prepareQuantitites($request->post('products'));
        }
        $sourceOrder = Order::find()->where(['id' => $request->post('id')])->one();
        list($result, $total) =  $this->getBasketItemsFromOrder($sourceOrder->id,$request->post('address'),$quantities,$request->post('is_express'));
        if ($request->post('save')) {
            $order = new Order();
            list ($success,$errors) = $order->makeFast(
                $result,
                $total,
                intval($request->post('address')),
                $request->post('is_express'),
                $request->post('return_bottles'),
                $request->post('bonus_add'),
                $request->post('bonus_remove'),
                $request->post('payment_method'),
                $request->post('period'),
                $request->post('period_comment'),
                $request->post('pledge_price')
            );
            
            if ($success) {
                return $this->finalAction($order,$request);
                
            } else {
                return $this->asJson(['success' => false, 'errors' => $errors]);
            }
    
        }
        return $this->asJson(['list' => $result, 'express_cost' => \app\controllers\api\BasketController::expressCost, 'pledge_price' => \app\controllers\api\BasketController::pledgePrice, 'count' => count($result['items']), 'total' => $total, 'discount' => 0, 'time_intervals' => $this->getBasket()->getTimeIntervals(intval($request->post('address')),$result['hasCoolers'])]);
    
    }

    private function finalAction($order,$request)
    {
      
        if ($request->post('payment_method') == 'cash') {
            $obQueue = new \app\models\tables\Queue();
            $obQueue->order_id = $order->id;
            $obQueue->retries = 0;
            $obQueue->save();
          
        }

        $result = ['success' => true, 'order_id' => $order->id];
        if ($request->post('payment_method') != 'cash') {
            list($status,$result['paymentUrl'],$result['bankUrl']) = $this->createPaymentUrl($order->id, $order->order_sum);
        }
        return $this->asJson($result);
    }

    private function prepareQuantitites($json)
    {
        $result = [];
        $q = json_decode($json,1);
        foreach ($q as $item) {
            $result[$item['id']] = intval($item['quantity']);
        }
        return $result;
    }

    /**
     * @SWG\Post(path="/api/order/copy",
     *     tags={"Order"},
     *      @SWG\Parameter(
     *      name="id",
     *      in="formData",
     *      type="string",
     *      description="Ид заказа"
     *      ),
     *     description="Копирование заказа (содержимого корзины)",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionCopy()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        $sourceOrder = Order::find()->where(['id' => $request->post('id')])->one();
        $sourceOrderBasket = $sourceOrder->getBasket()->one();
        $this->getBasket()->clear();

        foreach ($sourceOrderBasket->getItems()->all() as $obBasketItem) {
            $this->getBasket()->addItem($obBasketItem->product_id, $obBasketItem->quantity);
        }
        $this->getBasket()->setBasketExpressStatus(intval($sourceOrderBasket->is_express));
        list($result, $total) = $this->getBasketItems($sourceOrder->address_id);

        return $this->asJson(['result' => true, 'address' => $sourceOrder->address_id, 'basket' => $result, 'total' => $total]);
    }

    /**
     * @SWG\Post(path="/api/order/cancel",
     *     tags={"Order"},
     *      @SWG\Parameter(
     *      name="id",
     *      in="formData",
     *      type="string",
     *      description="Ид заказа"
     *      ),
     *     description="Отмена заказа",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionCancel()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        $order = Order::find()->where(['id' => $request->post('id')])->one();
        $order->canceled = 1;
        $order->period_comment = (string) $order->period_comment;
        $order->updated = date('Y-m-d H:i:s');
        $order->save();
        $order->updateBonusBalance('plus');
        if ($order->external_id) {
            $client = new \app\common\ErpClient();
            $res = $client->cancelOrder($order);
        }

        return $this->asJson(['result' => true, 'errors' => $order->errors]);
    }

    /**
     * @SWG\POST(path="/api/order/list",
     *     tags={"Order"},
     *      @SWG\Parameter(
     *      name="address_id",
     *      in="formData",
     *      type="string",
     *      description="ид адреса доставки"
     *      ),
     *      @SWG\Parameter(
     *      name="limit",
     *      in="formData",
     *      type="string",
     *      description="число на страницу"
     *      ),
     *      @SWG\Parameter(
     *      name="from",
     *      in="formData",
     *      type="string",
     *      description="количество записей (не страниц) которые пропускаем"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionList()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        $filter = ['orders.user_id' => $this->getUser()->getId()];
        if ($request->post('address_id')) {
            $filter['orders.address_id'] = $request->post('address_id');
        }

        if ($request->isPost) {
            $from = (($request->post('from'))) ? intval($request->post('from')) : 0;
            $limit = (($request->post('limit'))) ? intval($request->post('limit')) : 20;
        }

        $orders = Order::find()->where($filter)->joinWith('address')->joinWith('expeditor')->joinWith('basket')->joinWith('basket.items')->joinWith('basket.items.product')
        ->andWhere(['canceled' => 0])
        ->andWhere(['in', 'orders.status', [\app\models\tables\Order::STATUS_CREATED, \app\models\tables\Order::STATUS_INPROCESS, \app\models\tables\Order::STATUS_INPATH]])
        ->addGroupBy('orders.id')
        ->addOrderBy(['orders.id' => SORT_DESC]);
        $countOrders = clone $orders;
        $orders->offset($from);
        $orders->limit($limit);
        $ordersList = $orders->asArray()->all();
        $count = $countOrders->count();

        return $this->asJson(['success' => true, 'list' => $ordersList, 'total' => $count]);
    }


    /**
     * @SWG\POST(path="/api/order/last",
     *     tags={"Order"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionLast()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        if (!$this->getUser()) {
            return $this->asJson(['success' => true, 'list' => []]);
        }
        $filter = [
                    'orders.user_id' => $this->getUser()->getId(),
                   
                ];
        $ordersList = Order::find()->where($filter)->limit(1)->addOrderBy(['orders.id' => SORT_DESC])->asArray()->all();
        return $this->asJson(['success' => true, 'list' => $ordersList]);
    }


    /**
     * @SWG\POST(path="/api/order/archive",
     *     tags={"Order"},
     *      @SWG\Parameter(
     *      name="limit",
     *      in="formData",
     *      type="string",
     *      description="число на страницу"
     *      ),
     *      @SWG\Parameter(
     *      name="from",
     *      in="formData",
     *      type="string",
     *      description="количество записей (не страниц) которые пропускаем"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionArchive()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        $filter = ['orders.user_id' => $this->getUser()->getId()];
        if ($request->post('address_id')) {
            $filter['orders.address_id'] = $request->post('address_id');
        }

        if ($request->isPost) {
            $from = (($request->post('from'))) ? intval($request->post('from')) : 0;
            $limit = (($request->post('limit'))) ? intval($request->post('limit')) : 20;
        }

        $orders = Order::find()->where($filter)->joinWith('address')->joinWith('expeditor')->joinWith('basket')->joinWith('basket.items')->joinWith('basket.items.product')

       ->andWhere(['or',
           ['canceled' => 1],
           ['in', 'orders.status', [\app\models\tables\Order::STATUS_DONE, \app\models\tables\Order::STATUS_NEW]],
       ])
        ->addGroupBy('orders.id')
        ->addOrderBy(['orders.id' => SORT_DESC]);
        $countOrders = clone $orders;
        $orders->offset($from);
        $orders->limit($limit);
        $ordersList = $orders->asArray()->all();
        $count = $countOrders->count();

        return $this->asJson(['success' => true, 'list' => $ordersList, 'total' => $count]);
    }

    /**
     * @SWG\POST(path="/api/order/view",
     *     tags={"Order"},
     *      @SWG\Parameter(
     *      name="id",
     *      in="formData",
     *      type="string",
     *      description="ид заказа"
     *      ),
     *     description = "Получение заказа по его ID",
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/AddressList")
     *     ),
     * )
     */
    public function actionView()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        $filter = ['orders.user_id' => $this->getUser()->getId()];
        if ($request->post('id')) {
            $filter['orders.id'] = $request->post('id');
        }
        $orders = Order::find()->where($filter)->joinWith('basket')->joinWith('basket.items')->joinWith('basket.items.product')->asArray()->one();

        return $this->asJson(['success' => true, 'list' => $orders]);
    }
}
