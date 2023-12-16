<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_075514_products extends Migration
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
            '{{%products}}',
            [
                'id'=> $this->primaryKey(11),
                'name'=> $this->string(100)->notNull(),
                'balance'=> $this->integer(11)->notNull()->defaultValue(0),
                'description'=> $this->string(255)->notNull(),
                'image'=> $this->string(100)->notNull(),
                'sort'=> $this->integer(11)->notNull()->defaultValue(0),
                'is_deleted'=> $this->tinyInteger(1)->notNull()->defaultValue(1),
                'external_id'=> $this->string(255)->notNull(),
                'category_id'=> $this->integer(11)->null()->defaultValue(null),
                'code'=> $this->string(6)->notNull(),
                'created_at'=> $this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
                'updated_at'=> $this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
            ],$tableOptions
        );
        $this->createIndex('external_id','{{%products}}',['external_id'],true);
        $this->createIndex('category_id','{{%products}}',['category_id'],false);
        $this->createIndex('category_id_2','{{%products}}',['category_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('external_id', '{{%products}}');
        $this->dropIndex('category_id', '{{%products}}');
        $this->dropIndex('category_id_2', '{{%products}}');
        $this->dropTable('{{%products}}');
    }
}
