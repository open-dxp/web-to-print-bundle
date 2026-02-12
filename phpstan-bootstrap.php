<?php

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    define('OPENDXP_PROJECT_ROOT', __DIR__);
} elseif (file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
    define('OPENDXP_PROJECT_ROOT', __DIR__ . '/../../..');
} elseif (getenv('OPENDXP_PROJECT_ROOT')) {
    define('OPENDXP_PROJECT_ROOT', getenv('OPENDXP_PROJECT_ROOT'));
} else {
    throw new \Exception('Unknown configuration! OpenDXP project root not found, please set env variable OPENDXP_PROJECT_ROOT.');
}

include OPENDXP_PROJECT_ROOT . '/vendor/autoload.php';
\OpenDxp\Bootstrap::setProjectRoot();
\OpenDxp\Bootstrap::bootstrap();

if (!defined('OPENDXP_TEST')) {
    define('OPENDXP_TEST', true);
}
