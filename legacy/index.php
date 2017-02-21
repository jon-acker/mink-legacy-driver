<?php

use Jacker\LegacyDriver\Legacy\Outputer;

$outputer = new Outputer();

$uri = $_SERVER['REQUEST_URI'];

switch ($uri) {
    case '/form':
        $outputer->render(
            '<form action="/form-submit" method="post">' .
                '<input name="name" type="text" />' .
                '<input name="surname" type="text" />' .
                '<input type="submit" name="send" />' .
            '</form>',
            array()
        );
        break;

    case '/form-submit':
        $outputer->render(
            'Your name is {NAME} {SURNAME} and the env variable is {ENV}',
            array(
                '{NAME}' => $_POST['name'],
                '{SURNAME}' => $_POST['surname'],
                '{ENV}' => getenv('abc')
            )
        );
        break;

    case '/login':
        echo 'You are not supposed to be here!';
        exit;

    default:
        echo 'Not found: ' . $uri;
}
