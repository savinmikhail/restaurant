<?php

use yii\db\Schema;
use yii\db\Migration;

class m231216_063948_tables extends Migration
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
            '{{%tables}}',
            [
                'id'=> $this->primaryKey(11),
                'table_number'=> $this->integer(11)->notNull(),
                'external_id'=> $this->string(255)->notNull(),
                'name'=> $this->string(100)->notNull(),
                'seating_capacity'=> $this->integer(11)->notNull(),
                'revision'=> $this->integer(11)->notNull(),
                'is_deleted'=> $this->tinyInteger(1)->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%tables}}');
    }
}
