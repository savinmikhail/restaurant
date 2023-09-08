<?php

namespace app\controllers\admin;

use app\models\Order;
use Yii;
use yii\data\Pagination;
use \app\controllers\AdminController;

class OrdersController extends AdminController
{
    public function actionIndex()
    {
        $request = Yii::$app->request;


        $orders = Order::find()
            ->joinWith('user');
        if (!empty($request->post('id'))) {
            $orders->andWhere('orders.id=:id', [':id' => $request->post('id')]);
        }

        if (!empty($request->post('external_id'))) {
            $orders->andWhere('orders.external_id LIKE :external_id', [':external_id' => '%'.$request->post('external_id').'%']);
        }

        if (!empty($request->post('phone'))) {
            $orders->andWhere('user.user_phone LIKE :phone', [':phone' => '%' . $request->post('phone') . '%']);
        }

        $countOrders = clone $orders;
        $pages = new Pagination(['totalCount' => $countOrders->count(), 'pageSize' => 50]);
        $orders = $orders->offset($pages->offset)->limit($pages->limit)->addOrderBy(['created' => SORT_DESC])->all();

        return $this->render('/admin/orders/list', [
            'orders' => $orders,
            'pages' => $pages,
            'filter'=>$request->post()
        ]);
    }

    public function actionContent()
    {
        $id = intval($this->getReqParam('id'));
        $order = Order::find()->joinWith('user')->joinWith('address')->joinWith('basket')->joinWith('basket.items')->joinWith('basket.items.product')->where(['orders.id'=>$id])->asArray()->one();
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
}