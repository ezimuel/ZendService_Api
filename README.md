ZendService\Api 
===============

This is a micro HTTP framework to consume generic API calls in PHP. This framework can be used to
create PHP libraries that consume specific HTTP API using simple configuration files.
This project uses the `Zend\Http\Client` component of [Zend Framework 2](https://github.com/zendframework/zf2).

Release note
------------

This code is in *alpha* version, please don't use it in a production environment.


Installation
------------

You can install this component using [composer](http://getcomposer.org/) with following commands:

    ```
    curl -s https://getcomposer.org/installer | php
    php composer.phar install
    ```

Usage
-----

The `ZendService\Api` component can be used to facilitate the consume of generic API using HTTP.
The micro HTTP framework is able to configure the header, method, body, and query string of a HTTP
request according to specific API parameters. This mapping is provided using PHP configuration array.
The `ZendService\Api` consumes, for each API call, two PHP configuration files, one for the HTTP request
and another for the parameters mapping. The configuration files are named using the same name of 
the PHP function to generate. For instance, if the name of the API function is authenticate, the 
configuration files will be named `authenticate.php` (for the HTTP request) and `authenticate_params.php`
(for the mapping).

Let see an example, image you need to consume an authentication API call with a POST HTTP request using
a [JSON](http://www.json.org/) data format with the following parameters: username and password.
The HTTP request can be represented as follow:

    ```
    PUT /v1/auth HTTP/1.1
    Host: localhost
    Connection: close
    Content-Type: application/json
    Content-Length: 57

    { 'auth' : { 'username' : 'admin', 'password' : 'test' }}
    ```

You can use the Api class to perform this request as follow:

    ```php
    use ZendService\Api\Api;

    $api = new Api('path/to/config/files');
    $api->authenticate('admin', 'test');
    if ($api->isSuccess()) {
        printf("OK!\n");
    } else {
        printf("Error (%d): %s\n", $api->getStatusCode(), $api->getErrorMsg());
    }
    ```

The PHP configuration files for the `authenticate` API call are reported as follow:

    ```php
    // authenticate.php
    return array(
        'uri' => 'http://localhost/v1/auth',
        'header' => array(
            'Content-Type' => 'application/json'
        ),
        'method' => 'POST',
        'body' => json_encode(array(
            'auth' => array(
                'username' => $params['username'],
                'password' => $params['password']
            )
        )),
        'response' => array(
            'valid_codes' => array('200')
        )
    );
    ```

In this configuration file you can specify also the HTTP status code for the successful
requests using the `valid_codes` parameter in the `response` section. 
In order to map the API parameters to the `authenticate` function we need to use another
configuration file, the `authenticate_params.php` reported below:

    ```php
    // authenticate_params.php
    return array(
        'params' => array(
            'username' => 'string',
            'password' => 'string'
        )
    );
    ```
In this configuration file you can specify the type format of each parameters. In our example
we used two strings. This can be very useful for validate the parameters passed to the API calls.

Query string in the API calls
-----------------------------

If you need to pass a query string for an API HTTP call you can use the `setQueryParams` method
of the `Api` class. For instance, imagine you need to pass the HTTP query string `?auth=strong` in
the previous example, you can use the following code:

    ```php
    use ZendService\Api\Api;

    $api = new Api('path/to/config/files');
    $api->setQueryParams(array( 'auth' => 'strong' ));
    $api->authenticate('admin', 'test');
    if ($api->isSuccess()) {
        printf("OK!\n");
    } else {
        printf("Error (%d): %s\n", $api->getStatusCode(), $api->getErrorMsg());
    }
    ```

You can reset the query string calling the `setQueryParams()` function without a parameter.

