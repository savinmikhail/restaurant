<?php

use yii\db\Schema;
use yii\db\Migration;

class m231216_064029_tag_product extends Migration
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
            '{{%tag_product}}',
            [
                'id'=> $this->primaryKey(11),
                'product_id'=> $this->integer(11)->notNull(),
                'tag_id'=> $this->integer(11)->notNull(),
            ],$tableOptions
        );
        $this->createIndex('product_id','{{%tag_product}}',['product_id','tag_id'],false);
        $this->createIndex('product_id_2','{{%tag_product}}',['product_id'],false);
        $this->createIndex('tag_id','{{%tag_product}}',['tag_id'],false);
        $this->createIndex('product_id_3','{{%tag_product}}',['product_id'],false);
        $this->createIndex('tag_id_2','{{%tag_product}}',['tag_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('product_id', '{{%tag_product}}');
        $this->dropIndex('product_id_2', '{{%tag_product}}');
        $this->dropIndex('tag_id', '{{%tag_product}}');
        $this->dropIndex('product_id_3', '{{%tag_product}}');
        $this->dropIndex('tag_id_2', '{{%tag_product}}');
        $this->dropTable('{{%tag_product}}');
    }
}
