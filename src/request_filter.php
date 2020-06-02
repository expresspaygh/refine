<?php

/**
 * Filter incoming HTTP requests
 *
 * @todo document what filtering is done
 * @todo add phone number validation
 *
 * Example usage:
 *
 * ```php
 * $result = RequestFilter::check([
 *   "some_url" => "url",
 *   "some_email" => "email",
 *   "some_int" => "int",
 *   "some_bool" => "bool",
 *   "some_array" => "array",
 *   "some_ip" => "ip"
 * ], [
 *   "some_url" => "https://foobar.com",
 *   "some_email" => "someone@expresspaygh.com",
 *   "some_int" => 1,
 *   "some_bool" => "TRUE",
 *   "some_array" => [1, 2, 3],
 *   "some_ip" => "127.0.0.1",
 *   "some_string" => "<a href='/asdf'>foobar</a>"
 * ]);
 *
 * $result === [
 *   "status" => 0,
 *   "message" => "Success",
 *   "output" => [
 *     "some_url" => "https://foobar.com",
 *     "some_email" => "someone@expresspaygh.com",
 *     "some_int" => 1,
 *     "some_bool" => 1,
 *     "some_array" => [1, 2, 3],
 *     "some_ip" => "127.0.0.1",
 *     "some_string" => "a hrefasdffoobara"
 *   ]
 * ];
 * ```
 */
class RequestFilter
{
  /**
   * Perform request filtering. Responses can be found on the constructed
   * object.
   *
   * @param array $request
   * @todo document the options
   * @param array $options
   */
  public function __construct(array $options = [], array $request = null)
  {
    if (is_null($request))
      $request = $_REQUEST;

    $this->requestVars = $request;
    $this->applicableOptions = $options;
    $this->buildArgs();
    $this->run();
    $this->response();
  }

  /**
   * Return the filter response
   *
   * @todo document the format
   * @return array
   */
  public function getFilterResponse(): ?array
  {
    return $this->filterResponse;
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

  private $requestVars = NULL;
  private $applicableOptions = NULL;
  private $finalFilterOutput = array();
  private $nullifyValue = ["nullable", "null"];
  private $filterArgsOptions = array();
  private $filterResponse = array();

  private function getConstant(string $varType): ?array
  {
    $types = [
      'bool' => [
        'filter' => FILTER_VALIDATE_BOOLEAN
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
        'options' => ['regexp' => '/[^a-z0-9\.]/i'] // uses regular string search replace
      ],
      'ip' => [
        'filter' => FILTER_VALIDATE_IP
      ]
    ];
    return (array_key_exists($varType, $types)) ? $types[$varType] : NULL;
  }

  private function getResponse(int $status, string $message, array $output = null): array
  {
    $out = array();
    $out['status'] = $status;
    $out['message'] = $message;
    if (!is_null($output)) $out['output'] = $output;
    return $out;
  }

  /**
   * Compute the filter flags for the given request key and value, and do any
   * FilterRules processing on request values.
   *
   * @param string $requestKey
   * @param array &$requestValue the referenced variable is modified according
   *     to FilterRules
   *
   * @return ?array
   */
  private function getFilterFlagsOptions($requestKey, &$requestValue = null): ?array
  {
    $filterOptions = NULL;
    if (array_key_exists($requestKey, $this->applicableOptions)) {
      if (empty($this->applicableOptions[$requestKey])) {
        $filterOptions = $this->getConstant($requestKey);
      } else {
        $filterOptions = $this->applicableOptions[$requestKey];
        if (!is_array($filterOptions) && !in_array($filterOptions, $this->nullifyValue) && !FilterRules::check($filterOptions)) {
          $filterOptions = $this->getConstant($filterOptions);
        }
        if (in_array($filterOptions, $this->nullifyValue) && !FilterRules::check($filterOptions)) {
          $filterOptions = NULL;
        }
        if (!is_array($filterOptions) && !in_array($filterOptions, $this->nullifyValue) && FilterRules::check($filterOptions)) {
          $requestValue = FilterRules::$filterOptions($requestValue);
          $filterOptions = $this->getConstant(gettype($requestValue));
        }
      }
    } else {
      $getConstantKey = $this->getConstant($requestKey);
      if (!is_null($getConstantKey)) {
        $filterOptions = $getConstantKey;
      } else {
        $reqValType = gettype($requestValue);
        if ($reqValType == 'string') $requestValue = FilterRules::clean_string($requestValue);

        $getConstantValue = $this->getConstant($reqValType);
        if (!is_null($getConstantValue)) {
          $filterOptions = $getConstantValue;
        }
      }
    }
    return $filterOptions;
  }

  /**
   * Compute the filter args for all keys and values in the request, update
   * the stored request with the FilterRules processed result.
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
   * Run the php `filter_var_array` function and store the results.
   *
   * The response is stored on the `finalFilterOutput` property
   */
  private function run(): int
  {
    $this->finalFilterOutput = filter_var_array($this->requestVars, $this->filterArgsOptions);
    return 0;
  }

  /**
   * Check the filtered output and generate and store a success/error response.
   *
   * The response is stored on the `filterResponse` property
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
}

/**
 * A class to store functions we might want to run on incoming data before
 * performing filtering.
 */
class FilterRules
{
  public static function check(string $func = null): bool
  {
    return (!is_null($func)) ? method_exists(__CLASS__, $func) : false;
  }

  public static function clean_string(string $string): string
  {
    return preg_replace("/[^A-Za-z0-9-_., ]/", "", $string);
  }

  public static function clean_tags(string $string): string
  {
    return strip_tags($string);
  }
}

// Local Variables:
// c-basic-offset: 2
// End:
