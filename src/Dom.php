<?php

namespace HttpRequestSize;

/* Changes the third-party Dom class to not strip out <script> tags in clean().
 */
class Dom extends \PHPHtmlParser\Dom {
  protected function clean($str)
    {
      // clean out the \n\r
      $str = str_replace(["\r\n", "\r", "\n"], ' ', $str);

      // strip the doctype
      $str = preg_replace("'<!doctype(.*?)>'is", '', $str);

      // strip out comments
      $str = preg_replace("'<!--(.*?)-->'is", '', $str);

      // strip out cdata
      $str = preg_replace("'<!\[CDATA\[(.*?)\]\]>'is", '', $str);

      // strip out <style> tags
      $str = preg_replace("'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is", '', $str);
      $str = preg_replace("'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is", '', $str);

      // strip out preformatted tags
      $str = preg_replace("'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is", '', $str);

      // strip out server side scripts
      $str = preg_replace("'(<\?)(.*?)(\?>)'s", '', $str);

      // strip smarty scripts
      $str = preg_replace("'(\{\w)(.*?)(\})'s", '', $str);

      return $str;
    }
}