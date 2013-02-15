<?php
require 'vendor/autoload.php';

use Zend\Http\Request;
use Zend\Http\Client;

/**
 * Calculate Zend Server Web API request signature
 *
 * @param string $host Exact value of the 'Host:' HTTP header
 * @param string $path Request URI
 * @param integer $timestamp Timestamp used for the 'Date:' HTTP header
 * @param string $userAgent Exact value of the 'User-Agent:' HTTP header
 * @param string $apiKey Zend Server API key
 * @return string Calculated request signature
 */
function generateRequestSignature($host, $path, $timestamp, $userAgent, $apiKey)
{
    $data = $host . ":" .
        $path . ":" .
        $userAgent . ":" .
        gmdate('D, d M Y H:i:s ', $timestamp) . 'GMT';

    return hash_hmac('sha256', $data, $apiKey);
}

$time = time();
$sign = generateRequestSignature(
        'localhost:10081',
        '/ZendServer/Api/restartPhp',
        $time,
        'Zend_Http_Client/1.10',
        'e65fd9ff41d57e74fd229302327101432374f094ee8b52cc9336d74ddac33c48'
    );
$request = new Request();
$request->setMethod('POST');
$request->setUri('http://localhost:10081/ZendServer/Api/restartPhp');
$request->getHeaders()->addHeaders(array(
    'Content-type' => 'application/x-www-form-urlencoded',
    'Date' => gmdate('D, d M y H:i:s ', $time) . 'GMT',
    'Accept' => 'application/vnd.zend.serverapi+xml;version=1.3',
    'User-agent'    => 'Zend_Http_Client/1.10',
    'X-Zend-Signature' => 'creator-hosts; ' . $sign
));

$client = new Client();
$response = $client->dispatch($request);
if ($response->isSuccess()) {
    echo $response->getContent();
} else {
    echo $response->getHeaders()->toString();
    echo "\r\n";
    echo $response->getContent();
}
