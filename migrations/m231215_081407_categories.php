<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_081407_categories extends Migration
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
            '{{%categories}}',
            [
                'id'=> $this->primaryKey(11),
                'name'=> $this->string(100)->notNull(),
                'sort'=> $this->integer(11)->notNull()->defaultValue(0),
                'is_deleted'=> $this->tinyInteger(1)->notNull()->defaultValue(1),
                'image'=> $this->string(255)->notNull(),
                'description'=> $this->string(255)->notNull(),
                'external_id'=> $this->string(255)->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%categories}}');
    }
}
