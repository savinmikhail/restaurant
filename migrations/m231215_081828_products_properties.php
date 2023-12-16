<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_081828_products_properties extends Migration
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
            '{{%products_properties}}',
            [
                'id'=> $this->primaryKey(11),
                'sort'=> $this->integer(11)->notNull()->defaultValue(100),
                'code'=> $this->string(256)->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%products_properties}}');
    }
}
