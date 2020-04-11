<?php

namespace Kwabs;

use Exception;
use GuzzleHttp\Client;
use Necronru\Payture\EWallet\Card\Command\GetCardListCommand;
use Necronru\Payture\EWallet\Card\Command\RemoveCardCommand;
use Necronru\Payture\EWallet\EWallet;
use Necronru\Payture\EWallet\Payment\Command\InitCommand;
use Necronru\Payture\EWallet\Payment\Enum\SessionType;

class PaytureProxy
{

    public static function set_eWallet() // создание сервиса
    {
        global $globalCity;
        if ($globalCity['payture_enabled'] == 0) return false;
        $eWallet = new EWallet(
            new Client(['base_uri' => 'https://secure.payture.com']), //sandbox3 - test
            $globalCity['payture_add'],
            $globalCity['payture_check_pass']
        );
        return ["service" => $eWallet, "region" => $globalCity['code']];
    }

    public static function Add($client_id) // получаем url для фрейма
    {
        try {
            $eWallet = self::set_eWallet();
            if (!$eWallet) throw new Exception();
            $response = $eWallet['service']->payment()->init(new InitCommand(
                SessionType::ADD,
                'https://' . $_SERVER['HTTP_HOST'] . '/profile/add_success',
                $_SERVER['HTTP_CLIENT_IP'],
                $client_id . "%40" . $eWallet['region'],
                '***'
            ));
            $link = $eWallet['service']->card()->getSessionLink($response->SessionId);
            return $link;
        } catch (Exception $exception) {
            return 0;
        }
    }

    public static function GetList($client_id) // получение списка карта пользователя
    {
        try {
            $eWallet = self::set_eWallet();
            if (!$eWallet) throw new Exception();
            $response = $eWallet['service']->card()->getList(new GetCardListCommand($client_id . "%40" . $eWallet['region'],'***'));
            return $response->Item;
        } catch (Exception $exception) {
            return [];
        }
    }

    public static function Remove($client_id, $card_id) // удаление карты пользователя
    {
        if (!$card_id) return 0;
        try {
            $eWallet = self::set_eWallet();
            if (!$eWallet) throw new Exception();
            $eWallet['service']->card()->remove(new RemoveCardCommand($card_id, $client_id . "%40" . $eWallet['region'],'***'));
            return 1;
        } catch (Exception $exception) {
            return 0;
        }
    }
}