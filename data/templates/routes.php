<?php

{{block:routes}}
$app->any('/{{%entity_name%}}', 'App\Controllers\{{%class_name%}}Controller:getCollection');
{{/block:routes}}