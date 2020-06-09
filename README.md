# Expresspay Refine
A simple server-side request filter, sanitizer and validator

# Example Usage
```php
<?php
require("vendor/autoload.php");
use Expay\Refine\Filter;


$result = (new Filter([
  'some_url' => 'url',
  'some_email' => 'email',
  'some_int' => 'int',
  "some_bool" => "bool",
  "some_array" => "array",
  "some_ip" => "ip"
]))->check([
  "some_url" => "https://foobar.com",
  "some_email" => "someone@expresspaygh.com",
  "some_int" => 1,
  "some_bool" => true,
  "some_array" => [1, 2, 3],
  "some_ip" => "127.0.0.1",
  "some_string" => "<a href='/asdf'>foobar</a>"
]);

print_r($result);

```
