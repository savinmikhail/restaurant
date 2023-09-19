<?php

namespace app\controllers\api;

use app\controllers\api\OrderableController;
use app\models\forms\OrderForm;
use app\models\tables\Order;
use app\models\tables\Setting;
use app\models\tables\Table;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class OrderController extends OrderableController
{
    // public function behaviors()
    // {
    //     return [
    //         'access' => [
    //             'class' => AccessControl::class,
    //             'only' => ['index',  'list', 'view'],
    //             'rules' => [
    //                 [
    //                     'allow' => true,
    //                     'actions' => ['index', 'list', 'view'],
    //                     'roles' => ['@'],
    //                 ],
    //             ],
    //             'denyCallback' => function ($rule, $action) {
    //                 return $this->asJson(
    //                     [
    //                         'result' => false,
    //                         'errors' =>
    //                         [
    //                             'user' =>
    //                             [
    //                                 'message' => 'authorization required',
    //                                 'code' => '401'
    //                             ]
    //                         ]
    //                     ]
    //                 );
    //             },
    //         ],
    //     ];
    // }

    /**
     * @SWG\Post(path="/api/order",
     *     tags={"Order"},
     *      @SWG\Parameter(
     *      name="basket_id",
     *      in="formData",
     *      type="string",
     *      description="Ид корзины"
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
                return $this->finalAction($order, $request);
            }
        }
        $result = [
            'success' => false,
            'errors' => $order->errors
        ];
        return $this->asJson($result);
    }

    /** @SWG\Post(path="/api/order/render-q-r",
     *     tags={"Order"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "QR picture",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionRenderQR()
    {
        $data = Table::getTable()->id;
        $writer = new PngWriter();

        // Create QR code
        $qrCode = QrCode::create($data)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        $result = $writer->write($qrCode);

        // Validate the result
        $writer->validateResult($result, $data);

        header('Content-Type: ' . $result->getMimeType());

        // Save it to a file
        // $result->saveToFile('web/upload/QRs/qrcode.png');

        // Generate a data URI to include image data inline (i.e. inside an <img> tag)
        $dataUri = $result->getDataUri();
        echo $result->getString();
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
     *     description="Копирование заказа (быстрое)",
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */

    /* 
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
        list($result, $total) =  $this->getBasketItemsFromOrder($sourceOrder->id, $request->post('address'), $quantities, $request->post('is_express'));
        if ($request->post('save')) {
            $order = new Order();
            list($success, $errors) = $order->makeFast(
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
                return $this->finalAction($order, $request);
            } else {
                return $this->asJson(['success' => false, 'errors' => $errors]);
            }
        }
        return $this->asJson(
            [
                'list' => $result,
                'count' => count($result['items']),
                'total' => $total,
                'time_intervals' => $this->getBasket()->getTimeIntervals(intval($request->post('address')), $result['hasCoolers'])
            ]
        );
    }
*/
    private function finalAction($order, $request)
    {
        $obSetting = Setting::find()->where(['name' => 'order_limit'])->one();
        if ($obSetting && $order->order_sum > $obSetting->value) {
            return $this->asJson(['success' => true, 'data' => 'You need to call the waiter for further processing']);
        }
        if ($request->post('payment_method') === 'Cash') {
            $obQueue = new \app\models\tables\Queue();
            $obQueue->order_id = $order->id;
            $obQueue->retries = 0;
            $obQueue->save();
            return $this->asJson(['success' => true, 'data' => 'You need to call the waiter for further processing']);
        }

        $result = [
            'success' => true,
            'order_id' => $order->id
        ];
        if ($request->post('payment_method') !== 'cash') {
            list($status, $result['paymentUrl'], $result['bankUrl']) = $this->createPaymentUrl($order->id, $order->order_sum);
        }
        return $this->asJson($result);
    }

    private function createPaymentUrl($last_order_id, $amount)
    {
        return [
            1,
            'https://smth.smth',
            []
        ];
        /*   $returnUrl = "https://mp.kv.tomsk.ru/api/payment/redirect?orderId={$last_order_id}&success=true";
        $failUrl = "https://mp.kv.tomsk.ru/api/payment/redirect?orderId={$last_order_id}&success=false";
        $host = 'https://mp.kv.tomsk.ru/api/payment/callback';
        //$token = 'auaclh27bt0pejpl05pf138f0';
        $token = 'l08ccs6936bvhai03i5p2fu8fr';   //prod
        $amount = round($amount * 100, 0);
        $arrContextOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];
        //$url = 'https://3dsec.sberbank.ru/payment/rest/register.do'
        $url = 'https://securepayments.sberbank.ru/payment/rest/register.do'  //prod
        . "?amount={$amount}&orderNumber=M{$last_order_id}"
        . '&token=' . $token
            //    .'&dynamicCallbackUrl='.$host
            . '&pageView=MOBILE'
            . '&returnUrl=' . $returnUrl
            . '&failUrl=' . $failUrl;
        $response = json_decode(file_get_contents($url, false, stream_context_create($arrContextOptions)));

        if (isset($response->formUrl)) {
            return [
                1,
                $response->formUrl,
                []
            ];
        } else {
            return [
                0,
                [
                    'code' => $response->errorCode,
                    'message' => $response->errorMessage,
                ],
                ['url' => $url]
            ];
        }*/
    }
    private function prepareQuantitites($json)
    {
        $result = [];
        $q = json_decode($json, 1);
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
    // public function actionCopy()
    // {
    //     $request = \Yii::$app->request;
    //     if (!$request->isPost) {
    //         return $this->asJson(['error' => 'empty request']);
    //     }
    //     $sourceOrder = Order::find()->where(['id' => $request->post('id')])->one();
    //     $sourceOrderBasket = $sourceOrder->getBasket()->one();
    //     $this->getBasket()->clear();

    //     foreach ($sourceOrderBasket->getItems()->all() as $obBasketItem) {
    //         $this->getBasket()->addItem($obBasketItem->product_id, $obBasketItem->quantity);
    //     }
    //     $this->getBasket()->setBasketExpressStatus(intval($sourceOrderBasket->is_express));
    //     list($result, $total) = $this->getBasketItems($sourceOrder->address_id);

    //     return $this->asJson(['result' => true, 'address' => $sourceOrder->address_id, 'basket' => $result, 'total' => $total]);
    // }

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
        $order->updated_at = time();
        $order->save();
        if ($order->external_id) {
            // $client = new \app\common\ErpClient();
            // $res = $client->cancelOrder($order);
        }

        return $this->asJson(['result' => true, 'errors' => $order->errors]);
    }

    /**
     * @SWG\POST(path="/api/order/list",
     *     tags={"Order"},
     *      @SWG\Parameter(
     *      name="status",
     *      in="formData",
     *      type="string",
     *      description="Статусы заказа. Принимает значения new, created, in_process, in_path. По умолчанию фильтрую по всем статусам"
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
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionList()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        $obTable = Table::getTable();
        $filter = ['orders.table_id' => $obTable->id];
        $statuses = $request->post('status') ? [$request->post('status')] : [Order::STATUS_NEW, Order::STATUS_CREATED, Order::STATUS_INPROCESS, Order::STATUS_INPATH];
        if ($request->isPost) {
            $from = (($request->post('from'))) ? intval($request->post('from')) : 0;
            $limit = (($request->post('limit'))) ? intval($request->post('limit')) : 20;
        }

        $orders = Order::find()->where($filter)->joinWith('basket')->joinWith('basket.items')->joinWith('basket.items.product')
            ->andWhere(['canceled' => 0])
            ->andWhere(['in', 'orders.status', $statuses])
            ->addGroupBy('orders.id')
            ->addOrderBy(['orders.id' => SORT_DESC]);
        $ordersList = $orders->asArray()->all();

        $countOrders = clone $orders;
        $orders->offset($from);
        $orders->limit($limit);
        $count = $countOrders->count();

        return $this->asJson(['success' => true, 'list' => $ordersList, 'total' => $count]);
    }


    /**
     * @SWG\POST(path="/api/order/last",
     *     tags={"Order"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionLast()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }
        $table = Table::getTable();
        if (!$table) {
            return $this->asJson(['success' => false, 'list' => []]);
        }
        $filter = [
            'orders.table_id' => $table->id,
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
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    // public function actionArchive()
    // {
    //     $request = \Yii::$app->request;
    //     if (!$request->isPost) {
    //         return $this->asJson(['error' => 'empty request']);
    //     }
    //     $filter = ['orders.user_id' => $this->getUser()->getId()];
    //     if ($request->post('address_id')) {
    //         $filter['orders.address_id'] = $request->post('address_id');
    //     }

    //     if ($request->isPost) {
    //         $from = (($request->post('from'))) ? intval($request->post('from')) : 0;
    //         $limit = (($request->post('limit'))) ? intval($request->post('limit')) : 20;
    //     }

    //     $orders = Order::find()->where($filter)->joinWith('address')->joinWith('expeditor')->joinWith('basket')->joinWith('basket.items')->joinWith('basket.items.product')

    //         ->andWhere([
    //             'or',
    //             ['canceled' => 1],
    //             ['in', 'orders.status', [Order::STATUS_DONE, Order::STATUS_NEW]],
    //         ])
    //         ->addGroupBy('orders.id')
    //         ->addOrderBy(['orders.id' => SORT_DESC]);
    //     $countOrders = clone $orders;
    //     $orders->offset($from);
    //     $orders->limit($limit);
    //     $ordersList = $orders->asArray()->all();
    //     $count = $countOrders->count();

    //     return $this->asJson(['success' => true, 'list' => $ordersList, 'total' => $count]);
    // }

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
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionView()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost) {
            return $this->asJson(['error' => 'empty request']);
        }

        $table = Table::getTable();
        if (!$table) {
            return $this->asJson(['success' => false, 'list' => []]);
        }

        $filter = [
            'orders.table_id' => $table->id,
        ];
        if ($request->post('id')) {
            $filter['orders.id'] = $request->post('id');
        }
        $orders = Order::find()->where($filter)->joinWith('basket')->joinWith('basket.items')->joinWith('basket.items.product')->asArray()->one();

        return $this->asJson(['success' => true, 'list' => $orders]);
    }
}
