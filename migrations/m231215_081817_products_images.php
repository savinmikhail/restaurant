<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_081817_products_images extends Migration
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
            '{{%products_images}}',
            [
                'id'=> $this->primaryKey(11),
                'product_id'=> $this->integer(11)->notNull(),
                'image'=> $this->string(255)->notNull(),
            ],$tableOptions
        );
        $this->createIndex('product_id','{{%products_images}}',['product_id'],false);
        $this->createIndex('product_id_2','{{%products_images}}',['product_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('product_id', '{{%products_images}}');
        $this->dropIndex('product_id_2', '{{%products_images}}');
        $this->dropTable('{{%products_images}}');
    }
}
