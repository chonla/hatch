<?php

return [
    "settings" => [
        "displayErrorDetails" => true,
        "database" => [
            "dsn" => "{{%db_dsn%}}",
            "user" => "{{%db_user%}}",
            "password" => "{{%db_password%}}"
        ],
        'restful' => [
            'page_size' => 30,
        ],
    ],
];