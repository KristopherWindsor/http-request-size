# http-request-size
This command-line tool reports the total download size of a given web page (including dependencies) or of a single web resource.

## purpose
This is an example of my coding style and design.

## install
````
git clone <url>
composer install
````

## usage
````
php http_request_size.php kristopherwindsor.com
````

## outstanding issues
* Add composer package description so this can be used as a third-party library
* Should have unit tests!
* Refactoring: add a class for the stats returned by UrlAnalyzer
* HTTP redirects are followed, but "effective URLs" are not updated on redirect.
  Doing this is awkward with Guzzle as they removed that functionality after v3.x.
  There is an open ticket related to this:
  https://github.com/guzzle/guzzle/issues/1166
