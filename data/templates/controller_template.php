<?php

namespace App\Controllers;

class {{%class_name%}}Controller extends ControllerBase {

    function __construct(\Slim\Container $ci) {
        $this->table = "{{%entity_name%}}";
        $this->relative_list = [{{%relative_list%}}];
        parent::__construct($ci);
    }

}