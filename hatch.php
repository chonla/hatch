<?php

include "vendor/autoload.php";

$cmd = new Commando\Command();

$cmd->option()
    ->require()
    ->file()
    ->describedAs("Egg name e.g. demo.egg");

$egg = $cmd[0];

$hatcher = new App\Services\EggService();
$hatcher->hatch($egg);
