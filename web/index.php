<?php

//change from E_all to 0, and on to 0 to avoid sending errormessages with info to hackers
error_reporting(0);
ini_set('display_errors', 0);

if (! extension_loaded('openssl')) {
    die('You must enable the openssl extension.');
}

session_cache_limiter(false);
session_start();

if (preg_match('/\.(?:png|jpg|jpeg|gif|txt|css|js)$/', $_SERVER["REQUEST_URI"]))
    return false; // serve the requested resource as-is.
else {
    $app = require __DIR__ . '/../src/app.php';
    $app->run();
}
