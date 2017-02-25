<?php

namespace carlosV2\LegacyDriver\Exception;

use RuntimeException;

final class PhpCgiExecutableNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Unable to found the `php-cgi` binay.');
    }
}
