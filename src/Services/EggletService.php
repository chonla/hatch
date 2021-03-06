<?php

namespace App\Services;

class EggletService {
    private $basic_type = [
        "very_long_text" => "TEXT",
        "long_text" => "VARCHAR(1024)",
        "medium_text" => "VARCHAR(256)",
        "short_text" => "VARCHAR(128)",
        "very_short_text" => "VARCHAR(64)",
        "password" => "VARCHAR(128)",
        "date" => "INTEGER",
        "datetime" => "INTEGER",
        "auto" => "INTEGER NOT NULL AUTO_INCREMENT",
    ];
    private $bloat_type = [
        "files" => [
            "@" => [
                "auto" => false,
            ],
            "id" => "auto",
            "%%_id" => "INTEGER",
            "$$" => "VARCHAR(32)"
        ],
        "tags" => [
            "@" => [
                "auto" => false,
            ],
            "id" => "auto",
            "%%_id" => "INTEGER",
            "$$" => "VARCHAR(50)"
        ],
    ];
    private $auto_fields_pre = [
        "id" => "auto",
    ];
    private $auto_fields_post = [
        "created_time" => "datetime",
        "updated_time" => "datetime"
    ];
    private $db_dep_fields = [
        "mysql" => [
            "auto" => "INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY",
        ],
        "mssql" => [
            "auto" => "INTEGER IDENTITY(1,1) PRIMARY KEY",
        ],
        "sqlite" => [
            "auto" => "INTEGER PRIMARY KEY"
        ]
    ];

    public function meta($tab, $structure) {
        if (!$this->is_auto_off($structure)) {
            $structure = $this->add_auto_fields($structure);
        }

        $out = [
            $tab => [
                'attributes' => [],
                'fields' => [],
            ],
        ];

        foreach ($structure as $k => $v) {
            if ($k === '@') {
                foreach($v as $ka => $va) {
                    $out[$tab]['attributes'][$ka] = $va;
                }
            } else {
                $out[$tab]['fields'][] = [
                    'name' => $k,
                    'type' => $v,
                ];
            }
        }
        return $out;
    }

    public function compile($tab, $structure) {
        if (!$this->is_auto_off($structure)) {
            $structure = $this->add_auto_fields($structure);
        }

        $fields = [];
        $bloat = [];
        foreach ($structure as $name => $type) {
            if ($name !== "@") {
                if ($this->is_basic_type($type)) {
                    $fields[] = sprintf("`%s` %s", $name, $this->basic_type[$type]);
                } elseif ($this->is_bloat_type($type)) {
                    $bloat[sprintf("%s_%s", $tab, $name)] = $this->bloat($tab, $name, $type);
                } else {
                    $fields[] = sprintf("`%s` %s", $name, strtoupper($type));
                }
            }
        }

        $sql = [sprintf("CREATE TABLE `%s`\n(%s)", $tab, implode(",\n", $fields))];

        if (count($bloat) > 0) {
            foreach ($bloat as $bloat_name => $bloat_structure) {
                $result = $this->compile($bloat_name, $bloat_structure);
                for ($i = 0, $n = count($result); $i < $n; $i++) {
                    $sql[] = $result[$i];
                }
            }
        }

        return $sql;
    }

    public function prepare_data($tab, $structure, $values) {
        $auto = false;
        if (!$this->is_auto_off($structure)) {
            $structure = $this->add_auto_fields($structure);
            $auto = true;
        }

        $sql = [];
        for ($i = 0, $n = count($values); $i < $n; $i++) {
            $fields = [];
            $data_values = [];
            $bloat = [];
            foreach ($values[$i] as $k => $v) {
                if (array_key_exists($k, $structure)) {
                    if ($structure[$k] === "password") {
                        $fields[] = $k;
                        $data_values[] = password_hash($v, PASSWORD_BCRYPT);
                    } elseif ($this->is_bloat_type($structure[$k])) {
                        $bloat = array_merge($bloat, $this->bloat_data($tab, $k, $v));
                    } elseif ($structure[$k] !== "auto") {
                        $fields[] = $k;
                        $data_values[] = $v;
                    }
                } else {
                    return FALSE;
                }
            }
            if ($auto) {
                $now = time();
                if (!array_key_exists("created_time", $values[$i])) {
                    $fields[] = 'created_time';
                    $data_values[] = $now;
                }
                if (!array_key_exists("updated_time", $values[$i])) {
                    $fields[] = 'updated_time';
                    $data_values[] = $now;
                }
            }
            $sql[] = sprintf("INSERT INTO `%s` (`%s`) VALUES ('%s')", $tab, implode("`,`", $fields), implode("','", $data_values));

            if (count($bloat) > 0) {
                for ($j = 0, $m = count($bloat); $j < $m; $j++) {
                    $sql[] = "\t" . $bloat[$j];
                }
            }
        }

        return $sql;
    }

    private function bloat_data($base, $child, $data) {
        $tab = sprintf("%s_%s", $base, $child);

        $out = [];
        foreach ($data as $v) {
            $out[] = sprintf("INSERT INTO `%s` (`%s_id`, `%s`) VALUES ('{{%%last_id%%}}', '%s')", $tab, $base, $child, $v);
        }

        return $out;
    }

    private function bloat($base, $bloat_name, $bloat_type) {
        $structure = $this->bloat_type[$bloat_type];
        $out = [];
        foreach ($structure as $name => $type) {
            if ($name === "$$") {
                $out[$bloat_name] = $type;
            } else {
                $new_name = str_replace("%%", $base, $name);
                $out[$new_name] = $type;
            }
        }
        return $out;
    }

    public function add_auto_fields($structure) {
        if ($this->is_auto_off($structure)) {
            return $structure;
        }
        return array_merge(array_merge($this->auto_fields_pre, $structure), $this->auto_fields_post);
    }

    private function is_auto_off($structure) {
        if (array_key_exists("@", $structure) && array_key_exists("auto", $structure["@"])) {
            return $structure["@"]["auto"] === false;
        }
        return false;
    }

    private function is_basic_type($type) {
        return (array_key_exists($type, $this->basic_type));
    }

    public function is_bloat_type($type) {
        return (array_key_exists($type, $this->bloat_type));
    }

    public function set_db_type($dbtype) {
        $this->basic_type = array_merge($this->basic_type, $this->db_dep_fields[$dbtype]);
    }
}