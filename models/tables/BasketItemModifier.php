<?php

namespace app\models\tables;

use yii\db\ActiveRecord;

class BasketItemModifier extends ActiveRecord
{
    public function rules()
    {
        return [
            [['basket_item_id', 'modifier_id'], 'required'],
            [['basket_item_id', 'modifier_id', 'modifier_quantity'], 'integer'],
            [['modifier_quantity'], 'validateModifierQuantity', 'skipOnEmpty' => true, 'skipOnError' => false],
        ];
    }
    public function validateModifierQuantity($attribute_name)
    {
        $obModifier = Modifier::find()->where(['id' => $this->$attribute_name])->one();
        if ($this->modifier_quantity > $obModifier->max_amount) {
            $this->addError($attribute_name, 'Превышено маскимально допустимое количество модификатора');
            return false;
        }
    }

    public static function tableName()
    {
        return 'basket_item_modifiers';
    }

    protected function prefixName()
    {
        return 'basket_item_modifiers';
    }

    public function getModifier()
    {
        return $this->hasOne(Modifier::class, ['id' => 'modifier_id']);
    }


}
