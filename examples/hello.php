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
