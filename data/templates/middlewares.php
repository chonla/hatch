<?php

$cors_options = [
    "origin" => [{{%cors_origin%}}],
    "methods" => [{{%cors_methods%}}],
    "headers.allow" => [{{%cors_headers_allow%}}],
    "headers.expose" => [{{%cors_headers_expose%}}],
    "credentials" => {{%cors_credentials%}},
    "cache" => {{%cors_cache%}},
];

$app->add(new \Tuupola\Middleware\Cors($cors_options));