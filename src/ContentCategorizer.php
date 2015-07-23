<?php

namespace HttpRequestSize;

class ContentCategorizer {
  const ASSET = 'ASSET';
  const CSS   = 'CSS';
  const HTML  = 'HTML';
  const IMAGE = 'IMAGE';
  const JS    = 'JS';

  /* Categorize a web resource based on its Content-Type.
   * This logic is heuristical as we do not list out all of the Content-Types for each category.
   * The categories are arbitrary.
   */
  public static function getCategory($content_type){
    $content_type = strtolower($content_type);

    if ($content_type == 'text/css')
      return self::CSS;
    if (strpos($content_type, 'javascript') !== false)
      return self::JS;
    if (strpos($content_type, 'html') !== false)
      return self::HTML;
    if (strpos($content_type, 'image') !== false)
      return self::IMAGE;

    return self::ASSET;
  }

  public static function getAllTypes(){
    return [
      self::HTML  => 'Web pages (HTML)',
      self::IMAGE => 'Images',
      self::CSS   => 'CSS',
      self::JS    => 'Javascript',
      self::ASSET => 'Other assets',
    ];
  }
}
