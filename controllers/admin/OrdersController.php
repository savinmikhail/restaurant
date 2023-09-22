<?php

namespace app\controllers\admin;

use app\models\forms\AdminOrderForm;
use app\models\tables\Order;
use Yii;
use yii\data\Pagination;
use \app\controllers\AdminController;
use app\models\tables\BasketItem;
use app\models\tables\Products;
use app\models\tables\Size;
use yii\web\NotFoundHttpException;

class OrdersController extends AdminController
{
    public function actionIndex()
    {
        $request = Yii::$app->request;

        $orders = Order::find()->joinWith('table');

        if (!empty($request->post('id'))) {
            $orders->andWhere('orders.id=:id', [':id' => $request->post('id')]);
        }

        if (!empty($request->post('external_id'))) {
            $orders->andWhere('orders.external_id LIKE :external_id', [':external_id' => '%' . $request->post('external_id') . '%']);
        }

        $countOrders = clone $orders;
        $pages = new Pagination(['totalCount' => $countOrders->count(), 'pageSize' => 50]);
        $orders = $orders->offset($pages->offset)->limit($pages->limit)->addOrderBy(['created_at' => SORT_DESC])->all();

        return $this->render('/admin/orders/list', [
            'orders' => $orders,
            'pages' => $pages,
            'filter' => $request->post()
        ]);
    }

    public function actionContent()
    {
        $id = intval($this->getReqParam('id'));
        $order = Order::find()
            ->joinWith('table')
            ->joinWith('basket')
            ->joinWith('basket.items')
            // ->joinWith('basket.items.modifiers.modifier.product')
            ->joinWith('basket.items.product')
            ->where(['orders.id' => $id])
            ->asArray()->one();

        if (!$order) {
            Yii::$app->response->setStatusCode(404);
            return 'The requested page does not exist.';
        }
        $view = new yii\web\View();
        $view->title = 'Заказ';
        return $this->render('/admin/orders/content', [
            'order' => $order,
        ]);
    }

    public function actionEdit()
    {
        $id = intval($this->getReqParam('id'));
        $obOrder = Order::find()->where(['id' => $id])->one();
        if (!$obOrder) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $this->editObject($obOrder);
    }

    public function editObject(Order $obOrder)
    {
        $result = false;
        $arOrder = Order::find()
            ->joinWith('table')
            ->joinWith('basket')
            ->joinWith('basket.items')
            // ->joinWith('basket.items.modifiers.modifier.product')
            ->joinWith('basket.items.product')
            ->where(['orders.id' => $obOrder->id])
            ->asArray()->one();

        $obOrderForm = new AdminOrderForm();
        if ($this->request->isPost) {
            $obOrderForm->load($this->request->post(), 'AdminOrderForm');
            if ($obOrderForm->validate()) {
                $result = true;
                $redirect = true;

                $formAttributes = $obOrderForm->getAttributes();
                ($this->editItems($obOrderForm, $obOrder));

                $obOrder->attributes = $formAttributes;
                $result = $obOrder->save();
                if ($result && $redirect) {
                    return Yii::$app->response->redirect(['/admin/orders/edit', 'id' => $obOrder->id, 'success' => true]);
                }
                if (!$result) {
                    throw new \Exception("Failed to save order: " . print_r($obOrder->errors, true));
                }
                $obOrderForm->load($obOrder->attributes, '');
            } else {
                throw new \Exception("Failed to validate form: " . print_r($obOrderForm->errors, true));
            }
        } else {
            $obOrderForm->load($arOrder, '');
        }

        list($arStatus, $arPayments, $arAllProducts, $arAllSizes) = $this->getVariablesForRender();


        return $this->render('/admin/orders/edit', [
            'model' => $obOrderForm,
            'id' => $obOrder->id,
            "statuses" => $arStatus,
            'selectedStatus' => $obOrder->status,
            'payments' => $arPayments,
            'selectedPayment' => $obOrder->payment_method,
            'basketItems' => $arOrder['basket']['items'],
            'availableProducts' => $arAllProducts,
            'availableSizes' => $arAllSizes,
            'success' => ($this->request->isPost ? $result : (($this->getReqParam('success')) ? true : false)),
        ]);
    }

