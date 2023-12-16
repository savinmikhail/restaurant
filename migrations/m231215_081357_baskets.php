<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_081357_baskets extends Migration
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
            '{{%baskets}}',
            [
                'id'=> $this->primaryKey(11),
                'table_id'=> $this->integer(11)->notNull(),
                'created_at'=> $this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
                'updated_at'=> $this->timestamp()->notNull()->defaultExpression("CURRENT_TIMESTAMP"),
                'basket_total'=> $this->integer(11)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('order_id','{{%baskets}}',['table_id'],false);
        $this->createIndex('table_id','{{%baskets}}',['table_id'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('order_id', '{{%baskets}}');
        $this->dropIndex('table_id', '{{%baskets}}');
        $this->dropTable('{{%baskets}}');
    }
}
