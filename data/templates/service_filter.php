<?php

namespace App\Services;

class FilterService {
    function __construct($opts = []) {
        $this->opts = $opts;
    }

    function apply($filter, $value) {
        switch ($filter) {
            case 'datetime':
                return date(\DateTime::ISO8601, $value);
            case 'date':
                return date('Y-m-d', $value);
            default:
                return $value;
        }
    }
}