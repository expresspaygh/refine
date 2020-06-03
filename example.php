<?php
require("vendor/autoload.php");
use expresspay\request_filter\RequestFilter;


$result = RequestFilter::check([
    "some_url" => "https://foobar.com",
    "some_email" => "someone@expresspaygh.com",
    "some_int" => 1,
    "some_bool" => true,
    "some_array" => [1, 2, 3],
    "some_ip" => "127.0.0.1",
    "some_string" => "<a href='/asdf'>foobar</a>"
], [
    'some_url' => 'url',
    'some_email' => 'email',
    'some_int' => 'int',
    "some_bool" => "bool",
    "some_array" => "array",
    "some_ip" => "ip"
]);

print_r($result);
