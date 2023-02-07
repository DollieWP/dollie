<?php

$root_dir = str_replace('wp-content/plugins/dollie/core', '', __DIR__ );
// Require the wp-load.php file (which loads wp-config.php and bootstraps WordPress)
require $root_dir . '/wp-load.php';

//check authorization
\Dollie\Core\Services\HubDataService::instance()->check_incoming_auth();

header('Content-Type: application/json; charset=utf-8');
echo json_encode( \Dollie\Core\Services\HubDataService::instance()->get() );
