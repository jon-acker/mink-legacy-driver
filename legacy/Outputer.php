<?php

namespace Jacker\LegacyDriver\Legacy;

class Outputer
{
    /**
     * @param string $template
     * @param array  $params
     */
    public function render($template, array $params)
    {
        echo str_replace(array_keys($params), array_values($params), $template);
    }
}
