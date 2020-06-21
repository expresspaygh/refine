<?php

namespace Expay\Refine;
use Expay\Refine\Rules\Rule;
use Expay\Refine\Exceptions\InvalidField;

/**
 * Filter incoming HTTP requests
 */
class Filter
{
  /**
   * filterRules: Custom added filter rules
   *
   * @var array
   */
  private $filterRules = [];

  /**
   * The fields as specified by calling `addFields`
   *
   * @var array
   */
  private $fields = [];

  /**
   * __construct: Perform request filtering. Responses can be found on the constructed
   * object.
   *
   * @param  mixed $fields
   * @return void
   */
  public function __construct(array $fields = [])
  {
    $this->fields = $fields;
    $this->addDefaultFilterRules();
  }

  /**
   * getFilterRulesForKey: Return the configured filter rules for key
   *
   * @param  mixed $key
   * @return array || NULL
   */
  private function getFilterRulesForKey($key):?array
  {
    $fieldType = array_key_exists($key, $this->fields) ? $this->fields[$key] : null;
    
    if (is_array($fieldType))
      return $fieldType;

    if(array_key_exists($fieldType, $this->filterRules))
      return $this->filterRules[$fieldType];
    elseif(array_key_exists($key, $this->filterRules))
      return $this->filterRules[$key];

    return null;
  }

  /**
   * getFilterRulesForValue: Return the configured filter rules for value
   *
   * @param  mixed $value
   * @return array || NULL
   */
  private function getFilterRulesForValue($value):?array
  {
    $filter_rules = $this->filterRules[gettype($value)];
    return !empty($filter_rules) ? $filter_rules : null;
  }

  /**
   * formatResponse
   *
   * @param  mixed $status
   * @param  mixed $message
   * @param  mixed $output
   * @return array
   */
  private function formatResponse(int $status, string $message, array $output = null):array
  {
    $out = array();
    $out['status'] = $status;
    $out['message'] = $message;
    if (!is_null($output)) $out['output'] = $output;
    return $out;
  }
  
  /**
   * addDefaultFilterRules
   *
   * @return Filter
   */
  private function addDefaultFilterRules():Filter
  {
    // Custom
    $this->addRule("string", new Rules\CleanTags);
    $this->addRule("bool", new Rules\Boolean);
    $this->addRule("clean_string", new Rules\CleanString);
    $this->addRule("nullable", new Rules\Nullable);

    // PHP Classes
    $this->addRule('string', new Rules\PHPFilter([
      'filter' => FILTER_SANITIZE_STRING,
      'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
    ]));
    $this->addRule('url', new Rules\PHPFilter([
      'filter' => FILTER_SANITIZE_URL,
      'flags' => FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED
    ]));
    $this->addRule('email', new Rules\PHPFilter([
      'filter' => FILTER_VALIDATE_EMAIL | FILTER_SANITIZE_EMAIL
    ]));
    $this->addRule('float', new Rules\PHPFilter([
      'filter' => FILTER_VALIDATE_FLOAT | FILTER_SANITIZE_NUMBER_FLOAT,
      'flags' => FILTER_FLAG_ALLOW_THOUSAND
    ]));
    $this->addRule('int', new Rules\PHPFilter([
      'filter' => FILTER_VALIDATE_INT | FILTER_SANITIZE_NUMBER_INT
    ]));
    $this->addRule('html', new Rules\PHPFilter([
      'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
    ]));
    $this->addRule('array', new Rules\PHPFilter([
      'flags' => FILTER_REQUIRE_ARRAY
    ]));
    $this->addRule('regex', new Rules\PHPFilter([
      'filter' => FILTER_VALIDATE_REGEXP,
      'options' => ['regexp' => '/[^a-z0-9\.]/i']
    ]));
    $this->addRule('ip', new Rules\PHPFilter([
      'filter' => FILTER_VALIDATE_IP
    ]));

    return $this;
  }

  /**
   * run: Run the php `filter_var_array` function and store the results.
   * The response is stored on the `finalFilterOutput` property
   *
   * @param  mixed $request
   * @return array
   */
  private function run($request):array
  {
    $output = [];
    $errors = [];

    $keys = array_unique(array_merge(array_keys($this->fields), array_keys($request)));
    foreach ($keys as $key) {
      $value = array_key_exists($key, $request) ? $request[$key] : null;
      // get filter rules and options
      $rules = $this->getFilterRulesForKey($key);
      if (is_null($rules))
        $rules = $this->getFilterRulesForValue($value);
        if(is_null($rules))
          continue;

      // run filter rules
      foreach ($rules as $rule) {
        try {
          $value = $rule->apply($value, $key, $request);
        } catch (InvalidField $e) {
          $errors[$key] = $e->getMessage();
          break;
        }
      }

      if (empty($errors[$key]))
        $output[$key] = $value;
    }

    return [$output, $errors];
  }

  /**
   * check: Check the given request against the defined fields
   *
   * @param  mixed $request
   * @return array
   */
  public function check(array $request = null):array
  {
    if (is_null($request)) $request = $_REQUEST;
    [$response, $errors] = $this->run($request);

    if (empty($errors)) 
      return $this->formatResponse(0, "Success", $response);
    else
      return $this->formatResponse(2, 'Bad Request, kindly check and try again', $errors);
  }
  
  /**
   * addField
   *
   * @param  mixed $key
   * @param  mixed $type
   * @return Filter
   */
  public function addField(string $key, $type):Filter
  {
    if (!is_string($type) && !is_array($type))
      throw new \Exception("$type must be a string type or an array of rules");
    $this->fields[$key] = $type;
    return $this;
  }
  
  /**
   * addFields
   *
   * @param  mixed $fields
   * @return Filter
   */
  public function addFields(array $fields):Filter
  {
    foreach($fields as $key => $field)
      $this->addField($key, $field);
    return $this;
  }
  
  /**
   * addRule
   *
   * @param  mixed $key
   * @param  mixed $rule
   * @return Filter
   */
  public function addRule(string $key, Rule $rule):Filter
  {
    if (!array_key_exists($key, $this->filterRules))
      $this->filterRules[$key] = [];

    $this->filterRules[$key][] = $rule;
    return $this;
  }
  
  /**
   * addRules
   *
   * @param  mixed $key
   * @param  mixed $rules
   * @return Filter
   */
  public function addRules(string $key, array $rules):Filter
  {
    foreach($rules as $rule) {
      $this->addRule($key, $rule);
    }

    return $this;
  }

  /**
   * replaceRules: Replace stored rules for the given field type with the supplied ones
   *
   * @param  mixed $key
   * @param  mixed $rules
   * @return Filter
   */
  public function replaceRules(string $key, array $rules):Filter
  {
    $this->filterRules[$key] = $rules;
    return $this;
  }
}
