<?php

use yii\db\Schema;
use yii\db\Migration;

class m231216_063902_size_prices extends Migration
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
            '{{%size_prices}}',
            [
                'id'=> $this->primaryKey(11),
                'size_id'=> $this->integer(11)->null()->defaultValue(null),
                'product_id'=> $this->integer(11)->notNull(),
            ],$tableOptions
        );
        $this->createIndex('size_id','{{%size_prices}}',['size_id','product_id'],false);
        $this->createIndex('product_id','{{%size_prices}}',['product_id'],false);
        $this->createIndex('size_id_2','{{%size_prices}}',['size_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('size_id', '{{%size_prices}}');
        $this->dropIndex('product_id', '{{%size_prices}}');
        $this->dropIndex('size_id_2', '{{%size_prices}}');
        $this->dropTable('{{%size_prices}}');
    }
}
