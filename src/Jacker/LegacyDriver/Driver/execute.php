<?php

// Prepare the containers/repositories/data

$_SERVER = [];
$_SERVER['REQUEST_URI'] = $argv[2];

include $argv[1];

exit(9);