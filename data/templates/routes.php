<?php

{{block:routes}}
$app->get('/{{%entity_name%}}', 'App\Controllers\{{%class_name%}}Controller:getCollection');
$app->get('/{{%entity_name%}}/{id}', 'App\Controllers\{{%class_name%}}Controller:getElement');
{{/block:routes}}