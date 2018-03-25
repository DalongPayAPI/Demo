<?php
require_once "payapi_crypt.php";

$apiKey    = "11111111";
$clientKey = "22222222";

//Получаем сессионный ключ
$cryptSessionObject = getSessionKey($clientKey);
$SessionObject = \PAYAPI\cryptLib::decrypt($cryptSessionObject, $clientKey);
$session = JSON_decode($SessionObject);
$sessionKey = $session->key;

//Формируем крипто-ключ
$cryptoKey = $apiKey.$sessionKey.$clientKey;

//ПРИЕМ ПЛАТЕЖА

//Формируем счет на оплату (параметр запроса)
$receipt  = new stdClass();
$receipt->currency = "LTC";
$receipt->amount = "0.005";
$receiptStr = JSON_encode($receipt);

//Шифруем параметры платежа
$cryptoReceiptStr = \PAYAPI\cryptLib::crypt($buyStr, $cryptoKey);

//Выполняем вызов функции АПИ и  получаем зашифрованный результат
$cryptoReceiptResult = Receipt($sessionKey, $cryptoReceiptStr);

//Расшифровываем результат
$ReceiptResult = \PAYAPI\cryptLib::decrypt($cryptoReceiptResult, $cryptoKey);

//Получаем объект Чек
$ReceiptObject = JSON_decode(ReceiptResult);

//Получаем инфу из объекта (ИД чека)
$recieptID = $ReceiptObject->receipt;


//СТАТУС ПЛАТЕЖА
//Формируем параметр запроса
//В чачестве параметра используем ИД чека, сформированного на предыдущем шаге
$param  = new stdClass();
$param->receipt = $recieptID;
$paramStr = JSON_encode($param);
//Шифруем параметр запроса
$cryptoParamStr = \PAYAPI\cryptLib::crypt($paramStr, $cryptoKey);
//Выполняем вызов функции, получаем шифрованный результат
$cryptReceitpResult = ReceiptInfo($sessionKey, $cryptoParamStr);
//Расшифровываем результат
$ReceiptResult = \PAYAPI\cryptLib::decrypt($cryptReceitpResult, $cryptoKey);
echo $ReceiptResult;



function getSessionKey($key) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,"https://test-payapi.dalongpay.com/v1/");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'm=init&q=\'{"key": "'.$key.'"}\'');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec ($ch);

    curl_close ($ch);
    return $server_output;
}


function Receipt($key, $param) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,"https://test-payapi.dalongpay.com/v1/");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'm=get&q=\'{"key": "'.$key.'", "param":"'.urlencode($param).'"}\'');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec ($ch);

    curl_close ($ch);
    return $server_output;
}


function ReceiptInfo($key, $param) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,"https://test-payapi.dalongpay.com/v1/");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'm=receipt&q=\'{"key": "'.$key.'", "param":"'.urlencode($param).'"}\'');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec ($ch);

    curl_close ($ch);
    return $server_output;
}

