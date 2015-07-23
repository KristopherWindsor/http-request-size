<?php

namespace HttpRequestSize;

/* This class runs the application: it gets parameters from command line
 * and writes directly to stdOut rather than being extensible
 */
class Application {
  public function __construct($argc, $argv){
    if ($argc != 2)
      die("Usage: php http_request_size.php <url>\n");

    $url = $argv[1];
    if (strpos($url, '://') === false)
      $url = 'http://' . $url;

    try {
      $analyzer = new UrlAnalyzer($url);
      $stats = $analyzer->getStats();
    } catch (Exception $e){
      die("Unexpected error: " . $e->getMessage() . "\n");
    }

    var_dump($stats);
  }
}