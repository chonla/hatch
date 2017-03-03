<?php

$container = $app->getContainer();

// redbean
$container['r'] = function ($c) {
    $settings = $c->get('settings')['r'];
    $r = new \RedBeanPHP\R();
    $r->setup($settings['dsn']);
    return $r;
};
