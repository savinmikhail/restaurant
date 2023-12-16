<?php

use yii\db\Schema;
use yii\db\Migration;

class m231216_063845_settings extends Migration
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
            '{{%settings}}',
            [
                'id'=> $this->primaryKey(11),
                'name'=> $this->string(30)->notNull(),
                'value'=> $this->integer(11)->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%settings}}');
    }
}
