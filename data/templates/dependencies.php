<?php

$container = $app->getContainer();

// service
$container['data_service'] = function ($c) {
    $settings = $c->get('settings')['database'];
    $data = new \App\Services\DataService($settings);
    return $data;
};

// filter
$container['data_filter'] = function ($c) {
    $settings = $c->get('settings')['data_filter'];
    $f = new \App\Services\FilterService($settings);
    return $f;
};