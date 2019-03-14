<?php

require __DIR__.'/vendor/autoload.php';

if(empty($_SERVER['DOCUMENT_ROOT'])){
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__);
}
