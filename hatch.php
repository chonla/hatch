<?php

include "vendor/autoload.php";

$cmd = new Commando\Command();

$cmd->option()
    ->require()
    ->describedAs("Egg name e.g. demo.egg");

$cmd->option('n')
    ->aka('new')
    ->describedAs("When set, a new egg file will be created instead of hatch it.")
    ->boolean();

$egg = $cmd[0];

$hatcher = new App\Services\EggService();

if ($cmd['new']) {
    $hatcher->create($egg);
} else {
    $hatcher->hatch($egg);
}