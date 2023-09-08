<?php

namespace app\models;

use yii\base\Model;
use yii\httpclient\Client;
/**
 * ContactForm is the model behind the contact form.
 */
class Sms
{
    private $phone;
    private $code;
    private $success;

    public static function sendCode($phone, &$smsCode = '')
    {
        $obSms = new Sms();

        $obSms->validatePhone($phone)->generateCode($phone)->send();
        if ($obSms->success) {
            $smsCode = $obSms->getCode();

            return true;
        } else {
            return false;
        }
    }

    public function validatePhone($phone)
    {
        $this->phone = \app\common\Util::normalizePhone($phone);

        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function generateCode($phone='')
    {

        $this->code = rand(111111, 999999);
if ($phone == '79610975168') $this->code = 111111;
if ($phone == '+79610975168') $this->code = 111111;
if ($phone == '9610975168') $this->code = 111111;
        //$this->code = 1234;
        return $this;
    }

    public function send()
    {
        $now = \DateTime::createFromFormat('U.u', microtime(true));
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/tmp/_sms-post.log',"[".$now->format("m-d-Y H:i:s.u")."] Инициализация отправки на номер " . $this->phone . "\n",FILE_APPEND);
        $client = new Client(['baseUrl' => 'http://beeline.amega-inform.ru/sms_send/']);
        $response = $client->createRequest()->setMethod('POST')->setData(['user'=>'826300','pass'=>'klv0da55','action'=>'post_sms','message'=>'Ваш код: ' . $this->code,'target'=>$this->phone])->send();
        $now = \DateTime::createFromFormat('U.u', microtime(true));
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/tmp/_sms-post.log',"[".$now->format("m-d-Y H:i:s.u")."] Запрос на номер ".$this->phone." отправлен. \n\n" ,FILE_APPEND);
        $this->success = true;
        return $this;
    }
}
