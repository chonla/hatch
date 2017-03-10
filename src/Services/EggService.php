<?php

namespace App\Services;

class EggService {
    function __construct() {
        $this->c = new \Colors\Color();
        $this->c->setUserStyles(
            [
                "err" => ["red", "bold"],
                "label" => ["green", "bold"],
                "default" => ["white"],
            ]
        );
    }

    function hatch($eggfile) {
        $this->text("Hatching", $eggfile);

        $decoder = new \Webmozart\Json\JsonDecoder();
        $decoder->setObjectDecoding(\Webmozart\Json\JsonDecoder::ASSOC_ARRAY);
        
        $egg = $decoder->decodeFile($eggfile);

        // Create temp folder
        $temp_path = dirname(dirname(__DIR__)) . '/.tmp';
        if (!is_dir($temp_path)) {
            @mkdir($temp_path, 0777, true);
        }

        // Create compiled output
        $this->text("Hatching", "Setting up compiled space.");
        $compiled_path = array_key_exists("compiled", $egg)?$egg["compiled"]:"./compiled";
        if ($compiled_path == "") {
            $compiled_path = "./";
        }
        if (!is_dir($compiled_path)) {
            @mkdir($compiled_path, 0777, true);
        }
        chdir($compiled_path);

        // Create Egglets
        if (!array_key_exists("database", $egg) || !array_key_exists("dsn", $egg["database"]) || $egg["database"]["dsn"] === "") {
            $this->err("DSN is required to create Egglet, but no DSN is specified.");
            return;
        }

        $egglet = new EggletService();
        list($dbtype, $dummy) = explode(":", $egg["database"]["dsn"]);
        $egglet->set_db_type($dbtype);

        $entities = [];
        foreach ($egg["entities"] as $name => $entity) {
            $result = $egglet->compile($name, $entity);
            for ($i = 0, $n = count($result); $i < $n; $i++) {
                $entities[] = $result[$i];
            }
        }

        $egglet_count = count($entities);
        if ($egglet_count === 0) {
            $this->err("Nothing to be hatched?");
            return;
        }

        $this->text("Hatching", sprintf("Incubating %s egglet%s.", $egglet_count, $egglet_count==1?"":"s"));

        if (!$this->incubate($egg["database"]["dsn"], $entities)) {
            $this->err("Unable to create tables to database.");
            return;
        }

        // Create composer file
        $this->text("Hatching", "Templating composer.");

        $proj_name = array_key_exists("name", $egg)?$egg["name"]:"My App";
        $proj_description = array_key_exists("description", $egg)?$egg["description"]:"My App generated from Hatch.";
        $proj = [
            "name" => $proj_name,
            "description" => $proj_description,
            "autoload" => [
                "psr-4" => [
                    "App\\"=> "src",
                ],
            ],
            "require" => [
                "slim/slim" => "^3.0",
                "tuupola/cors-middleware" => "^0.5",
            ],
        ];

        $encoder = new \Webmozart\Json\JsonEncoder();
        $encoder->setPrettyPrinting(true);
        $encoder->setEscapeSlash(false);

        $encoder->encodeFile($proj, "./composer.json");

        $this->text("Hatching", "Ensuring the latest version of composer is ready.");
        $composer = new ComposerService();
        $composer->set_path($temp_path);
        if (!$composer->ensure_available()) {
            $this->err("Unable to download composer.");
            return;
        }
        if (!$composer->install_composer()) {
            $this->err("Unable to install composer.");
            return;
        }
        if (!$composer->install_deps()) {
            $this->err("Unable to install dependencies. Please go to output folder and run \"composer install\" manually.");
            return;
        }

        if (!$composer->update_deps()) {
            $this->err("Unable to update dependencies. Please go to output folder and run \"composer install\" manually.");
            return;
        }

        // Template application
        $this->text("Hatching", "Scaffolding application.");
        $db_user = array_key_exists("user", $egg["database"])?$egg["database"]["user"]:"";
        $db_password = array_key_exists("password", $egg["database"])?$egg["database"]["password"]:"";

        $cors_origin = ["*"];
        $cors_methods = ["GET", "POST", "PUT", "PATCH", "DELETE"];
        $cors_headers_allow = [];
        $cors_headers_expose = [];
        $cors_credentials = false;
        $cors_cache = 0;
        if (array_key_exists("cors", $egg)) {
            $cors_origin = array_key_exists("origin", $egg["cors"])?$egg["cors"]["origin"]:["*"];
            $cors_methods = array_key_exists("methods", $egg["cors"])?$egg["cors"]["methods"]:["GET", "POST", "PUT", "PATCH", "DELETE"];
            $cors_headers_allow = array_key_exists("headers.allow", $egg["cors"])?$egg["cors"]["headers.allow"]:[];
            $cors_headers_expose = array_key_exists("headers.expose", $egg["cors"])?$egg["cors"]["headers.expose"]:[];
            $cors_credentials = array_key_exists("credentials", $egg["cors"])?$egg["cors"]["credentials"]:false;
            $cors_cache = array_key_exists("cache", $egg["cors"])?$egg["cors"]["cache"]:0;
        }

        $a = new AppService("../data", [
            "db_dsn" => $egg["database"]["dsn"],
            "db_user" => $db_user,
            "db_password" => $db_password,
            "cors_origin" => $cors_origin,
            "cors_methods" => $cors_methods,
            "cors_headers_allow" => $cors_headers_allow,
            "cors_headers_expose" => $cors_headers_expose,
            "cors_credentials" => $cors_credentials,
            "cors_cache" => $cors_cache,
            "egglet" => $egglet,
        ]);
        $a->scaffold();
        $a->base();
        $a->generate($egg["entities"]);

        // Migration
        if (array_key_exists("migration", $egg)) {
            $this->text("Hatching", "Migrating data");

            $data = [];
            foreach($egg["migration"] as $name => $values) {
                $result = $egglet->prepare_data($name, $egg["entities"][$name], $values);
                if ($result !== FALSE) {
                    for ($i = 0, $n = count($result); $i < $n; $i++) {
                        $data[] = $result[$i];
                    }
                } else {
                    $this->err("Unable to collect data being imported. Data structure may no be compatible with egg schema. Please verify your data and re-hatch.");
                    return;
                }
            }

            $data_count = count($data);
            if ($data_count === 0) {
                $this->text("Hatching", "Nothing to be migrated?");
            } else {
                $this->text("Hatching", sprintf("Importing %s record%s.", $data_count, $data_count==1?"":"s"));

                if (!$this->import_data($egg["database"]["dsn"], $data)) {
                    $this->err("Unable to import data.");
                    return;
                }
            }
        }

        // Done
        $this->text("Hatching", "Done. Chirp chirp!");

        $this->text("Hatching", "Thank you for hatching some eggs. You may copy all content in path ${compiled_path} to your server. Enjoy RESTful API from Hatch!");
    }

    private function incubate($to, $entities) {
        return $this->bulk_execute_sql($to, $entities);
    }

    public function import_data($to, $data) {
        return $this->bulk_execute_sql($to, $data);
    }

    private function bulk_execute_sql($to, $sqls) {
        try {
            // Create path for sqlite if need.
            if (preg_match('/^sqlite:(.+)$/', $to, $match)) {
                $sqlite = $match[1];
                if (!file_exists($sqlite)) {
                    $dir = dirname($sqlite);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0777, true);
                    }
                }
            }

            // Spin things up
            $db = new \PDO($to);
            $lastid = 0;

            foreach ($sqls as $sql) {
                if ($sql[0] === "\t") {
                    $sql = preg_replace("/\{\{%last_id%\}\}/", $lastid, $sql, -1);
                    $db->query($sql);
                } else {
                    $db->query($sql);
                    $lastid = $db->lastInsertId();
                }
            }
        } catch(PDOException $e) {
            $this->err($e);
            return false;
        }
        return true;
    }

    private function text($label, $msg) {
        $this->outln("<label>${label} ></label> <default>${msg}</default>");
    }

    private function err($msg) {
        $this->outln("<err>Error ></err> <default>${msg}</default>");
    }

    private function outln($msg) {
        echo $this->c->colorize($msg) . PHP_EOL;
    }
}