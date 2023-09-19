<?php

namespace app\Services;

use app\models\tables\Categories;
use app\models\tables\Group;
use app\models\tables\GroupModifier;
use app\models\tables\Modifier;
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
        Price::deleteAll();
        SizePrice::deleteAll();
        ProductsImages::deleteAll();

        foreach ($data['groups'] as $arGroup) {
            $this->processGroups($arGroup);
        }
        foreach ($data['sizes'] as $arSize) {
            $this->processSizes($arSize);
        }
        foreach ($data['productCategories'] as $arCategory) {
            $this->processCategories($arCategory);
        }
        foreach ($data['products'] as $arProduct) {
            $this->processProducts($arProduct);
        }
    }

    private function processGroups(array $arGroup)
    {
        $obGroup = Group::find()->where(['external_id' => $arGroup['id']])->one();
        if (!$obGroup) {
            $obGroup = new Group;
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

    private function processCategories(array $arCategory)
    {
        $obCategory = Categories::find()->where(['external_id' => $arCategory['id']])->one();
        if (!$obCategory) {
            $obCategory = new Categories;
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

    private function processProducts(array $arProduct)
    {
        $obProduct = Products::find()->where(['external_id' => $arProduct['id']])->one();
        if (!$obProduct) {
            $obProduct = new Products;
        }

        if (isset($arProduct['imageLinks']) && !empty($arProduct['imageLinks'])) {
            $image = $arProduct['imageLinks'][0];
            //если несколько картинок
            if (isset($arProduct['imageLinks'][1])) {
                $this->processProductImages($arProduct['imageLinks'], $obProduct->id);
            }
        }

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
        foreach ($arProduct['sizePrices'] as $arSizePrice) {
            $this->processSizePrices($arSizePrice, $obProduct->id);
        }
        if ($arProduct['modifiers']) {
            $this->processProductModifiers($arProduct['modifiers'], $obProduct->id);
        }
        if ($arProduct['groupModifiers']) {
            $this->processProductGroupModifiers($arProduct['groupModifiers'], $obProduct->id);
        }
    }

    private function processProductGroupModifiers(array $groupModifiers, int $productId)
    {
        foreach ($groupModifiers as $groupModifier) {
            $obGroupModifier = GroupModifier::find()->where(['product_id' => $productId, 'external_id' => $groupModifier['id']])->one();
            if (!$obGroupModifier) {
                $obGroupModifier = new GroupModifier;
                $obGroupModifier->product_id = $productId;
                $obGroupModifier->external_id = $groupModifier['id'];
            }
            $obGroupModifierValues = [
                "default_amount" => $groupModifier['defaultAmount'],
                "min_amount" => $groupModifier['minAmount'],
                "max_amount" => $groupModifier['maxAmount'],
                "required" => $groupModifier['required'],
                "hide_if_default_amount" => $groupModifier['hideIfDefaultAmount'],
                "splittable" => $groupModifier['splittable'],
                "free_of_charge_amount" => $groupModifier['freeOfChargeAmount'],
                'child_modifiers_have_min_max_restrictions' => $groupModifier['childModifiersHaveMinMaxRestrictions'],
            ];
            $obGroupModifier->load($obGroupModifierValues, '');
            if (!$obGroupModifier->save()) {
                $this->handleError('Modifier', $obGroupModifier);
            }
            if ($groupModifier['childModifiers']) {
                $this->processProductModifiers($groupModifier['childModifiers'], $productId, $obGroupModifier->id);
            }
        }
    }

    private function processProductModifiers(array $modifiers, int $productId, $parentModifierId = null)
    {
        foreach ($modifiers as $modifier) {
            $obModifier = Modifier::find()->where(['product_id' => $productId, 'external_id' => $modifier['id']])->one();
            if (!$obModifier) {
                $obModifier = new Modifier;
                $obModifier->product_id = $productId;
                $obModifier->external_id = $modifier['id'];
            }
            $obModifierValues = [
                "min_amount" => $modifier['minAmount'],
                "max_amount" => $modifier['maxAmount'],
                "required" => $modifier['required'],
                "hide_if_default_amount" => $modifier['hideIfDefaultAmount'],
                "default_amount" => $modifier['defaultAmount'],
                "splittable" => $modifier['splittable'],
                "free_of_charge_amount" => $modifier['freeOfChargeAmount'],
                'group_modifier_id' => $parentModifierId,
            ];

            $obModifier->load($obModifierValues, '');
            if (!$obModifier->save()) {
                $this->handleError('Modifier', $obModifier);
            }
        }
    }

    private function processProductImages(array $imageLinks, int $productId)
    {

        foreach ($imageLinks as $link) {
            $obProductImage = new ProductsImages;
            $obProductImage->product_id = $productId;
            $obProductImage->image = $link;
            $obProductImage->save();
        }
    }
    private function processProductProps(array $arProduct, int $productId)
    {
        //исключаю все то, что уже лежит в products, или в отдельных таблицах, или является массивом (и подлежит лежанию в отдельной таблице)
        $excludeKeys = ['id', 'name', 'description', 'isDeleted', 'order', 'imageLinks', 'productCategoryId', 'sizePrices', 'modifiers', 'groupModifiers', 'tags'];
        $productProps = array_diff(array_keys($arProduct), $excludeKeys);
        foreach ($productProps as $productProp) {
            $obProductProps = ProductsProperties::find()->where(['code' => $productProp])->one();
            if (!$obProductProps) {
                $obProductProps = new ProductsProperties;
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
        $obProductPropsVals = ProductsPropertiesValues::find()->where([
            'product_id' => $productId,
            'property_id' => $obProductProps->id
        ])
            ->one();

        if (!$obProductPropsVals) {
            $obProductPropsVals = new ProductsPropertiesValues;
            $obProductPropsVals->product_id = $productId;
            $obProductPropsVals->property_id = $obProductProps->id;
        }
        $obProductPropsVals->value = (string) $arProduct[$obProductProps->code] ?? null;
        if (!$obProductPropsVals->save()) {
            $this->handleError('ProductPropsVals', $obProductPropsVals);
        }
    }

    private function processSizePrices(array $arSizePrice, int $productId)
    {

        $obSizePrice = new SizePrice;
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

    private function processPrices(array $arPrice, int $sizePriceId)
    {
        $obPrice = new Price;
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
    private function processSizes(array $arSize)
    {
        $obSize = Size::find()->where(['external_id' => $arSize['id']])->one();
        if (!$obSize) {
            $obSize = new Size;
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
                $obTable = new Table;
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
                $obPaymentType = new PaymentType;
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
