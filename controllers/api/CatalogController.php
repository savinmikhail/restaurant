<?php

namespace app\controllers\api;

use app\controllers\ApiController;
use app\models\Categories;
use app\models\Products;

class CatalogController extends ApiController
{
    private $sort = [
        [
            'name' => 'По популярности',
            'code' => 'popular',
            'sorting' => ['name' => 'popular', 'direction' => 'desc'],
        ],
        [
            'name' => 'Сначала дешевле',
            'code' => 'price_asc',
            'sorting' => ['name' => 'price', 'direction' => 'asc'],
        ],
        [
            'name' => 'Сначала дороже',
            'code' => 'price_desc',
            'sorting' => ['name' => 'price', 'direction' => 'desc'],
        ],
    ];

    /**
     * @SWG\Get(path="/api/catalog/categories",
     *     tags={"Catalog"},
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/Categories")
     *     ),
     * )
     */
    public function actionCategories()
    {
        $categories = Categories::find()->where(['active' => 1])->addOrderBy(['sort' => SORT_ASC])->all();
        if ($categories) {
            foreach ($categories as &$cat) {
                $cat['image'] = ($cat['image']) ? $cat['image'].'?t='.time() : '';
            }
        }

        return $this->asJson(['list' => $categories]);
    }

    /**
     * @SWG\Post(path="/api/catalog/filter",
     *     tags={"Catalog"},
     *      @SWG\Parameter(
     *      name="category",
     *      in="formData",
     *      type="string"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Products by filter",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionFilter()
    {
        $request = \Yii::$app->request;
        if (!$request->isPost || !$request->post('category')) {
            return $this->asJson(['filter' => []]);
        }

        $result = (new \yii\db\Query())->select('(`facet_index`.`property_value`) as prop_value,(`facet_index`.`is_white`) as prop_value_is_white,(`facet_index`.`hex_value`) as prop_value_hex, products_properties.*,facet_index.*')
        ->from('products_properties')
        ->join('JOIN `facet_index` ON ', '(`facet_index`.`category_id`='.$request->post('category').' AND `facet_index`.`property_id`=`products_properties`.`id`)')
        ->where(['in_filter' => 1])
        //->andWhere(['facet_index.category_id' => $request->post('category')])
        ->addGroupBy('facet_index.property_id')
        ->addGroupBy('facet_index.property_value')
        ->addOrderBy('`products_properties`.`sort` ASC, `prop_value` ASC')
        ->all();
        $arFilterResult = [];
        foreach ($result as $item) {
            if (!isset($arFilterResult[$item['property_id']])) {
                $arFilterResult[$item['property_id']] = [
                   'name' => $item['name'],
                   'code' => $item['property_id'],
                   'sort' => $item['sort'],
                   'values' => [
                    [
                        'value' => $item['prop_value'],
                        'is_white' => $item['prop_value_is_white'],
                        'hex' => $item['prop_value_hex'],
                    ],
                    ],
                ];
            } else {
                $arFilterResult[$item['property_id']]['values'][] = [
                    'value' => $item['prop_value'],
                    'is_white' => $item['prop_value_is_white'],
                    'hex' => $item['prop_value_hex'],
                ];
            }
        }
        $arFilterResult = array_values($arFilterResult);

        return $this->asJson(['filter' => $arFilterResult]);
    }

    /**
     * @SWG\Get(path="/api/catalog/popular",
     *     tags={"Catalog"},
     *      description="Популярные товары",
     *     @SWG\Response(
     *         response = 200,
     *         description = "User collection response",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionPopular()
    {
        $result = Products::find()
        ->where(['products.active' => 1, 'products.is_popular' => 1])
        ->andWhere(['>','products.quantity',0])
        ->joinWith([
            'productProperties',
            'productProperties.property',
        ])->joinWith([
            'categories',
        ])
        ->addGroupBy('products.id')
        ->addOrderBy(['sort' => SORT_ASC]);
        $list = $result->asArray()->all();
        $count = $result->count();

        return $this->asJson(['list' => $list, 'count' => $count]);
    }



    /**
     * @SWG\Post(path="/api/catalog/products",
     *     tags={"Catalog"},
     *      @SWG\Parameter(
     *      name="address_id",
     *      in="formData",
     *      type="string"
     *      ),
     *      @SWG\Parameter(
     *      name="from",
     *      in="formData",
     *      type="string"
     *      ),
     *      @SWG\Parameter(
     *      name="limit",
     *      in="formData",
     *      type="string"
     *      ),
     *      @SWG\Parameter(
     *      name="category",
     *      in="formData",
     *      type="string"
     *      ),
     *      @SWG\Parameter(
     *      name="sort",
     *      in="formData",
     *      type="string"
     *      ),
     *      @SWG\Parameter(
     *      name="filter[{prop_id}][]",
     *      in="formData",
     *      type="string"
     *      ),
     *      @SWG\Parameter(
     *      name="filter[1][]",
     *      in="formData",
     *      type="string"
     *      ),
     *      @SWG\Parameter(
     *      name="filter[1][]",
     *      in="formData",
     *      type="string"
     *      ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "Products by filter",
     *         @SWG\Schema(ref = "#/definitions/Products")
     *     ),
     * )
     */
    public function actionProducts()
    {
        $request = \Yii::$app->request;
        if ($request->isPost) {
            $from = (($request->post('from'))) ? intval($request->post('from')) : 0;
            $limit = (($request->post('limit'))) ? intval($request->post('limit')) : 20;
            list($count, $list) = $this->getProductsByFilter($request->post(), $from, $limit,$request->post('sort'));
        } else {
            $from = (($request->post('from'))) ? intval($request->post('from')) : 0;
            $limit = (($request->post('limit'))) ? intval($request->post('limit')) : 2000;
            $result = [];
            $result = Products::find()
            ->where(['products.active' => 1])
            ->joinWith([
                'productProperties',
                'productProperties.property',
            ])->joinWith([
                'categories',
            ])
            ->addGroupBy('products.id')
            ->addOrderBy(['sort' => SORT_ASC]);
            $list = $result->asArray()->all();
            $count = $result->count();
        }

        $list = $this->prepareCatalogData($list);

        return $this->asJson(['list' => $list, 'count' => $count, 'from' => $from, 'limit' => $limit, 'sorting' => $this->getSorting()]);
    }

