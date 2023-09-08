<?php 
namespace app\models;

use Yii;
use yii\base\Model;

class FeedbackForm extends Model
{
    public $entity;
    public $fio;
    public $phone;
    public $message;
    public $order_id;
    public $mark;
    public $file;

    public function rules()
    {
        return [
            [['entity', 'fio', 'phone'], 'required'],
            [['entity', 'fio', 'phone','order_id',  'message' ], 'string'],
            [['mark'], 'integer'],
            [['file'], 'file']
        ];
    }
}
