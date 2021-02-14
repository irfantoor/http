# IrfanTOOR\Http

Irfan's Http package is a hacker's Http suite which implements psr/http-factory and
psr/http-message, eliminating the validations, so that you can audit your web apps
or sites against vulnerabilities or can write small API's or testing utilities. You
can forget about the constraints of validations.

## Quick install.
Use composer to include it in your package.

```sh
$ composer require irfantoor/http
```

Note: this package comes pre installed with the latest releases of Irfan's Engine
(irfantoor/engine). Since there is a psr compliance at implementation of funtions,
though not strictly adhering to the guide lines of validating the parameters etc.,
you can always use IrfanTOOR\Http in any of your packages requiring Psr compliant
Http components, with the flexibilty of testing or enhancing the protocol for your
home grown Http clients, IoT devices for example.

## Examples

Here is an example code (you can find it in the examples folder):

```php
<?php

# use php -S localhost:8000 examples/hello.php

require dirname(__DIR__) . "/vendor/autoload.php";

use IrfanTOOR\Http\ServerRequest;
use IrfanTOOR\Http\Response;

# Server request we have received
$request  = new ServerRequest();

# create a response to send
$response = new Response();
$response = $response
    ->withHeader("Content-Type", "application/json")
    ->withHeader("Engine", "Engine One v1.0")
;

# our simple router
$path = ltrim(rtrim($request->getUri()->getPath(), '/'), '/');
$args = $path === "" ? [] : explode('/', $path);
$action = $args[0] ?? "home";

# lets route
switch($action) {
    case "home":
        $response->getBody()->write(json_encode("I'm home"));
        break;

    case "hello":
        $name = ucfirst($args[1] ?? "world");
        $response->getBody()->write(json_encode("Hello " . $name . "!"));
        break;

    default:
        $response = $response
            ->withStatus(0, "the status quo")
            ->withProtocolVersion("2.0")
            ->withHeader("lib(rary)", "IrfanTOOR\Http 0.1")
        ;
        $response->getBody()->write(json_encode([
            "status" => 404,
            "error" => "nothing found here!",
        ]));
        break;
}

# thats all folks!
$response->send();
```