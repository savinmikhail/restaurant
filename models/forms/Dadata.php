<?php

namespace app\models;

use yii\httpclient\Client;

class Dadata
{
    private $token;
    private $secret;

    public function __construct($token, $secret)
    {
        $this->token = $token;
        $this->secret = $secret;
    }

    public function geocode($query)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setUrl('https://suggestions.dadata.ru/suggestions/api/4_1/rs/geolocate/address')
            ->setOptions([
                CURLOPT_CONNECTTIMEOUT => 15, // тайм-аут подключения
                CURLOPT_TIMEOUT => 15, // тайм-аут получения данных
            ])
            ->setMethod('POST')
            ->addHeaders(['Content-Type' => 'application/json', 'Authorization' => 'Token '.$this->token])
            ->setContent(json_encode($query))
            ->send();

        return $response->content;
    }

    public function suggestion($query,$nobuild = false)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setUrl('https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address')
            ->setOptions([
                CURLOPT_CONNECTTIMEOUT => 15, // тайм-аут подключения
                CURLOPT_TIMEOUT => 15, // тайм-аут получения данных
            ])
            ->setMethod('POST')
            ->addHeaders(['Content-Type' => 'application/json', 'Authorization' => 'Token '.$this->token])
            ->setContent(json_encode($this->buildQuery($query,$nobuild), JSON_UNESCAPED_UNICODE))
            ->send();

        return $response->content;
    }

    private function buildQuery($query,$nobuild = false)
    {
        $res =  [
        'query' => $query,
        'locations' => [
            ['region' => 'Томская'],
            ['city' => 'Юрга'],
            ['city' => 'Томск'],
        ],
    ];
    if ($nobuild) unset($res['locations']);
    return $res;
    }
}
