<?php 

require_once __DIR__ . '/../vendor/autoload.php'; 

use Symfony\Component\Console\Application; 
use Console\App;

$app = new Application('Reddit Search', 'v0.1.0');

$app -> add(new TimeCommand());

$app -> run();
