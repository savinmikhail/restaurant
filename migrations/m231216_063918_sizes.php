<?php

use yii\db\Schema;
use yii\db\Migration;

class m231216_063918_sizes extends Migration
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
            '{{%sizes}}',
            [
                'id'=> $this->primaryKey(11),
                'name'=> $this->string(100)->notNull(),
                'priority'=> $this->integer(11)->notNull(),
                'is_default'=> $this->tinyInteger(1)->notNull()->defaultValue(0),
                'external_id'=> $this->string(255)->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%sizes}}');
    }
}
