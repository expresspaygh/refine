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
    $this->addDefaultFilterTypes();
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
  private function getFilterRulesForKey($key, $value): ?array
  {
    $type = gettype($value);
    $fieldType = array_key_exists($key, $this->fields)
               ? $this->fields[$key]
               : null;

    if (array_key_exists($fieldType, $this->filterRules))
      return $this->filterRules[$fieldType];
    else if (array_key_exists($key, $this->filterRules))
      return $this->filterRules[$key];
    else if (array_key_exists($type, $this->filterRules))
      return $this->filterRules[$type];
    else
      return [];
  }

  /**
   * getFilterOptionsForKey: Return the configured filter options
   *
   * @return array[]
   */
  private function getFilterOptionsForKey($all, $key, $value): array
  {
    $type = gettype($value);
    if (array_key_exists($key, $all))
      return $all[$key];
    else if (array_key_exists($key, $this->filterTypes))
      return $this->filterTypes[$key];
    else if (array_key_exists($type, $all))
      return $all[$type];
    else
      return [];
  }

  /**
   * getFilterType
   *
   * @param  mixed $varType
   * @return array
   */
  private function getFilterType(string $key): ?array
  {
    return array_key_exists($key, $this->filterTypes) ? $this->filterTypes[$key] : NULL;
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

  private function getAllFilterOptions(array $request): array {
    $all = [];
    foreach ($request as $requestKey => $_) {
      $option = NULL;

      // TOOD refactor
      // user supplied filter options for this key
      if (array_key_exists($requestKey, $this->fields)) {
        // but the filter options are empty
        if (empty($this->fields[$requestKey])) {
          $option = $this->getFilterType($requestKey);
        } else {
          // filter options are not empty so use them
          $option = $this->fields[$requestKey];

          // user supplied option is a string so we get the filter options we
          // have saved under that string
          if (!is_array($option) && !in_array($option, ["nullable", "null"])) {
            $option = $this->getFilterType($option);
          }

          // user supplied option is not a string, but they have nullify in the
          // array and we don't have a custom filter to apply so we don't use a
          if (in_array($option, ["nullable", "null"])) {
            $option = NULL;
          }
        }
      }

      if (!is_null($option))
        $all[$requestKey] = $option;
    }

    return $all;
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
    $allOptions = $this->getAllFilterOptions($request);

    foreach ($request as $key => $value) {
      // get filter rules and options
      $rules = $this->getFilterRulesForKey($key, $value);
      $options = $this->getFilterOptionsForKey($allOptions, $key, $value);

      // run filter rules
      foreach ($rules as $rule) {
        $value = $rule->apply($value);
      }
      
      // run php filters
      if (!empty($options))
      {
        if (array_key_exists("filter", $options))
          $filter = $options["filter"];
        else
          $filter = FILTER_DEFAULT;

        $flags = [];
        if (array_key_exists("flags", $options))
          $flags["flags"] = $options["flags"];
        if (array_key_exists("options", $options))
          $flags = array_merge($flags, $options["options"]);

        $value = filter_var($value, $filter, $flags);
        $output[$key] = $value;
      }
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


  private function addDefaultFilterTypes() {
    $this->addFilterType('bool', [
      'filter' => FILTER_DEFAULT
    ]);

    $this->addFilterType('string', [
      'filter' => FILTER_SANITIZE_STRING,
      'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
    ]);

    $this->addFilterType('url', [
      'filter' => FILTER_SANITIZE_URL,
      'flags' => FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED
    ]);

    $this->addFilterType('email', [
      'filter' => FILTER_VALIDATE_EMAIL | FILTER_SANITIZE_EMAIL
    ]);

    $this->addFilterType('float', [
      'filter' => FILTER_VALIDATE_FLOAT | FILTER_SANITIZE_NUMBER_FLOAT,
      'flags' => FILTER_FLAG_ALLOW_THOUSAND
    ]);

    $this->addFilterType('int', [
      'filter' => FILTER_VALIDATE_INT | FILTER_SANITIZE_NUMBER_INT
    ]);

    $this->addFilterType('html', [
      'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
    ]);

    $this->addFilterType('array', [
      'flags' => FILTER_REQUIRE_ARRAY
    ]);

    $this->addFilterType('regex', [
      'filter' => FILTER_VALIDATE_REGEXP,
      'options' => ['regexp' => '/[^a-z0-9\.]/i']
    ]);

    $this->addFilterType('ip', [
      'filter' => FILTER_VALIDATE_IP
    ]);
  }

  private function addDefaultFilterRules() {
    $this->addRule("string", new Rules\CleanTags);
    $this->addRule("bool", new Rules\Boolean);
  }
}
