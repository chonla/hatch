<?php

namespace App\Services;

class EggletService {
    private $basic_type = [
        "short_text" => "VARCHAR(256)",
        "long_text" => "TEXT",
        "date" => "INTEGER",
        "datetime" => "INTEGER",
        "auto" => "INTEGER NOT NULL AUTO_INCREMENT",
    ];
    private $bloat_type = [
        "files" => [
            "@" => [
                "auto" => false,
            ],
            "id" => "INTEGER",
            "$$" => "VARCHAR(32)"
        ],
        "tags" => [
            "@" => [
                "auto" => false,
            ],
            "id" => "INTEGER",
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
                    $bloat[sprintf("%s_%s", $tab, $name)] = $this->bloat($name, $type);
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

    private function bloat($bloat_name, $bloat_type) {
        $structure = $this->bloat_type[$bloat_type];
        $out = [];
        foreach ($structure as $name => $type) {
            if ($name === "$$") {
                $out[$bloat_name] = $type;
            } else {
                $out[$name] = $type;
            }
        }
        return $out;
    }

    public function add_auto_fields($structure) {
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

    private function is_bloat_type($type) {
        return (array_key_exists($type, $this->bloat_type));
    }

    public function set_db_type($dbtype) {
        $this->basic_type = array_merge($this->basic_type, $this->db_dep_fields[$dbtype]);
    }
}