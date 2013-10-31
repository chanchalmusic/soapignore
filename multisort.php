<?php

$arr1 = array('b', 'z', 'd', 'a');

$arr2 = array('100', '10', '20', '3');

array_multisort($arr1, $arr2);

$arrCache = array(
    'output' => array('<li>a</li>', '<li>b</li>', '<li>d</li>', '<li>c</li>'),
    'time' => 434343434,
    'tips no' => 12
);

$arrCacheModified = array(
    'data' => array(
        array(
        'text' => '<li>a</li>',
        'odds' => 100
        ),
        array(
            'text' => '<li>b</li>',
            'odds' => 10
        ),
        array(
            'text' => '<li>c</li>',
            'odds' => 20
        )
    ),
    'time' => 434343434,
    'tips no' => 12
);

$lists = implode(PHP_EOL, $arrCache['output']);

$arrListTexts = array();

foreach($arrCacheModified['data'] as $data) {
    $arrListTexts[] = $data['text'];
}

$newLists = implode(PHP_EOL, $arrListTexts);

echo $lists;
echo $newLists;

$dataArray = $arrCacheModified['data'];

$arrOdds = array();
foreach($dataArray as $data) {
    $arrOdds[] = $data['odds'];
}

print_r($arrOdds);
print_r($arrCacheModified);
array_multisort($arrOdds, SORT_DESC, $arrCacheModified['data']);
print_r($arrOdds);
print_r($arrCacheModified);

die();

print_r($arrCache);

print_r($arr1);
print_r($arr2);
