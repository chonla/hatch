<?php

namespace App\Controllers;

class ControllerBase {
    protected $table;
    protected $relative_list;
    private $relative_list_count;

    function __construct(\Slim\Container $ci) {
        $this->ci = $ci;
        $this->conf = $this->ci->get('settings')['restful'];
        $this->relative_list_count = count($this->relative_list);
    }

    private function expand($element) {
        $R = $this->ci->get('r');

        // expand record with relative table
        for ($i = 0; $i < $this->relative_list_count; $i++) {
            $ex = $R->find(sprintf("%s_%s", $this->table, $this->relative_list[$i]), sprintf('%s_id=:id', $this->table), [':id'=>$element->id]);
            $element[$this->relative_list[$i]] = $this->flat($ex, $this->relative_list[$i]);
        }

        return $element;
    }

    private function flat($arr, $field_name) {
        $out = [];
        foreach ($arr as $obj) {
            $out[] = $obj[$field_name];
        }
        return $out;
    }

    public function getElement($request, $response, $args) {
        $R = $this->ci->get('r');

        $id = $args['id'];
        $element = $R->findOne($this->table, 'id=:id', [':id'=>$id]);

        if (count($this->relative_list) > 0) {
            $element = $this->expand($element);
        }

        return $response->withJson($element);
    }

    public function getCollection($request, $response, $args) {
        $R = $this->ci->get('r');

        $current_page = $request->getQueryParam('page', '1');
        if (!preg_match('/^\d+$/', $current_page)) {
            $current_page = '1';
        }

        $current_page = intVal($current_page);
        $count = $R->count($this->table);
        $page_size = $this->conf['page_size'];
        $page_count = 1;
        if ($count > 0) {
            $page_count = ceil($count / $page_size);
        }
        $from_row = (($current_page - 1) * $page_size);
        $api_url = strtok($request->getUri()->__toString(), '?');

        $all = array_values($R->findAll($this->table, sprintf(' ORDER BY id LIMIT %d OFFSET %d ', $page_size, $from_row)));

        $attributes = [
            'row_count' => $count,
            'page_count' => $page_count,
            'current_page' => $current_page,
            'page_size' => $page_size,
            'first_page' => sprintf('%s?page=%d', $api_url, '1'),
            'last_page' => sprintf('%s?page=%d', $api_url, $page_count),
        ];

        if ($current_page < $page_count) {
            $next_page = $current_page + 1;
            $attributes['next_page'] = sprintf('%s?page=%d', $api_url, $next_page);
        }

        if ($current_page > 1) {
            $previous_page = $current_page - 1;
            $attributes['previous_page'] = sprintf('%s?page=%d', $api_url, $previous_page);;
        }

        return $response->withJson([
            '_attributes' => $attributes,
            'results' => $all,
        ]);
    }
}