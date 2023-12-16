<?php

use yii\db\Schema;
use yii\db\Migration;

class m231215_081818_Relations extends Migration
{

    public function init()
    {
       $this->db = 'db';
       parent::init();
    }

    public function safeUp()
    {
        $this->addForeignKey('fk_products_images_product_id',
            '{{%products_images}}','product_id',
            '{{%products}}','id',
            'CASCADE','CASCADE'
         );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_products_images_product_id', '{{%products_images}}');
    }
}
