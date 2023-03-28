HTTP
====

.. image:: image.png
    :alt: Aplus Framework HTTP Library

Aplus Framework HTTP (HyperText Transfer Protocol) Library.

- `Installation`_
- `Request`_
- `Response`_
- `URL`_
- `AntiCSRF`_

Installation
------------

The installation of this library can be done with Composer:

.. code-block::

    composer require aplus/http

Request
-------

With the Request class you can get Object Oriented information about the
requested Protocol, URL, Headers and Body.

Create a PHP file (**users.php**) with the following contents:

.. code-block:: php

    <?php
    require __DIR__ . '/vendor/autoload.php';

    use Framework\HTTP\Request;

    $request = new Request();

**Testing:**

Add the following lines to test get Request information:

.. code-block:: php

    var_dump([
        'method' => $request->getMethod(),
        'host' => $request->getHeader('Host'),
        'isSecure' => $request->isSecure(),
        'isAjax' => $request->isAjax(),
        'url' => $request->getUrl()->getAsString(),
        'userAgent' => $request->getUserAgent()->getAsString(),
    ]);

Now, lets try a call with Curl:

.. code-block::

    curl http://localhost:8080/users.php?page=1

Curl will connect to the server with an HTTP Message like this:

.. code-block:: http

    GET /users.php?page=1 HTTP/1.1
    Host: localhost:8080
    User-Agent: curl/7.68.0
    Accept: */*
    
In the PHP script, the Request class will be instantiated and the array will be set and dumped.

The Curl response body will be like this:

.. code-block::

    array(6) {
      ["method"]=>
      string(3) "GET"
      ["host"]=>
      string(14) "localhost:8080"
      ["isSecure"]=>
      bool(false)
      ["isAjax"]=>
      bool(false)
      ["url"]=>
      string(38) "http://localhost:8080/users.php?page=1"
      ["userAgent"]=>
      string(11) "curl/7.68.0"
    }

Secure Request
##############

To make sure that the application will only respond to secure requests, you can
force the request to be over HTTPS and also that it is responding only on
allowed hosts.

Force HTTPS
^^^^^^^^^^^

If the request is not secure, we can force a redirect using HTTPS:

.. code-block:: php

    $request->forceHttps(); // void

This method checks if the request scheme is HTTPS.

And only if is not, it set headers and status to redirect to the HTTPS version of the URL
and terminate the script.

Allowed Hosts
^^^^^^^^^^^^^

If, for some unknown reason, the virtual host is incorrectly configured on the
server, it is possible to prevent unwanted access by whitelisting the allowed hosts.

See this example using nginx:

.. code-block:: nginx

    root /var/www/app/public;
    server_name domain.tld api.domain.tld other.tld;

A Company requires only *domain.tld* and *api.domain.tld* to work,
but one added the *other.tld* to the list of server_names.
Nginx will respond to this host accessing the application public folder.

To prevent that, whitelist the allowed hosts. Set it on the Request constructor:

.. code-block:: php

    $allowedHosts = ['domain.tld', 'domain.tld:8088', 'api.domain.tld'];
    $request = new Request($allowedHosts);

When a request for an unwanted host is done, an ``UnexpectedValueException``
will thrown, with the message "Invalid Host: other.tld".

With the throwable is possible, for example, to *catch* the Exception message
and log it.

If the $allowedHosts argument is not set, the Request will accept any host.

Content Negotiation
###################

It is also in the request that information is acquired for
`Content Negotiation <https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation>`_. 
Knowing what the HTTP Client accepts, and prioritizes, it is possible to
generate a more complete and featured `Response`_ for each user.

The Request class has methods for negotiating content.

In them it is possible to pass the values available by the application.

Let's look at an example negotiating the value of the headers, Content-Type and
Content-Language, which can be used in the Response:

.. code-block:: php

    $availableTypes = ['text/html', 'application/xml'];
    $negotiatedType = $request->negotiateAccept($availableTypes);

    $availableLanguages = ['en', 'es', 'pt-br'];
    $negotiatedLanguage = $request->negotiateLanguage($availableLanguages); // string

The negotiation takes the `Quality Values <https://developer.mozilla.org/en-US/docs/Glossary/Quality_values>`_
from the header in order of priority and returns the first one in the list of 
those accepted by the application. 
If none of the Quality Values are available in the application, the value
returned is the first of the array of available.

Anyway, it is now possible to set the headers negotiated in the Response:

.. code-block:: php

    $response->setHeader('Content-Type', $negotiatedType); // static
    $response->setHeader('Content-Language', $negotiatedLanguage); // static

Request with JSON
#################

When working with JSON, has a method to check if the ``Content-Type`` is of JSON
type.

And also, a method to get the JSON data from the Request body:

.. code-block:: php

    if ($request->isJson()) {
        $data = $request->getJson(); // object or false
    }

Request with Uploads
####################

When the request is done via the POST method and has ``multipart/form-data`` as
Content-Type, it characterizes the upload of files.

The Request class has methods to work with uploaded files.

The ``getFile`` method returns an ``UploadedFile`` instance or ``null``.

.. code-block:: php

    $file = $request->getFile('fieldName'); // UploadedFile or null
    if ($file && $file->isValid()) {
        $filename = 'rand0m' . $file->getExtension();
        $filepath = '/var/www/app/uploads/' . $filename;
        $moved = $file->move($filepath); // bool
    }

Request with Authorization
##########################

To identify the type of authorization received, you can use the ``getAuthType``
method. Which will return ``null`` if there is none, ``Basic``, ``Bearer`` or
``Digest``:

.. code-block:: php

    $request->getAuthType(); // string or null

Using the ``getBasicAuth`` method, we obtain an array containing two keys,
``username`` and ``password`` or null:

.. code-block:: php

    $request->getBasicAuth(); // array or null

Using the ``getBearerAuth`` method, we obtain an array containing one key,
``token`` or null:

.. code-block:: php

    $request->getBearerAuth(); // array or null

Using the ``getDigestAuth`` method, an array with 9 keys is obtained:
``username``, ``realm``, ``nonce``, ``uri``, ``response``, ``opaque``,
``qop``, ``nc`` and ``cnonce``.

.. code-block:: php

    $request->getDigestAuth(); // array or null

Request working with REST
#########################

The Request class has methods that work very well with REST APIs.

With the ``getMethod`` method, we get the HTTP method used:

.. code-block:: php

    $request->getMethod(); // string

With the ``getGet`` method, query parts from the current URL:

.. code-block:: php

    $request->getGet(); // array

The ``getPost`` method get data from POST requests.

.. code-block:: php

    $request->getPost(); // array

With the ``getJson`` method, the request body data is parsed in JSON, being an
object, array or false if there are syntax errors.

.. code-block:: php

    $request->getJson(); // object, array or false

With the ``getBody`` method, the body string of the request is obtained.

.. code-block:: php

    $request->getBody(); // string

And with the ``getParsedBody`` method you get parsed body parts, used when the
HTTP method is neither GET nor POST.

.. code-block:: php

    $request->getParsedBody(); // array

Request working with Arrays
###########################

The ``getGet``, ``getPost``, ``getFile``, ``getParsedBody``, ``getEnv`` and
``getServer`` methods can get data from arrays via strings with square brackets.

For example, let's say ``$_POST`` equals the array below:

.. code-block:: php

    $_POST = [
        'user' => [
            'name' => 'John Doe',
            'address' => [
                'country' => 'Brazil',
                'city' => 'Sapiranga',
            ],
        ],
    ];

User data can be obtained as follows:

.. code-block:: php

    $name = $request->getPost('user[name]'); // John Doe
    $city = $request->getPost('user[address][city]'); // Sapiranga

Response
--------

The HTTP response send the message status, headers and body to a client.

To use the Response class, just instantiate it:

.. code-block:: php

    <?php
    require __DIR__ . '/vendor/autoload.php';
    
    use Framework\HTTP\Request;
    use Framework\HTTP\Response;
    
    $request = new Request();
    $response = new Response($request);

Response Status
###############

Response status can be set with the ``setStatusCode``, ``setStatusReason`` or
``setStatus`` methods using the status number or the value of a constant of
the **Status** class:

.. code-block:: php

    use Framework\HTTP\Status;

    $response->setStatus(401); // static
    $response->setStatus(Status::UNAUTHORIZED); // static

Response Headers
################

Headers can be set using the ``setHeader`` method, using a string in the first
parameter with the name of the header and in the second the value.

Also, you can use constants from the **Header** and **ResponseHeader** classes:

.. code-block:: php

    use Framework\HTTP\Header;

    $response->setHeader('Content-Type', 'text/xml'); // static
    $response->setHeader(Header::CONTENT_TYPE, 'text/xml'); // static
    $response->setContentType('text/xml'); // static

Response Body
#############

Each time you call the ``getBody`` method the buffer will be appended to the
body. This is so that, for example, ``echo`` can be used.

Also, you can use the ``setBody`` method.

Let's see an example manipulating the body of the Response:

.. code-block:: php

    echo 'Oi!';
    $body = $response->getBody(); // Oi!
    $response->setBody('Hi!');
    $body = $response->getBody(); // Hi!
    echo ' What is your name';
    $body = $response->getBody(); // Hi! What is your name
    $response->appendBody('???');
    $body = $response->getBody(); // Hi! What is your name???
    $response->setBody(['name' => 'A Framework']);
    $body = $response->getBody(); // name=A+Framework

Response with JSON
##################

A response containing JSON can be set as:

.. code-block:: php

    $users = [
        [
            'id' => 1,
            'name' => 'Adam',
        ],
        [
            'id' => 2,
            'name' => 'Eve',
        ],
    ];

    $response->setHeader('Content-Type', 'application/json'); // static
    $response->setBody(json_encode($users)); // static

or simply:

.. code-block:: php

    $response->setJson($users); // static

Response with HTML
##################

HTML, and any other Content-Type, can be set with the
``Response::{set,append,prepend}Body()`` methods.

.. code-block:: php

    $contents = '<h1>Hello, Aplus!</h1>';
    $response->setBody($contents);
    $contents = '<p>I am so happy to meet you.</p>';
    $response->appendBody($contents);

If the Content-Type header is not set, it is automatically set to
``text/html; charset=UTF-8`` when the Response is sent.

Response with Download
######################

To send a file as a download in the response, you can call:

.. code-block:: php

    $response->setDownload('filepath.pdf'); // static

With the second parameter set to true the content disposition is ``inline``,
causing the browser to open the file in the window.

.. code-block:: php

    $response->setDownload('filepath.pdf', inline: true); // static

The third parameter makes it possible to continue downloads or start downloading
a video at a certain time.

.. code-block:: php

    $response->setDownload('filepath.pdf', true, acceptRanges: true); // static

Sending the Response
####################

Now that you've seen how to set the Response status, headers and body, it's time
to see how to send the response to the User-Agent:

.. code-block:: php

    $response->send(); // void

The ``send`` method must be called only once, otherwise it will throw an exception.
Calling the ``send`` method is the last step of the HTTP response. After that,
nothing else should come out to the PHP Output Buffer. But, your script can
continue to run normally if necessary.

Response Cache
##############

Response has methods to simplify the caching process in the browser.

You can use the **Cache-Control** header to enable the cache for a certain time
or negotiate the response through the **ETag** header:

Cache-Control
^^^^^^^^^^^^^

To set the ``Cache-Control`` header, you can use the ``setCache`` method passing
the number of seconds in the first parameter and in the second pass true for it
to be public or false to be private, which is the default .

.. code-block:: php

    $response->setCache(60); // static

To not save anything in the cache, use the ``setNoCache`` method:

.. code-block:: php

    $response->setNoCache(); // static

ETag
^^^^

Through the ETag header it is possible to make the User-Agent cache the contents
of the response body by an identifier. This will cause the response to contain a
304 Not Modified status and the message body to be empty, saving data to be
transferred.

Likewise, it will avoid mid-air collisions on requests other than the GET or
HEAD method:

.. code-block:: php

    $response->setAutoEtag(); // static

URL
---

The library has a class for working with URLs. See how it works:

.. code-block:: php

    use Framework\HTTP\URL;
    
    $url = new URL('http://domain.tld:8080/slug?page=1#heading');
    echo $url->getScheme(); // http
    echo $url->getHost(); // domain.tld:8080
    echo $url->getHostname(); // domain.tld
    echo $url->getPort(); // 8080
    $url->setHostname('foo-bar.com');
    echo $url->getHost(); // foo-bar.com:8080
    $url->setPort(80);
    echo $url->getHost(); // foo-bar.com
    echo $url->getPath(); // /slug
    echo $url->getQuery(); // page=1
    echo $url->getFragment(); // heading

AntiCSRF
--------

The HTTP library has a class to prevent
`Cross-Site Request Forgery (CSRF) <https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#synchronizer-token-pattern>`_
using the Synchronizer Token Pattern.

We will see below how AntiCSRF works.

We have a file called **header.php** that will be required by other files
because it loads the autoload file, starts the session and instantiates the
Request and AntiCSRF objects:

.. code-block:: php

    <?php
    require __DIR__ . '/vendor/autoload.php';

    use Framework\HTTP\AntiCSRF;
    use Framework\HTTP\Request;

    session_start();
    $request = new Request();
    $antiCsrf = new AntiCSRF($request);

In this example, we have a form in the **form.php** file. In which there is a
call to the ``$antiCsrf`` variable that returns the field with the token saved
in the session.

The form action takes you to the **save.php** file using the POST method:

.. code-block:: php

    <?php
    require __DIR__ . '/header.php';
    ?>
    <form action="save.php" method="post">
        <?= $antiCsrf->input() ?>
        <label>Name</label>
        <input name="name"/>
        <label>Message</label>
        <textarea name="message"></textarea>
        <button>Send</button>
    </form>

Finally, we have the **save.php** file, where the verification of the token
received in the form is performed. If it is invalid, the script will terminate
showing that the request is invalid. If valid, it will show that the form
message can be saved.

.. code-block:: php

    <?php
    require __DIR__ . '/header.php';

    if ( ! $request->isPost() || ! $antiCsrf->verify()) {
        exit ('Request is invalid.');
    }
    echo 'OK. Anti-CSRF is valid. You can save the message!';

Note that in this example we validated only the field with the anti-CSRF token
and did not validate the other fields. Which can be validated using the
`Validation Library <https://gitlab.com/aplus-framework/libraries/validation>`_.

Conclusion
----------

Aplus HTTP Library is an easy-to-use tool for, beginners and experienced, PHP developers. 
It is perfect for building, simple and full-featured, HTTP interactions. 
The more you use it, the more you will learn.

.. note::
    Did you find something wrong? 
    Be sure to let us know about it with an
    `issue <https://gitlab.com/aplus-framework/libraries/http/issues>`_. 
    Thank you!
