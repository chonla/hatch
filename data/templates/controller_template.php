<?php

namespace App\Controllers;

class {{%class_name%}}Controller extends ControllerBase {

    function __construct(\Slim\Container $ci) {
        $this->table = "{{%entity_name%}}";
        parent::__construct($ci);
    }

}