<?php

require '../vendor/autoload.php';

$replaceFunction = function ($matches) {
    return str_replace("\n", "&#10;", $matches[0]);
};

$xml = file_get_contents('sms_backup.xml');
$xml = preg_replace_callback("/body=\"[^']+\"/i", $replaceFunction, $xml);

$service = new Sabre\Xml\Service();
$result  = $service->parse($xml);

$filtered = [];

foreach ($result as $row) {

    $number = $row['attributes']['address'];
    $prefix = substr($number, 0, 3);

    if (in_array($prefix, ['017', '018', '019', '016', '011', '015'])) {
        $number = str_replace([' ', '-'], '', '+88' . $number);
    } elseif ($prefix == '+88') {
        $number = str_replace([' ', '-'], '', $number);
    }

    $row['attributes']['address'] = str_replace([' ', '-'], '', $row['attributes']['address']);

    $attr = $row['attributes'];
    unset($attr['time']);
    unset($attr['date']);

    $value = implode('|', array_values($attr));
    $hash  = md5($value);

    if (!array_key_exists($hash, $filtered)) {
        $filtered[$hash] = $row['attributes'];
        $filtered[$hash]['address'] = $number;
    }

}

$count = count($filtered);

$output = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<allsms count="$count">

XML;

foreach ($filtered as $hash => $row) {
    $body   = htmlentities($row['body']);
    $output .= <<<XML
	<sms address="{$row['address']}" time="{$row['time']}" date="{$row['date']}" type="{$row['type']}" body="{$body}" read="{$row['read']}" service_center="{$row['service_center']}" name="{$row['name']}" />\n
XML;
}

$output .= "</allsms>";

file_put_contents('result.xml', $output);
