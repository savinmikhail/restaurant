<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_081756_payment_types extends Migration
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
            '{{%payment_types}}',
            [
                'id'=> $this->primaryKey(11),
                'external_id'=> $this->string(40)->notNull(),
                'code'=> $this->string(30)->notNull(),
                'name'=> $this->string(30)->notNull(),
                'is_deleted'=> $this->tinyInteger(1)->notNull(),
                'payment_processing_type'=> $this->string(10)->notNull(),
                'payment_type_kind'=> $this->string(10)->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%payment_types}}');
    }
}
