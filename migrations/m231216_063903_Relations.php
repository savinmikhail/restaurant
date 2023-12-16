<?php

use yii\db\Schema;
use yii\db\Migration;

class m231216_063903_Relations extends Migration
{

    public function init()
    {
       $this->db = 'db';
       parent::init();
    }

    public function safeUp()
    {
        $this->addForeignKey('fk_size_prices_size_id',
            '{{%size_prices}}','size_id',
            '{{%sizes}}','id',
            'CASCADE','CASCADE'
         );
        $this->addForeignKey('fk_size_prices_product_id',
            '{{%size_prices}}','product_id',
            '{{%products}}','id',
            'CASCADE','CASCADE'
         );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_size_prices_size_id', '{{%size_prices}}');
        $this->dropForeignKey('fk_size_prices_product_id', '{{%size_prices}}');
    }
}
