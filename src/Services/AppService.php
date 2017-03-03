<?php

namespace App\Services;

class AppService {
    private $path;
    private $options;

    function __construct($resource, $options = []) {
        $this->path = $resource;
        $this->options = $options;
    }

    function scaffold() {
        @mkdir("src/Controllers", 0755, true);
    }

    function base() {
        copy(sprintf("%s/templates/controller_base.php", $this->path), "src/Controllers/ControllerBase.php");
        copy(sprintf("%s/templates/index.php", $this->path), "index.php");
        copy(sprintf("%s/templates/middlewares.php", $this->path), "src/middlewares.php");
        copy(sprintf("%s/templates/dependencies.php", $this->path), "src/dependencies.php");

        $settings_vars = [
            "dsn" => $this->options["dsn"],
        ];
        $this->create_file_from_template(sprintf("%s/templates/settings.php", $this->path), $settings_vars, "src/settings.php");
    }

    function generate($structure) {
        $vars = [];
        foreach ($structure as $name => $entity) {
            $this->create_controllers_from_template($name, $entity);

            $class = $this->safe_class_name($name);
            $entity_name = $name;

            $vars[] = [
                "class_name" => $class,
                "entity_name" => $entity_name,
            ];
        }

        $this->insert_to_template(sprintf("%s/templates/routes.php", $this->path), $vars, "src/routes.php");
    }

    private function insert_to_template($file, $vars, $saveto) {
        $content = file_get_contents($file);
        preg_match_all('@\{\{block:([a-zA-Z0-9_]+)\}\}(.+)\{\{/block:\\1\}\}@s', $content, $matches, PREG_SET_ORDER);

        for ($i = 0, $n = count($matches); $i < $n; $i++) {
            $out = [];
            for ($j = 0, $m = count($vars); $j < $m; $j++) {
                $out[] = $this->apply_vars($matches[$i][2], $vars[$j]);
            }
            $content = str_replace($matches[$i], implode("", $out), $content);
        }

        file_put_contents($saveto, $content);
    }

    private function create_controllers_from_template($name, $structure) {
        $class = $this->safe_class_name($name);
        $filename = sprintf("src/Controllers/%sController.php", $class);
        $template = sprintf("%s/templates/controller_template.php", $this->path);
        
        $vars = [
            "class_name" => $class,
            "entity_name" => $name,
        ];

        $this->create_file_from_template($template, $vars, $filename);

    }

    private function create_file_from_template($template, $vars, $saveto) {
        $content = file_get_contents($template);

        $content = $this->apply_vars($content, $vars);

        file_put_contents($saveto, $content);
    }

    private function apply_vars($content, $vars) {
        foreach ($vars as $k => $v) {
            $content = preg_replace("/\{\{%$k%\}\}/", $v, $content, -1);
        }

        return $content;
    }

    private function safe_class_name($name) {
        $class = implode("", array_map("ucfirst", explode("_", $name)));
        return $class;
    }
}