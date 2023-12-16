<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_081716_orders extends Migration
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
            '{{%orders}}',
            [
                'id'=> $this->primaryKey(11),
                'basket_id'=> $this->integer(11)->notNull(),
                'status'=> $this->string(100)->notNull(),
                'order_sum'=> $this->integer(11)->notNull(),
                'created_at'=> $this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
                'updated_at'=> $this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
                'table_id'=> $this->integer(11)->notNull(),
                'external_id'=> $this->string(50)->notNull(),
                'payment_method'=> $this->string(20)->null()->defaultValue(null),
                'paid'=> $this->tinyInteger(1)->notNull()->defaultValue(0),
                'canceled'=> $this->tinyInteger(1)->notNull()->defaultValue(0),
                'confirmed'=> $this->tinyInteger(4)->notNull(),
            ],$tableOptions
        );
        $this->createIndex('basket_id','{{%orders}}',['basket_id'],false);
        $this->createIndex('user_id','{{%orders}}',['table_id'],false);
        $this->createIndex('external_id','{{%orders}}',['external_id'],false);
        $this->createIndex('table_id','{{%orders}}',['table_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('basket_id', '{{%orders}}');
        $this->dropIndex('user_id', '{{%orders}}');
        $this->dropIndex('external_id', '{{%orders}}');
        $this->dropIndex('table_id', '{{%orders}}');
        $this->dropTable('{{%orders}}');
    }
}
