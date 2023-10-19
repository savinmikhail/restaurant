<?php

namespace app\common;

use Yii;

class Request
{
    static public function getReqParam($name, $default = '')
    {
        $request = Yii::$app->request;
        
        $val = $request->get($name);
        
        if (!$val) {
            $val = $request->post($name);
        }
        
        if (!$val) {
            $val = $request->post($name);
        }
        
        if (!$val) {
            $params = $request->getRawBody();
            $json   = json_decode($params, true);
            
            if (!is_array($json)) {
                $json = [];
            }
            
            if (isset($json[$name])) {
                $val = $json[$name];
            }
        }
        
        if (!$val) {
            return $default;
        }
        
        return $val;
    }
}