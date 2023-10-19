<?php

namespace app\Services;

class Payment 
{
    
    public static function createSberPaymentUrl($last_order_id, $amount)
    {
        return [
            1,
            'https://smth.smth',
            []
        ];
        /* $returnUrl = "https://mp.kv.tomsk.ru/api/payment/redirect?orderId={$last_order_id}&success=true";
         $failUrl = "https://mp.kv.tomsk.ru/api/payment/redirect?orderId={$last_order_id}&success=false";
         $host = 'https://mp.kv.tomsk.ru/api/payment/callback';
         //$token = 'auaclh27bt0pejpl05pf138f0';
         $token = 'l08ccs6936bvhai03i5p2fu8fr'; //prod
         $amount = round($amount * 100, 0);
         $arrContextOptions = [
         'ssl' => [
         'verify_peer' => false,
         'verify_peer_name' => false,
         ],
         ];
         //$url = 'https://3dsec.sberbank.ru/payment/rest/register.do'
         $url = 'https://securepayments.sberbank.ru/payment/rest/register.do' //prod
         . "?amount={$amount}&orderNumber=M{$last_order_id}"
         . '&token=' . $token
         // .'&dynamicCallbackUrl='.$host
         . '&pageView=MOBILE'
         . '&returnUrl=' . $returnUrl
         . '&failUrl=' . $failUrl;
         $response = json_decode(file_get_contents($url, false, stream_context_create($arrContextOptions)));

         if (isset($response->formUrl)) {
         return [
         1,
         $response->formUrl,
         []
         ];
         } else {
         return [
         0,
         [
         'code' => $response->errorCode,
         'message' => $response->errorMessage,
         ],
         ['url' => $url]
         ];
         }*/
    }
}
