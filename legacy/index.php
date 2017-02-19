<?php

$uri = $_SERVER['REQUEST_URI'];

switch ($uri) {
    case '/form': ?>
<form action="/form-submit" method="post">
    <input name="name" type="text" />
    <input name="surname" type="text" />
    <input type="submit" name="send" />
</form>
<?php
        break;

    case '/form-submit':
        echo 'Your name is ' . $_POST['name'] . ' ' . $_POST['surname'];
        break;

    case '/login':
        echo 'You are not supposed to be here!';
        exit;

    default:
        echo 'Not found: ' . $uri;
}
