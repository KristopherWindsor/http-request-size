<?php

namespace HttpRequestSize;

class ContentCategorizer {
  const HTML  = 'HTML';
  const CSS   = 'CSS';
  const JS    = 'JS';
  const ASSET = 'ASSET';

  /* Categorize a web resource based on its Content-Type.
   * This logic is heuristical as we do not list out all of the Content-Types for each category.
   * The categories are arbitrary.
   */
  public static function getCategory($content_type){
    $content_type = strtolower($content_type);

    if (strpos($content_type, 'javascript') !== false)
      return self::JS;
    if (strpos($content_type, 'html') !== false)
      return self::HTML;
    if ($content_type == 'text/css')
      return self::CSS;

    return self::ASSET;
  }

}
