<?php
namespace Parser;

require 'Classes/Parser.php';

$filePathFromConsole = $_SERVER['argv'];
if(isset($filePathFromConsole))
{
    $analytics = new Analysis($filePathFromConsole[1]);
    $analytics->loadingLogDataFromFile();
    $result = $analytics->collectingResult();

    echo(json_encode($result, JSON_PRETTY_PRINT));
}