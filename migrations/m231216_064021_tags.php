<?php

use yii\db\Schema;
use yii\db\Migration;

class m231216_064021_tags extends Migration
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
            '{{%tags}}',
            [
                'id'=> $this->primaryKey(11),
                'name'=> $this->string(30)->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%tags}}');
    }
}
