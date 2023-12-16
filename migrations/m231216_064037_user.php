<?php

use yii\db\Schema;
use yii\db\Migration;

class m231216_064037_user extends Migration
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
            '{{%user}}',
            [
                'user_id'=> $this->primaryKey(11),
                'user_login'=> $this->string(100)->notNull(),
                'user_password'=> $this->string(100)->notNull(),
                'user_role'=> $this->string(20)->notNull()->defaultValue('ADMIN'),
                'user_auth_key'=> $this->string(100)->notNull(),
                'user_remote_ip'=> $this->string(255)->notNull(),
            ],$tableOptions
        );

    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
