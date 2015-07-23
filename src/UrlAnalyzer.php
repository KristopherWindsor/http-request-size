<?php

namespace HttpRequestSize;

class UrlAnalyzer {

  private $guzzle_client;

  private $all_visited = [];          // all urls visited via HEAD request
  private $all_visited_complete = []; // all urls visited via GET  request

  private $promises = [];
  private $promises_total = 0;

  private $stats = null;
  private $stats_recorded = []; // all urls for which stats have been recorded

  public function __construct($url){
    $this->guzzle_client = new \GuzzleHttp\Client();
    $this->queueUrl($url);
  }

  /* Add to the queue of urls to visit via HEAD request
   */
  private function queueUrl($url){
    if (isset($this->all_visited[$url]))
      return;
    $this->all_visited[$url] = true;
    $this->promises[$this->promises_total++] = [$url, $this->guzzle_client->headAsync($url)];
  }

  /* Add to the queue of urls to visit via GET request
   */
  private function queueCompleteUrl($url){
    if (empty($this->all_visited[$url]))
      throw new Exception('Please make HEAD request before GET request');
    if (isset($this->all_visited_complete[$url]))
      return;

    $this->all_visited_complete[$url] = true;
    $this->promises[$this->promises_total++] = [$url, $this->guzzle_client->getAsync($url)];
  }

  /* Process (hit) all urls in the queue.
   * If we find web pages in the queue, we will add their dependencies to the queue, so they will be processed too.
   */
  private function processQueue(){
    for ($i = 0; $i < $this->promises_total; $i++){
      list($url, $promise) = $this->promises[$i];
      $response = $promise->wait();

      if ($response->hasHeader('Content-Type'))
        $content_type = $response->getHeader('Content-Type')[0];
      else
        $content_type = null;
      $category = ContentCategorizer::getCategory( $content_type );
      // have we requested the entire resource or just the headers?
      $is_complete_request = (isset($this->all_visited_complete[$url]));

      // for web pages, find references to other assets
      if ($category == ContentCategorizer::HTML){
        if ($is_complete_request){
          // right here we need to load the whole html / response body into a variable
          // this may use a lot of memory; there is no easy way around that
          $this->findUrlsInHtml( $url, (string) $response->getBody() );
        } else
          $this->queueCompleteUrl($url);
      }

      // example url, record stats
      if ( !$is_complete_request && $response->hasHeader('Content-Length') ){
        // record size based on header
        $this->recordStats( $url, $category, $response->getHeader('Content-Length')[0] );
      } else if (!$is_complete_request){
        // request body to see how big it is (if no content length -- last resort)
        $this->queueCompleteUrl($url);
      } else {
        // examine body to see how big it is
        $this->recordStats( $url, $category, $response->getBody()->getSize() );
      }
    }
    $this->promises = [];
  }

  private function findUrlsInHtml($url, $html){
    if (!$html)
      return;

    $dom = new Dom();
    $dom->load($html);
    $elems = $dom->find('html');

    foreach ($elems as $i){
      $this->findUrlsInNode($url, $i);
    }
  }

  private function findUrlsInNode($url, $node){
    // recursively process children and siblings
    try {
      $this->findUrlsInNode($url, $node->nextSibling());
    } catch (\PHPHtmlParser\Exceptions\ChildNotFoundException $e) {
    }
    if ( $node->hasChildren() ){
      $this->findUrlsInNode($url, $node->firstChild());
    }

    // get properties of this node
    $tag_name = $node->getTag()->name();
    $attributes = $node->getTag()->getAttributes();

    // note: we do not include object, embed, audio, or video
    // because those resources are not typically not loaded automatically with the web page

    if ($tag_name == 'iframe'){
      // iframes
      $this->reportFoundUrl($url, @$attributes['src']['value']);
    } else if ($tag_name == 'script'){
      // js references
      $this->reportFoundUrl($url, @$attributes['src']['value']);
    } else if ($tag_name == 'link'){
      // css, favicon, etc references
      // alternates are not dependencies
      $rel = @$attributes['rel']['value'];
      if ($rel != 'alternate')
        $this->reportFoundUrl($url, @$attributes['href']['value']);
    } else if ($tag_name == 'image'){
      // images in the web page
      $this->reportFoundUrl($url, @$attributes['src']['value']);
    }
  }

  /* This handles the case where an asset reference is found in the dom for a web page.
   * It needs to resolve relative urls and add the url to the queue
   */
  private function reportFoundUrl($base_url, $relative){
    // skip in case the src/href attribute is missing
    if (!$relative)
      return;

    $resolver = new \Net_URL2($base_url);
    $absolute_url = (string) $resolver->resolve($relative);
    $this->queueUrl($absolute_url);
  }

  private function recordStats($url, $category, $size){
    if (isset($this->stats_recorded[$url]))
      return;
    $this->stats_recorded[$url] = true;

    if (empty( $this->stats[$category] ))
      $this->stats[$category] = ['requests' => 0, 'size' => 0];

    $this->stats[$category]['requests'] ++;
    $this->stats[$category]['size'] += $size;
  }

  public function getStats(){
    $this->processQueue();
    return $this->stats;
  }
}