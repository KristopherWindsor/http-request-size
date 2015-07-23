<?php

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
  require_once( __DIR__ . '/vendor/autoload.php' );
} else {
  throw new \Exception("Can't find autoload.php");
}

$x = new HttpRequestSize\UrlAnalyzer('http://imgur.com/');
var_dump( $x->getStats() );
echo 'done';