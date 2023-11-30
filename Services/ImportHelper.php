<?php

namespace app\Services;

use app\models\tables\Categories;
use app\models\tables\Group;
use app\models\tables\PaymentType;
use app\models\tables\Price;
use app\models\tables\Products;
use app\models\tables\ProductsImages;
use app\models\tables\ProductsProperties;
use app\models\tables\ProductsPropertiesValues;
use app\models\tables\Size;
use app\models\tables\SizePrice;
use app\models\tables\Table;
use Exception;

class ImportHelper
{
    public function parse(array $data)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            Price::deleteAll();
            SizePrice::deleteAll();
            ProductsImages::deleteAll();

            $this->processGroups($data['groups']);
            $this->processSizes($data['sizes']);
            $this->processCategories($data['productCategories']);
            $this->processProducts($data['products']);

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    private function processProducts(array $arProducts)
    {
        foreach ($arProducts as $arProduct) {
            $this->processProduct($arProduct);
        }
    }

    private function processCategories(array $arCategories)
    {
        foreach ($arCategories as $arCategory) {
            $this->processCategory($arCategory);
        }
    }

    private function processSizes(array $arSizes)
    {
        foreach ($arSizes as $arSize) {
            $this->processSize($arSize);
        }
    }

    private function processGroups(array $arGroups)
    {
        foreach ($arGroups as $arGroup) {
            $this->processGroup($arGroup);
        }
    }

    private function processGroup(array $arGroup)
    {
        $obGroup = Group::find()->where(['external_id' => $arGroup['id']])->one();
        if (!$obGroup) {
            $obGroup = new Group();
        }
        $obGroupValues = [
            'external_id' => $arGroup['id'],
            'name' => $arGroup['name'],
            'is_deleted' => $arGroup['isDeleted']
        ];
        $obGroup->load($obGroupValues, '');
        if (!$obGroup->save()) {
            $this->handleError('Group', $obGroup);
        }
    }

    private function processCategory(array $arCategory)
    {
        $obCategory = Categories::find()->where(['external_id' => $arCategory['id']])->one();
        if (!$obCategory) {
            $obCategory = new Categories();
        }
        $obCategoryValues = [
            'external_id' => $arCategory['id'],
            'name' => $arCategory['name'],
            'is_deleted' => $arCategory['isDeleted']
        ];
        $obCategory->load($obCategoryValues, '');
        if (!$obCategory->save()) {
            $this->handleError('Category', $obCategory);
        }
    }

    private static function handleError(string $modelName, $obModel)
    {
        throw new Exception("$modelName save failed: " . print_r($obModel->errors, true));
    }

    private function processProduct(array $arProduct)
    {
        $obProduct = Products::find()->where(['external_id' => $arProduct['id']])->one();
        if (!$obProduct) {
            $obProduct = new Products();
        }

        $image = $this->processProductImages($arProduct, $obProduct->id);

        $category = Categories::find()->where(['external_id' => $arProduct['productCategoryId']])->one();
        $obProductValues = [
            'image' => $image ?? '',
            'external_id' => $arProduct['id'],
            'name' => $arProduct['name'],
            'description' => $arProduct['description'] ?? '',
            'is_deleted' => $arProduct['isDeleted'],
            'sort' => $arProduct['order'],
            'category_id' => $category ? $category->id : null
        ];
        $obProduct->load($obProductValues, '');
        if (!$obProduct->save()) {
            $this->handleError('Product', $obProduct);
        }

        $this->processProductProps($arProduct, $obProduct->id);
        $this->processSizePrices($arProduct, $obProduct->id);
    }

    private function processProductImages(array $arProduct, int $productId)
    {
        if (isset($arProduct['imageLinks']) && !empty($arProduct['imageLinks'])) {
            $imageLinks = $arProduct['imageLinks'];
            $imagesLocalPaths = [];
            foreach ($imageLinks as $link) {
                $fileName = basename(parse_url($link, PHP_URL_PATH));
                $imagesDir = 'web/upload/productImages/';
                $storagePath = $imagesDir . $fileName;

                $obProductImage = new ProductsImages();
                $obProductImage->product_id = $productId;
                if ($this->downloadImage($link, $storagePath)) {
                    $imagesLocalPaths[] = $storagePath;

                    $obProductImage->image = $storagePath;
                } else {
                    $obProductImage->image = $link;
                }
                if (!$obProductImage->save()) {
                    $this->handleError('Product Image', $obProductImage);
                }
            }
            return !empty($imagesLocalPaths) ? $imagesLocalPaths[0] : null;
        }
        return null;
    }


    private function downloadImage($url, $storagePath): bool
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);

        return (bool) (file_put_contents($storagePath, $data));
    }

    private function processProductProps(array $arProduct, int $productId)
    {
        //исключаю все то, что уже лежит в products, или в отдельных таблицах, или является массивом (и подлежит лежанию в отдельной таблице)
        $excludeKeys = ['id', 'name', 'description', 'isDeleted', 'order', 'imageLinks', 'productCategoryId', 'sizePrices', 'modifiers', 'groupModifiers', 'tags'];
        $productProps = array_diff(array_keys($arProduct), $excludeKeys);
        foreach ($productProps as $productProp) {
            $obProductProps = ProductsProperties::find()->where(['code' => $productProp])->one();
            if (!$obProductProps) {
                $obProductProps = new ProductsProperties();
                $obProductProps->code = $productProp;
                if (!$obProductProps->save()) {
                    $this->handleError('ProductProps', $obProductProps);
                }
            }
            $this->processProductPropsVals($arProduct, $productId, $obProductProps);
        }
    }

    private function processProductPropsVals(array $arProduct, int $productId, ProductsProperties $obProductProps)
    {
        $obProductPropsVals = ProductsPropertiesValues::find()
            ->where([
                'product_id' => $productId,
                'property_id' => $obProductProps->id
            ])
            ->one();

        if (!$obProductPropsVals) {
            $obProductPropsVals = new ProductsPropertiesValues();
            $obProductPropsVals->product_id = $productId;
            $obProductPropsVals->property_id = $obProductProps->id;
        }
        $obProductPropsVals->value = (string) $arProduct[$obProductProps->code] ?? null;
        if (!$obProductPropsVals->save()) {
            $this->handleError('ProductPropsVals', $obProductPropsVals);
        }
    }

    private function processSizePrices(array $arProduct, int $productId)
    {
        foreach ($arProduct['sizePrices'] as $arSizePrice) {
            $obSizePrice = new SizePrice();
            $obSizePrice->size_id = null;
            $obSizePrice->product_id = $productId;

            if (!is_null($arSizePrice['sizeId'])) {
                $size = Size::find()->where(['external_id' => $arSizePrice['sizeId']])->one();
                $obSizePrice->size_id = $size->id;
            }

            if (!$obSizePrice->save()) {
                $this->handleError('SizePrice', $obSizePrice);
            }
            $this->processPrices($arSizePrice['price'], $obSizePrice->id);
        }
    }

    private function processPrices(array $arPrice, int $sizePriceId)
    {
        $obPrice = new Price();
        $obPriceValues = [
            'size_price_id' => $sizePriceId,
            'current_price' => $arPrice['currentPrice'],
            'is_included_in_menu' => $arPrice['isIncludedInMenu'],
            'next_price' => $arPrice['nextPrice'],
            'next_included_in_menu' => $arPrice['nextIncludedInMenu'],
            'next_date_price' => $arPrice['nextDatePrice'],
        ];
        $obPrice->load($obPriceValues, '');
        if (!$obPrice->save()) {
            $this->handleError('Price', $obPrice);
        }
    }
    private function processSize(array $arSize)
    {
        $obSize = Size::find()->where(['external_id' => $arSize['id']])->one();
        if (!$obSize) {
            $obSize = new Size();
        }
        $obSizeValues = [
            'external_id' => $arSize['id'],
            'name' => $arSize['name'],
            'priority' => $arSize['priority'],
            'is_default' => $arSize['isDefault']
        ];
        $obSize->load($obSizeValues, '');
        if (!$obSize->save()) {
            $this->handleError('Size', $obSize);
        }
    }

    public static function processTables(array $arTables)
    {
        foreach ($arTables as $table) {
            $obTable = Table::find()->where(['external_id' => $table['id']])->one();
            if (!$obTable) {
                $obTable = new Table();
                $obTable->external_id = $table['id'];
            }
            $arPropValues = [
                'table_number' => $table['number'],
                'name' => $table['name'],
                'seating_capacity' => $table['seatingCapacity'],
                'revision' => $table['revision'],
                'is_deleted' => $table['isDeleted'],
            ];
            $obTable->attributes = $arPropValues;
            if (!$obTable->save()) {
                self::handleError('Table', $obTable);
            }
        }
    }

    public static function processPaymentTypes(array $arPaymentTypes)
    {
        foreach ($arPaymentTypes as $paymentType) {
            $obPaymentType = PaymentType::find()->where(['external_id' => $paymentType['id']])->one();
            if (!$obPaymentType) {
                $obPaymentType = new PaymentType();
                $obPaymentType->external_id = $paymentType['id'];
            }
            $arPaymentTypeVals = [
                'code' => $paymentType['code'],
                'name' => $paymentType['name'],
                'is_deleted' => $paymentType['isDeleted'],
                'payment_processing_type' => $paymentType['paymentProcessingType'],
                'payment_type_kind' => $paymentType['paymentTypeKind'],
            ];
            $obPaymentType->attributes = $arPaymentTypeVals;
            if (!$obPaymentType->save()) {
                self::handleError('PaymentType', $obPaymentType);
            }
        }
    }
}
