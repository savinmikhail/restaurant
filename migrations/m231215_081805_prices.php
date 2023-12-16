<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_081805_prices extends Migration
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
            '{{%prices}}',
            [
                'id'=> $this->primaryKey(11),
                'current_price'=> $this->double()->notNull(),
                'is_included_in_menu'=> $this->tinyInteger(1)->notNull(),
                'next_price'=> $this->double()->null()->defaultValue(null),
                'next_included_in_menu'=> $this->tinyInteger(1)->notNull(),
                'next_date_price'=> $this->datetime()->null()->defaultValue(null),
                'size_price_id'=> $this->integer(11)->notNull(),
            ],$tableOptions
        );
        $this->createIndex('size_price_id','{{%prices}}',['size_price_id'],false);
        $this->createIndex('size_price_id_2','{{%prices}}',['size_price_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('size_price_id', '{{%prices}}');
        $this->dropIndex('size_price_id_2', '{{%prices}}');
        $this->dropTable('{{%prices}}');
    }
}
