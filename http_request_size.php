<?php

/* This is the script you should use to run this application on the command line
 */

require_once( __DIR__ . '/bootstrap.php' );

new HttpRequestSize\Application($argc, $argv);
