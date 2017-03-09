<?php

namespace App\Services;

class DataService {
    private $opt;
    private $handler;

    function __construct($opts = []) {
        $this->opts = $opts;

        $this->handler = new \PDO($this->opts['dsn'], $this->opts['user'], $this->opts['password'], [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]);
    }

    function count($table, $criteria = '', $criteria_values = []) {
        $sql = $this->select($table, $criteria, 'count(*) AS data_service_row_count_result');
        $obj = $this->fetch($sql, $criteria_values);
        return intval($obj['data_service_row_count_result']);
    }

    function findOne($table, $criteria = '', $criteria_values = []) {
        $sql = $this->select($table, $criteria);
        return $this->fetch($sql, $criteria_values);
    }

    function findAll($table, $criteria = '', $criteria_values = [], $order_by = '', $page_size = 30, $page_number = 1) {
        $sql = $this->select($table, $criteria, '*', $order_by, $page_size, $page_number);
        return $this->fetchAll($sql, $criteria_values);
    }

    private function select($from, $criteria = '', $what = '*', $order_by = '', $page_size = 30, $page_number = 1) {
        if (trim($criteria) !== '') {
            $criteria = sprintf(' WHERE %s', $criteria);
        }
        if (trim($order_by) !== '') {
            $order_by = sprintf(' ORDER BY %s', $order_by);
        }
        $limit = sprintf(' LIMIT %d OFFSET %d', $page_size, ($page_number - 1) * $page_size);
        $sql = sprintf('SELECT %s FROM %s %s %s %s', $what, $from, $criteria, $order_by, $limit);
        return $sql;
    }

    private function fetch($sql, $criteria_values = []) {
        $stmt = $this->handler->prepare($sql);
        $stmt->execute($criteria_values);
        $obj = $stmt->fetch();
        return $obj;
    }

    private function fetchAll($sql, $criteria_values = []) {
        $stmt = $this->handler->prepare($sql);
        $stmt->execute($criteria_values);
        $obj = $stmt->fetchAll();
        return $obj;
    }
}