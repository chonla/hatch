<?php

namespace App\Controllers;

class {{%class_name%}}Controller extends ControllerBase {

    function __construct(\Slim\Container $ci) {
        $this->table = "{{%entity_name%}}";
        $this->relative_list = [{{%relative_list%}}];
        $this->filter = [{{%filter_list%}}];
        $this->private_list = [{{%private_list%}}];
        $this->sort_by = "{{%sort_by%}}";
        parent::__construct($ci);
    }

}