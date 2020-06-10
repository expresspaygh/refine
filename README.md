
# Table of Contents

1.  [Expresspay Refines]
    1.  [Basic Usage]
    2.  [Custom field types]
    3.  [Writing custom rules]


# Expresspay Refine

A simple server-side request filter, sanitizer and validator


## Basic Usage

\`Expay\Refine\Filter\` does most of the work. Most calls to the class will only
use the methods \`addField\` and \`check\`.

A field is defined with its key in the request and a value indicating its type.
When this value is a string, its used to lookup a stored list of rules to
filter the request value with.

```php
use Expay\Refine\Filter;
use Expay\Refine\Rules;

$result = (new Filter())
	->addField("password", "string")
	->addField("email", "email")
	->check([
	"email" => "someone@expresspaygh.com",
	"password" => "hackme"
]);

print_r($result);
```

```
Array
(
	[status] => 0
	[message] => Success
	[output] => Array
		(
			[password] => hackme
			[email] => someone@expresspaygh.com
		)

)
```

## Custom field types

Custom field types can be added by specifying the rules to apply to them. This
can be done with the following code:


```php
use Expay\Refine\Filter;
use Expay\Refine\Rules;


$result = (new Filter())
	// add a field type called boolean_value 
	->addRule("uppercase_bool", new Rules\Boolean('upper'))
	->addRules("email", [
		new Rules\Required,
		new Rules\PHPFilter([
			'filter' => FILTER_VALIDATE_EMAIL | FILTER_SANITIZE_EMAIL
		])])
	->addField("is_admin", "uppercase_bool")
	->addField("email", "email")
	->check([
		"is_admin" => "true",
		"email" => "admin@example.com"
	]);

print_r($result);
```

```
Array
(
	[status] => 0
	[message] => Success
	[output] => Array
		(
			[is_admin] => TRUE
			[email] => admin@example.com
		)

)
```

A factory function can be used to avoid repeating definitions.

```php
use Expay\Refine\Filter;
use Expay\Refine\Rules;

function filter() {
	return (new Filter())
	// add a field type called boolean_value 
	->addRule("uppercase_bool", new Rules\Boolean('upper'))
	->addRules("email", [
		new Rules\Required,
		new Rules\PHPFilter([
			'filter' => FILTER_VALIDATE_EMAIL | FILTER_SANITIZE_EMAIL
	])]);
}

print_r(filter()->check([
	"is_admin" => "true",
	"email" => "admin@example.com"
]));
```

```
Array
(
	[status] => 0
	[message] => Success
	[output] => Array
		(
			[email] => admin@example.com
		)

)
```

## Writing custom rules

Custom rules can be written by sub-classing `\Expay\Refine\Rules\Rule` and
implementing the apply method.

```php
use Expay\Refine\Filter;
use Expay\Refine\Rules\Rule;
use Expay\Refine\Exceptions\InvalidField;

class CVV extends Rule
{
	public function apply($value, string $key, array $request): string
	{
		if (preg_match("/^\d\d\d$/", $value))
			return $value;
		throw new InvalidField("Invalid cvv");
	}
}

$result = (new Filter())
	->addRule("cvv", new CVV)
	->check(["cvv" => "123"]);

var_dump($result);
```

```
array(3) {
  'status' =>
  int(0)
  'message' =>
  string(7) "Success"
  'output' =>
  array(1) {
    'cvv' =>
    string(3) "123"
  }
}
```
