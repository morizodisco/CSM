<?php
if (! function_exists('d')) {
    function d($var) {
        $args = func_get_args();
        echo '<pre>';
        var_dump(...$args);
        echo '</pre>';
    }
}

if (! function_exists('hd')) {
    function hd($var) {
        $args = func_get_args();
        echo '<!--<pre>';
        var_dump(...$args);
        echo '</pre>-->';
    }
}
