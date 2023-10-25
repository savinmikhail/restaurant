<?php

namespace app\controllers\api;

use app\controllers\api\OrderableController;
use app\models\forms\OrderForm;
use app\models\tables\Basket;
use app\models\tables\Order;
use app\models\tables\Setting;
use app\models\tables\Table;
use app\Services\Payment;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
// use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;

class OrderController extends OrderableController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::class,
            'actions' => [
                'index'  => ['POST'],
                'renderQR'  => ['GET'],
                'pay' => ['POST'],
                'cancel' => ['POST'],
                'list' => ['GET'],
            ],
        ];

        return $behaviors;
    }
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
     *     @SWG\Response(
     *         response = 200,
     *         description = "Результат добавления заказа",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionIndex()
    {
        $tableId = Table::getTable()->id;
        $basket = Basket::find()->where(['table_id' => $tableId])->one();

        $order = new Order();
        $result = $order->make(['basket_id' => $basket->id, 'table_id' => $tableId, 'basket_total' => $basket->basket_total]);

        if ($result === true) {
            $this->sendResponse(201, ['data' => $order]);

        }elseif(is_array($result)){
            $this->sendResponse(400, ['data' => $result]);
        }

        $this->sendResponse(400, ['data' => $order->errors]);
    }

    /**
     * @SWG\Post(path="/api/order/pay",
     *     tags={"Order"},
     *     @SWG\Parameter(
     *          name="paymentMethod",
     *          in="formData",
     *          type="string",
     *          description="метод оплаты"
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Ссылка на оплату \ вызов официанта",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionPay()
    {
        $request = \Yii::$app->request;
        $paymentMethod = $request->post('paymentMethod');
        $orderId = $request->post('orderId');
        
        $order = Order::find()->where(['id' => $orderId])->one();

        if ($order) {
            $order->payment_method = $paymentMethod;
            if (!$order->save()) {
                return $this->sendResponse(400, ['data' => $order->errors]);
            }
            return $this->finalAction($order, $paymentMethod);
        }
    }

    /** @SWG\Get(path="/api/order/render-q-r",
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
        $writer = new SvgWriter();
        // $writer = new PngWriter();

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
        // $writer->validateResult($result, $data);

        header('Content-Type: ' . $result->getMimeType());

        // Save it to a file
        // $result->saveToFile('web/upload/QRs/qrcode.png');

        // Generate a data URI to include image data inline (i.e. inside an <img> tag)
        $dataUri = $result->getDataUri();
        echo $result->getString();
    }

    private function finalAction($order, $paymentMethod)
    {
        $obSetting = Setting::find()->where(['name' => 'order_limit'])->one();

        if ($obSetting && $order->order_sum > $obSetting->value) {
            return $this->asJson(['success' => true, 'data' => 'You need to call the waiter for further processing']);
        }

        if ($paymentMethod === 'Cash') {
            return $this->asJson(['success' => true, 'data' => 'You need to call the waiter for further processing']);
        }

        $result = [
            'order_id' => $order->id
        ];

        if ($paymentMethod !== 'Cash') {
            list($status, $result['paymentUrl'], $result['bankUrl']) = Payment::createSberPaymentUrl($order->id, $order->order_sum);
        }

        $this->sendResponse(200, $result);
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
        $order->updated_at = time();
        $order->save();
        if ($order->external_id) {
            // $res = $client->cancelOrder($order);
        }

        return $this->asJson(['result' => true, 'errors' => $order->errors]);
    }

    /**
     * Retrieves a list of orders.
     *
     * @SWG\Get(path="/api/order/list",
     *     tags={"Order"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "Содержимое корзины",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionList()
    {
        $obTable = Table::getTable();
        $filter = ['orders.table_id' => $obTable->id];

        $query = Order::find()
            ->where($filter)
            ->joinWith('basket')
            ->joinWith('basket.items')
            ->joinWith('basket.items.product')
            ->joinWith('basket.items.size')
            ->andWhere(['canceled' => 0])
            ->addGroupBy('orders.id')
            ->addOrderBy(['orders.id' => SORT_DESC]);

        $ordersList = $query->asArray()->all();


        // Initialize an empty array to hold the output
        $output = [];

        // Iterate through the orders list
        foreach ($ordersList as $order) {
            // Initialize an empty array to hold the items
            $items = [];

            // Iterate through the items in the order's basket
            foreach ($order['basket']['items'] as $item) {
                // Append a new item with the desired structure to the items array
                $items[] = [
                    'productId' => $item['product_id'],
                    'image' => $item['product']['image'],
                    'name' => $item['product']['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'size' => $item['size']['name'],  // Assuming size name is available
                    'sizeId' => $item['size_id']
                ];
            }

            // Append a new order with the desired structure to the output array
            $output[] = [
                'id' => $order['id'],
                'price' => $order['basket']['basket_total'],
                'isPaid' => $order['paid'] ? true : false,
                'list' => $items
            ];
        }

        // Output the result as JSON
        return $this->asJson($output);
    }
}
