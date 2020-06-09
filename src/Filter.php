<?php

namespace Expay\Refine;
use Expay\Refine\Rules\Rule;

/**
 * Filter incoming HTTP requests
 *
 * @todo document what filtering is done
 * @todo add phone number validation
 *
 */
class Filter
{
  /**
   * __construct
   * Perform request filtering. Responses can be found on the constructed
   * object.
   *
   * @todo document the options
   * @param  mixed $options
   * @param  mixed $request
   * @return void
   */
  public function __construct(array $fields = []) {
    $this->fields = $fields;
    $this->addDefaultFilterRules();
  }

  /**
   * Check the given request against the defined fields
   *
   * @param array $request
   * @todo document the options
   * @param array $options
   *
   * @todo document the format
   * @return array
   */
  public function check(array $request = null) {
    if (is_null($request)) $request = $_REQUEST;
    $response = $this->run($request);
    $response = $this->checkFailures($response);

    return $response;
  }

  public function addField(string $key, string $type) {
    $this->fields[$key] = $type;
    return $this;
  }

  public function addRule(string $key, Rule $rule) {
    if (!array_key_exists($key, $this->filterRules))
      $this->filterRules[$key] = [];

    $this->filterRules[$key][] = $rule;
    return $this;
  }

  /**
   * Replace stored rules for the given field type with the supplied ones
   *
   * @param Rule[]
   */
  public function replaceRules(string $key, array $rules) {
    $this->filterRules[$key] = $rules;
    return $this;
  }

  public function addFilterType(string $key, array $type) {
    $this->filterTypes[$key] = $type;
    return $this;
  }

  /************************************************************************
   * Private methods and variables                                        *
   ***********************************************************************/

  /**
   * Custom added filter rules
   *
   * @var Rule[]
   */
  private $filterRules = [];

  /**
   * Custom added filter types
   *
   * @var array[]
   */
  private $filterTypes = [];

  /**
   * getFilterRulesForKey: Return the configured filter rules
   *
   * @return Rule[]
   */
  private function getFilterRulesForKey($key): ?array
  {
    $fieldType = array_key_exists($key, $this->fields)
               ? $this->fields[$key]
               : null;

    if (array_key_exists($fieldType, $this->filterRules))
      return $this->filterRules[$fieldType];
    else if (array_key_exists($key, $this->filterRules))
      return $this->filterRules[$key];
    else
      return [];
  }

  /**
   * formatResponse
   *
   * @param  mixed $status
   * @param  mixed $message
   * @param  mixed $output
   * @return array
   */
  private function formatResponse(int $status, string $message, array $output = null): array
  {
    $out = array();
    $out['status'] = $status;
    $out['message'] = $message;
    if (!is_null($output)) $out['output'] = $output;
    return $out;
  }

  /**
   * run
   * Run the php `filter_var_array` function and store the results.
   * The response is stored on the `finalFilterOutput` property
   *
   * @return int
   */
  private function run($request): array
  {
    $output = [];

    foreach ($request as $key => $value) {
      // get filter rules and options
      $rules = $this->getFilterRulesForKey($key);

      if (empty($rules))
        continue;

      // run filter rules
      foreach ($rules as $rule) {
        $value = $rule->apply($value);
      }

      $output[$key] = $value;
    }

    return $output;
  }

  /**
   * Check the filtered output and generate and store a success/error response.
   * The response is stored on the `filterResponse` property
   *
   * @return int
   */
  private function checkFailures($response): array
  {
    $failures = array();
    $passed = array();
    foreach ((array) $response as $key => $value) {
      // workaroud for false booleans being identical to an error value
      $isBool = array_key_exists($key, $this->fields) && $this->fields[$key] === "bool";

      if ($value === false && !$isBool) {
        $failures[$key] = "$key is not valid, kindly check and try again";
      } else {
        $passed[$key] = $value;
      }
    }
    if (!empty($failures)) {
      $response = $this->formatResponse(2, 'Bad Request, kindly check and try again', $failures);
    } else {
      $response = $this->formatResponse(0, 'Success', $passed);
    }

    return $response;
  }

  /**
   * The fields as specified by calling `addFields`
   *
   * @var array
   */
  private $fields = [];

  private function addDefaultFilterRules() {
    $this->addRule("string", new Rules\CleanTags);
    $this->addRule("bool", new Rules\Boolean);
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
  }
}
