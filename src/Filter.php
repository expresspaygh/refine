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
   * requestVars: The request coming in as passed to the class constructor or to check
   *
   * @var array
   */
  private $requestVars;

  /**
   * applicableOptions
   *
   * @var array
   */
  private $applicableOptions = [];
  /**
   * finalFilterOutput
   *
   * @var array
   */
  private $finalFilterOutput = array();
  /**
   * nullifyValue
   *
   * @var array
   */
  private $nullifyValue = ["nullable", "null"];
  /**
   * filterArgsOptions
   *
   * @var array
   */
  private $filterArgsOptions = array();
  /**
   * filterResponse
   *
   * @var array
   */
  private $filterResponse = array();

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
  public function __construct(array $options = [], array $request = null)
  {
    if (is_null($request)) $request = $_REQUEST;
    $this->requestVars = $request;
    $this->applicableOptions = $options;
    $this->buildArgs();
    $this->run();
    $this->response();
  }

  /**
   * getFilterResponse
   *
   * @todo document the format
   * @return array
   */
  public function getFilterResponse(): ?array
  {
    return $this->filterResponse;
  }

  /**
   * Custom added filter rules
   *
   * @var Rule[]
   */
  private static $customFilterRules = [];

  /**
   * Custom added filter types
   *
   * @var array[]
   */
  private static $customFilterTypes = [];

  /**
   * Add a filter type
   *
   * @param string $name
   * @param mixed $type
   */
  public static function addType(string $name, $type) {
    self::$customFilterTypes[$name] = $type;
  }

  /**
   * Add a filter rule
   *
   * @param string $name
   * @param Rule $rule
   */
  public static function addRule(string $name, Rule $rule) {
    self::$customFilterRules[$name] = $rule;
  }

  /**
   * getFilterRules: Return the configured filter rules
   *
   * @return Rule[]
   */
  private static function getFilterRules(): array
  {
    return array_merge([
      "string" => [new Rules\CleanTags],
      "bool" => [new Rules\Boolean]
    ], self::$customFilterRules);
  }

  /**
   * getConstant
   *
   * @param  mixed $varType
   * @return array
   */
  private function getConstant(string $varType): ?array
  {
    $types = array_merge([
      'bool' => [
        'filter' => FILTER_DEFAULT
      ],
      'string' => [
        'filter' => FILTER_SANITIZE_STRING,
        'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK
      ],
      'url' => [
        'filter' => FILTER_SANITIZE_URL,
        'flags' => FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED
      ],
      'email' => [
        'filter' => FILTER_VALIDATE_EMAIL | FILTER_SANITIZE_EMAIL
      ],
      'float' => [
        'filter' => FILTER_VALIDATE_FLOAT | FILTER_SANITIZE_NUMBER_FLOAT,
        'flags' => FILTER_FLAG_ALLOW_THOUSAND
      ],
      'int' => [
        'filter' => FILTER_VALIDATE_INT | FILTER_SANITIZE_NUMBER_INT
      ],
      'html' => [
        'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
      ],
      'array' => [
        'flags' => FILTER_REQUIRE_ARRAY
      ],
      'regex' => [
        'filter' => FILTER_VALIDATE_REGEXP,
        'options' => ['regexp' => '/[^a-z0-9\.]/i']
      ],
      'ip' => [
        'filter' => FILTER_VALIDATE_IP
      ]
    ], self::$customFilterTypes);
    return (array_key_exists($varType, $types)) ? $types[$varType] : NULL;
  }

  /**
   * getResponse
   *
   * @param  mixed $status
   * @param  mixed $message
   * @param  mixed $output
   * @return array
   */
  private function getResponse(int $status, string $message, array $output = null): array
  {
    $out = array();
    $out['status'] = $status;
    $out['message'] = $message;
    if (!is_null($output)) $out['output'] = $output;
    return $out;
  }

  /**
   * getFilterFlagsOptions
   * Compute the filter flags for the given request key and value, and do any
   * Rules processing on request values.
   *
   * @param  mixed $requestKey
   * @param  mixed $requestValue
   * @return array
   */
  private function getFilterFlagsOptions($requestKey, &$requestValue = null): ?array
  {
    $filterOptions = NULL;

    // user supplied filter options for this key
    if (array_key_exists($requestKey, $this->applicableOptions)) {
      // but the filter options are empty
      if (empty($this->applicableOptions[$requestKey])) {
        $filterOptions = $this->getConstant($requestKey);
      } else {
        // filter options are not empty so use them
        $filterOptions = $this->applicableOptions[$requestKey];

        // user supplied option is a string so we get the filter options we
        // have saved under that string
        if (!is_array($filterOptions) && !in_array($filterOptions, $this->nullifyValue)) {
          $filterOptions = $this->getConstant($filterOptions);
        }

        // user supplied option is not a string, but they have nullify in the
        // array and we don't have a custom filter to apply so we don't use a
        if (in_array($filterOptions, $this->nullifyValue)) {
          $filterOptions = NULL;
        }
      }
    } else {
      // user supplied no filter options for this key so we try to find the key
      // in our filter constants.
      $getConstantKey = $this->getConstant($requestKey);
      if (!is_null($getConstantKey)) {
        // we found it so we use that
        $filterOptions = $getConstantKey;
      } else {
        // we didn't find anything. then we look for some stored filter rules
        // for the type of the request value
        $reqValType = gettype($requestValue);
        $getConstantValue = $this->getConstant($reqValType);
        if (!is_null($getConstantValue)) {
          // and use them if we find them
          $filterOptions = $getConstantValue;
        }
      }
    }
    return $filterOptions;
  }

  /**
   * buildArgs
   * Compute the filter args for all keys and values in the request, update
   * the stored request with the Rules processed result.
   *
   * @return int
   * @todo remove the "update stored request" part
   */
  private function buildArgs(): int
  {
    if (!is_null($this->requestVars)) {
      foreach ($this->requestVars as $requestKey => $requestValue) {
        $collectArgs = $this->getFilterFlagsOptions($requestKey, $requestValue);
        if (!is_null($collectArgs)) $this->filterArgsOptions[$requestKey] = $collectArgs;
        if (!is_null($requestValue)) $this->requestVars[$requestKey] = $requestValue;
      }
    }
    return 0;
  }

  /**
   * run
   * Run the php `filter_var_array` function and store the results.
   * The response is stored on the `finalFilterOutput` property
   *
   * @return int
   */
  private function run(): int
  {
    $output = [];

    foreach ($this->requestVars as $key => $value) {
      $type = gettype($value);
      $filterRules = $this->getFilterRules();
      $applicableOption = array_key_exists($key, $this->applicableOptions)
                        ? $this->applicableOptions[$key]
                        : null;

      // get filter rules for this key value pair
      if (array_key_exists($applicableOption, $filterRules))
        $rules = $filterRules[$applicableOption];
      else if (array_key_exists($key, $filterRules))
        $rules = $filterRules[$key];
      else if (array_key_exists($type, $filterRules))
        $rules = $filterRules[$type];
      else $rules = [];

      // get filter optionss for this key value pair
      if (array_key_exists($key, $this->filterArgsOptions))
        $options = $this->filterArgsOptions[$key];
      else if (array_key_exists($type, $this->filterArgsOptions))
        $options = $this->filterArgsOptions[$type];
      else $options = [];

      // run filter rules
      if (!empty($rules))
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

    $this->finalFilterOutput = $output;
    return 0;
  }

  /**
   * response
   * Check the filtered output and generate and store a success/error response.
   * The response is stored on the `filterResponse` property
   *
   * @return int
   */
  private function response(): int
  {
    if (!empty($this->finalFilterOutput)) {
      $failures = array();
      $passed = array();
      foreach ((array) $this->finalFilterOutput as $key => $value) {
        if (empty($value)) {
          $failures[$key] = "$key is not valid, kindly check and try again";
        } else {
          $passed[$key] = $value;
        }
      }
      if (!empty($failures)) {
        $this->filterResponse = $this->getResponse(2, 'Bad Request, kindly check and try again', $failures);
      } else {
        $this->filterResponse = $this->getResponse(0, 'Success', $passed);
      }
    }
    return 0;
  }

  /**
   * Helper static method to construct a filter and return the result.
   *
   * @param array $request
   * @todo document the options
   * @param array $options
   *
   * @todo document the format
   * @return array
   */
  public static function check(array $options = [], array $request = null): array
  {
    $flt = new self($options, $request);
    return $flt->getFilterResponse();
  }
}