    private function prepareCatalogData($list)
    {
        //TODO: вывести дисконтную цену для продуктов если они нужны
        foreach ($list as $k => &$product) {
            if (!$product['base_price_yurga']) {

                $product['base_price_yurga'] =  $product['base_price'];
            }
        }

        return $list;
    }

    private function getSorting()
    {
        $result = [];
        foreach ($this->sort as $sort) {
            $result[] = ['code' => $sort['code'], 'name' => $sort['name']];
        }

        return $result;
    }

    private function getProductsByFilter($filter, $from = 0, $limit = 20,$sort='sort')
    {
        $result = [];
        $result = Products::find()
            ->where(['products.active' => 1])
            ->andWhere(['>','products.quantity',"0"])
            ->joinWith([
                'productImages',
                'productProperties',
                'productProperties.property',
                'productProperties.property as pp',
            ])->joinWith([
                'categories',
            ])
            ->addGroupBy('products.id');
            if (isset($filter['filter'])) {
                foreach ($filter['filter'] as $prop_id => $prop_values) {
                    $result->join(' JOIN `products_properties_values` p'.$prop_id.' ON ', 'p'.$prop_id.'.`product_id`=`products`.`id`');
                }
            }
            
            if ($sort == 'price_asc') {
                $result->addOrderBy(['products.base_price' => SORT_ASC]);
            }elseif ($sort == 'price_desc') {
                $result->addOrderBy(['products.base_price' => SORT_DESC]);
            } else {
                $result->addOrderBy(['products.sort' => SORT_ASC]);
            }

        if (isset($filter['category']) && $filter['category']) {
            $result->andWhere(['products.category_id' => intval($filter['category'])]);
        }
        if (isset($filter['address_id']) && $filter['address_id']) {
        }
        if (isset($filter['filter'])) {
            foreach ($filter['filter'] as $prop_id => $prop_values) {
                $result->andWhere(['p'.$prop_id.'.property_id' => $prop_id]);
                if (is_array($prop_values)) {
                    $result->andWhere(['in','p'.$prop_id.'.value', $prop_values]);
                } else {
                    $result->andWhere(['p'.$prop_id.'.value' => $prop_values]);
                }
            }
        }
            /*
            $result->andWhere(['products_properties_values.value' => '2А']);
            $result->andWhere(['products_properties_values.property_id' => 1]);
        */
        $count = $result->count();

        $result->offset($from);
        $result->limit($limit);

        return [$count, $result->asArray()->all()];
    }
}
