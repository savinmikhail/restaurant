<?php

use yii\db\Schema;
use yii\db\Migration;

class m231216_063714_Relations extends Migration
{

    public function init()
    {
       $this->db = 'db';
       parent::init();
    }

    public function safeUp()
    {
        $this->addForeignKey('fk_products_properties_values_property_id',
            '{{%products_properties_values}}','property_id',
            '{{%products_properties}}','id',
            'CASCADE','CASCADE'
         );
        $this->addForeignKey('fk_products_properties_values_product_id',
            '{{%products_properties_values}}','product_id',
            '{{%products}}','id',
            'CASCADE','CASCADE'
         );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_products_properties_values_property_id', '{{%products_properties_values}}');
        $this->dropForeignKey('fk_products_properties_values_product_id', '{{%products_properties_values}}');
    }
}