    private function editItems(AdminOrderForm $form, Order $obOrder)
    {
        $this->editQuantities($form, $obOrder);
        $this->editIds($form);
        $this->editSize($form);
    }

    private function editQuantities(AdminOrderForm $form, Order $obOrder)
    {
        foreach ($form->product_quantity as $itemId => $quantity) {
            //добавление новго элемента
            if ($itemId === 0) {
                if ($quantity !== '' && $quantity !== '0') {
                    $obItem = new BasketItem; //TODO: находить айтем с таким же продуктом и добавлять ему quantity
                    $obItem->basket_id = $obOrder->basket_id;
                    $obItem->product_id = $form->product_id[0];
                    $obItem->quantity = $quantity;
                    $obItem->size_id = $form->product_size[0]; 
                    $obItem->price = 200; //MAGIC VALUE
                    if (!$obItem->save()) {
                        throw new \Exception("Failed to update item quantity" . print_r($obItem->errors, true));
                    }
                }
                //редактирование старого
            } else {
                $obItem = BasketItem::find()->where(['id' => $itemId])->one();
                if ($obItem) {
                    if ($quantity === '0') {
                        ($obItem->delete());
                    } else {
                        $obItem->quantity = $quantity;
                        if (!$obItem->save()) {
                            throw new \Exception("Failed to update item quantity" . print_r($obItem->errors, true));
                        }
                    }
                }
            }
        }
    }

    private function editIds(AdminOrderForm $form)
    {
        foreach ($form->product_id as $itemId => $product_id) {
            if ($itemId !== 0) {
                $obItem = BasketItem::find()->where(['id' => $itemId])->one();
                if ($obItem) {
                    $obItem->product_id = $product_id;

                    if (!$obItem->save()) {
                        throw new \Exception("Failed to update item product" . print_r($obItem->errors, true));
                    }
                }
            }
            //если itemId равен 0, то он был уже сохранен методом editQuantities()
        }
    }

    private function editSize(AdminOrderForm $form)
    {
        foreach ($form->product_size as $itemId => $size_id) { //TODO: доставать объекты в родителе и передавать на изменение, в 3 раза меньше обращений в бд
            if ($itemId !== 0) {
                $obItem = BasketItem::find()->where(['id' => $itemId])->one();
                if ($obItem) {
                    $obItem->size_id = $size_id;
                    if (!$obItem->save()) {
                        throw new \Exception("Failed to update item size " . print_r($obItem->errors, true));
                    }
                }
            }
            //если itemId равен 0, то он был уже сохранен методом editQuantities()

        }
    }

    private function getVariablesForRender()
    {
        $arStatus = array_flip([
            'Новый' => Order::STATUS_NEW,
            'Ошибка' => Order::STATUS_ERROR,
            'Отменен' => Order::STATUS_CANCELED,
            'Создан' => Order::STATUS_CREATED,
            'В обработке' => Order::STATUS_INPROCESS,
            'В пути' => Order::STATUS_INPATH,
            'Завершен' => Order::STATUS_DONE,
        ]);

        $arPayments = [
            'Cash' => 'Наличные',
            'IikoCard' => 'Карта Айко',
            'Card' => 'Карта',
            'External' => 'Внешняя система'
        ];

        $products = Products::find()
            /*->where(['is_deleted' => 0])*/
            // ->joinWith('productSizePrices.price')
            ->asArray()
            ->all();

        $arAllSizes = [];
        $sizes = Size::find()->asArray()->all();
        foreach ($sizes as $size) {
            $arAllSizes[$size['id']] = $size['name'];
        }
        //TODO: нужен джаваскрипт, чтоб для каждого продукта отображались только доступные ему размеры 
        // foreach ($products as $product) {
        //     foreach ($product['productSizePrices'] as $sizePrice) {
        //         if ($sizePrice['size_id']) {
        //             $obSize = Size::find()->where(['id' => $sizePrice['size_id']])->one();
        //             $arAllSizes[$product['id']][$obSize->id] = $obSize->name;
        //         }
        //     }
        // }

        $arAllProducts = [];
        foreach ($products as $product) {
            $arAllProducts[$product['id']] = $product['name'];
        }

        return [$arStatus, $arPayments, $arAllProducts, $arAllSizes];
    }
}
