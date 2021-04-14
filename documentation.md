# HTTP Library *documentation*

The HTTP library has files that facilitate request and response processes, as well as providing classes for handling URLs, file uploads and downloads, as well as Cookies and GeoIP.

## Requests

With the Request class you can get Object Oriented information about headers, URL, and request body.

To use the class just instantiate it:

```php
require __DIR__ . '/../vendor/autoload.php';

use Framework\HTTP\Request;

$request = new Request();
```

That done, we can already get data from the headers:

```php
$method = $request->getMethod(); // GET
$host = $request->getHeader('Host'); // localhost:8080
$secure = $request->isSecure(); // false
$ajax = $request->isAJAX(); // false
```

### Security

If the request is not secure, we can force a redirect using HTTPS:

```php
$request->forceHTTPS(); // redirect and exit
```

### JSON

When working with JSON, there are methods to check the request's `Content-Type` header and get the data already decoded:

```php
if ($request->isJSON()) {
    $data = $request->getJSON();
}
```

### Request with uploads

To get a file, call it by name.

The `getFile` method returns an instance of` UploadedFile` or `null`.

```php
$file = $request->getFile('name');
$file->move('/new/path/name');
```

## Responses

HTTP responses send headers and message body in addition to the code and the reason for the status.

To use the response class, just instantiate it as well:

```php
require __DIR__ . '/../vendor/autoload.php';

use Framework\HTTP\Request;
use Framework\HTTP\Response;

$request = new Request();
$response = new Response($request); // an instance of Request is required
```

A response containing JSON could be set to:

```php
$response->setHeader('content-type', 'application/json');
$response->setBody(json_encode([
    'id' => 1,
    'name' => 'Mary',
]));
$response->send();
```

or simply:

```php
$response->setJSON([
    'id' => 1,
    'name' => 'Mary',
]);
$response->send();
```

#### Response with download

To send a file as a download in the response, you can call:

```php
$response->setDownload('filepath.pdf');
```

With the second parameter set to true the content disposition is `inline`, causing the browser to open the file in the window.

```php
$response->setDownload('filepath.pdf', true);
```

The third parameter makes it possible to continue downloads or start downloading a video at a certain time.

```php
$response->setDownload('filepath.pdf', true, true);
```

## URL
 
The library has a class for working with URLs.
 
```php
use Framework\HTTP\URL;

$url = new URL('http://localhost:8080');
echo $url->getScheme(); // http
echo $url->getHost(); // localhost:8080
$url->setHostname('foo-bar.com');
echo $url->getHost(); // foo-bar.com:8080
$url->setPort(80);
echo $url->getHost(); // foo-bar.com
```
