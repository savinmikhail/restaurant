<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_081703_groups extends Migration
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
            '{{%groups}}',
            [
                'id'=> $this->primaryKey(11),
                'external_id'=> $this->string(255)->notNull(),
                'name'=> $this->string(255)->notNull(),
                'is_deleted'=> $this->tinyInteger(1)->notNull()->defaultValue(1),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%groups}}');
    }
}
