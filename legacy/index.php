<?php

$uri = $_SERVER['REQUEST_URI'];

switch ($uri) {
    case '/item':
        echo 'Here is your item';
        break;

    case '/login':
        echo 'You are not supposed to be here!';
        exit;

    default:
        echo 'Not found: ' . $uri;
}
