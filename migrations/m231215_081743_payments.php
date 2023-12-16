<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_081743_payments extends Migration
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
            '{{%payments}}',
            [
                'id'=> $this->primaryKey(11),
                'sum'=> $this->integer(11)->notNull(),
                'order_ids'=> $this->string(255)->notNull(),
                'payment_method'=> $this->string(30)->notNull(),
                'created_at'=> $this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
                'updated_at'=> $this->timestamp()->notNull()->defaultValue('0000-00-00 00:00:00'),
                'external_id'=> $this->string(100)->notNull(),
                'paid'=> $this->tinyInteger(1)->notNull()->defaultValue(0),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%payments}}');
    }
}
