<?php

use yii\db\Schema;
use yii\db\Migration;

class m231216_063713_products_properties_values extends Migration
{

    public function init()
    {
        $this->db = 'db';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%products_properties_values}}',
            [
                'id'=> $this->primaryKey(11),
                'product_id'=> $this->integer(11)->notNull(),
                'property_id'=> $this->integer(11)->notNull(),
                'value'=> $this->string(255)->notNull(),
            ],$tableOptions
        );
        $this->createIndex('product_id','{{%products_properties_values}}',['product_id','property_id'],false);
        $this->createIndex('property_id','{{%products_properties_values}}',['property_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('product_id', '{{%products_properties_values}}');
        $this->dropIndex('property_id', '{{%products_properties_values}}');
        $this->dropTable('{{%products_properties_values}}');
    }
}
