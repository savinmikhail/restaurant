<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_075607_basket_items extends Migration
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
            '{{%basket_items}}',
            [
                'id'=> $this->primaryKey(11),
                'basket_id'=> $this->integer(11)->notNull(),
                'product_id'=> $this->integer(11)->notNull(),
                'quantity'=> $this->float()->notNull(),
                'price'=> $this->integer(11)->notNull()->defaultValue(0),
                'size_id'=> $this->integer(11)->null()->defaultValue(null),
                'order_id'=> $this->integer(11)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('basket_id','{{%basket_items}}',['basket_id','product_id'],false);
        $this->createIndex('modifier_id','{{%basket_items}}',['size_id'],false);
        $this->createIndex('order_id','{{%basket_items}}',['order_id'],false);
        $this->createIndex('basket_id_2','{{%basket_items}}',['basket_id'],false);
        $this->createIndex('order_id_2','{{%basket_items}}',['order_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('basket_id', '{{%basket_items}}');
        $this->dropIndex('modifier_id', '{{%basket_items}}');
        $this->dropIndex('order_id', '{{%basket_items}}');
        $this->dropIndex('basket_id_2', '{{%basket_items}}');
        $this->dropIndex('order_id_2', '{{%basket_items}}');
        $this->dropTable('{{%basket_items}}');
    }
}
