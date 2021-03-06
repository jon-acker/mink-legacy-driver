<?php

$path = __DIR__;
while ($path !== '/') {
    if (file_exists($path . '/autoload.php')) {
        $composer = require_once $path . '/autoload.php';
        break;
    }

    if (file_exists($path . '/vendor/autoload.php')) {
        $composer = require_once $path . '/vendor/autoload.php';
        break;
    }

    $path = dirname($path);
}

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

$serializer = new \carlosV2\LegacyDriver\Serializer();

$app = new \Symfony\Component\Console\Application();
$app->add(new \carlosV2\LegacyDriver\Runner\RunCommand($serializer));
$app->run($input);
