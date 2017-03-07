<?php

$container = $app->getContainer();

// redbean
$container['r'] = function ($c) {
    $settings = $c->get('settings')['r'];
    $r = new \RedBeanPHP\R();
    $r->setup($settings['dsn']);
    return $r;
};

// filter
$container['data_filter'] = function ($c) {
    $settings = $c->get('settings')['data_filter'];
    $f = new \App\Services\FilterService($settings);
    return $f;
};