<?php
require_once __DIR__ . "/includes/extensions/adodb5/adodb.inc.php";
define("BASE_NAMESPACE", "FakeLaravel");
function autoload($className)
{
    $filename = "includes" . DIRECTORY_SEPARATOR;
    if ($rpos = strrpos($className, "\\")) {
        $namespace = substr($className, 0, $rpos);
        if (strpos($namespace, BASE_NAMESPACE) == 0) {
            $namespace = substr($namespace, strlen(BASE_NAMESPACE) + 1);
        }
        $filename .= str_replace("\\", DIRECTORY_SEPARATOR, $namespace);
        $filename .= DIRECTORY_SEPARATOR;
        $className = substr($className, $rpos + 1);
    }
    $filename .= $className . ".php";
    require_once $filename;
}

spl_autoload_register('autoload');
