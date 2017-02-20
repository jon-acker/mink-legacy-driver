<?php

require_once __DIR__ . '/../../vendor/autoload.php';

if (php_sapi_name() === 'cgi-fcgi') {
    $argv = array();
    foreach ($_GET as $key => $value) {
        if ($value !== '') {
            $value = sprintf('=%s', $value);
        }

        $argv[] = $key . $value;
    }
} else {
    $argv = $_SERVER['argv'];
}

$input = new \Symfony\Component\Console\Input\ArgvInput($argv);

$serializer = new \Jacker\LegacyDriver\Serializer();
$legacyApp = new \Jacker\LegacyDriver\Runner\LegacyApp();

$app = new \Symfony\Component\Console\Application();
$app->add(new \Jacker\LegacyDriver\Runner\RunCommand($serializer, $legacyApp));
$app->run($input);