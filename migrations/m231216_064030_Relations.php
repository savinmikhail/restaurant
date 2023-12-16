<?php

use yii\db\Schema;
use yii\db\Migration;

class m231216_064030_Relations extends Migration
{

    public function init()
    {
       $this->db = 'db';
       parent::init();
    }

    public function safeUp()
    {
        $this->addForeignKey('fk_tag_product_product_id',
            '{{%tag_product}}','product_id',
            '{{%products}}','id',
            'CASCADE','CASCADE'
         );
        $this->addForeignKey('fk_tag_product_tag_id',
            '{{%tag_product}}','tag_id',
            '{{%tags}}','id',
            'CASCADE','CASCADE'
         );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_tag_product_product_id', '{{%tag_product}}');
        $this->dropForeignKey('fk_tag_product_tag_id', '{{%tag_product}}');
    }
}
