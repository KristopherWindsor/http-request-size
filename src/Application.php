<?php

namespace HttpRequestSize;

/* This class runs the application: it gets parameters from command line
 * and writes directly to stdOut rather than being extensible.
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

    $total_req = 0;
    $total_size = 0;
    foreach ($stats as $stat){
      $total_req += $stat['requests'];
      $total_size += $stat['size'];
    }

    printf( "Processed URL: %s\n\n", $url );

    printf( "Total number of HTTP requests: %d\n", $total_req );
    printf( "Total download size for all requests: %s bytes\n\n", number_format($total_size) );

    foreach (ContentCategorizer::getAllTypes() as $type => $display_name){
      if (isset($stats[$type]))
        printf("%s %d request(s), %s bytes\n",
          substr($display_name . str_repeat('.', 20), 0, 20),
          $stats[$type]['requests'],
          number_format($stats[$type]['size']) );
      else
        printf( "%s 0 requests\n", substr($display_name . str_repeat('.', 20), 0, 20) );
    }
  }
}