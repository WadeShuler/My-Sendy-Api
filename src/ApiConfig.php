<?php
namespace Sendy;

use Sendy\ApiHelpers;

/**
 * ApiConfig is a Config Helper Class for `Api`.
 *
 * This helps with properly formatting the data for use with the main `Api` class.
 *
 * @NOTE: This class isn't meant to be used directly.
 *
 * @todo Maybe add magic getter to get class variables via `$this->config()->get('param')` ?
 *
 * @author Wade Shuler https://github.com/WadeShuler
 */
Class ApiConfig
{
    /**
     * The Base Uri to be set as a default for all subsequent requests.
     *
     * Must be a fully-qualified domain name (FQDN). The extendor of the `API` class will later
     * add the appropriate action paths to this baseUri.
     *
     * Base URI Example: `https://domain.com`
     *
     * @var String
     */
    public $baseUri;

    /**
     * The connection timeout in seconds for the API request.
     *
     * @var Int
     */
    public $timeout = 30;

    /**
     * The array of headers to be sent with the request.
     *
     * @var Array
     */
    public $headers;

    /**
     * Whether or not we are handling an AJAX request
     * Set this to true when using AJAX
     *
     * @var bool
     */
    public $ajax = false;

    /**
     * ApiConfig constructor to build our baselines before using the `Api` class.
     *
     * @param Array $config The config array
     */
    public function __construct(Array $config)
    {
        $this->validateConfig($config);
        $this->loadSettings($config);
    }

    /**
     * Validate the settings passed to this Object.
     *
     * @param  Array  $config The config passed to `__construct($config)`
     *
     * @return bool Returns true if everything passed. Else, it will terminate with an error.
     */
    private function validateConfig(Array $config)
    {
        // Required

        if ( ! isset($config['baseUri']) ) {
            $this->terminate(['status' => false, 'message' => 'ApiConfig Error: API URL Missing!']);
        }

        if ( ! filter_var($config['baseUri'], FILTER_VALIDATE_URL) ) {
            $this->terminate(['status' => false, 'message' => 'ApiConfig Error: Invalid API URL!']);
        }

        // Optional

        if ( isset($config['timeout']) && ! is_int($config['timeout']) || ! ($config['timeout'] > 0)) {
            $this->terminate(['status' => false, 'message' => 'ApiConfig Error: timeout must be a valid integer!']);
        }

        if ( isset($config['headers']) && ! is_array($config['headers']) ) {
            $this->terminate(['status' => false, 'message' => 'ApiConfig Error: headers must be an array!']);
        }

        if ( isset($config['ajax']) && ! is_bool($config['ajax']) ) {
            $this->terminate(['status' => false, 'message' => 'ApiConfig Error: ajax Must be a boolean!']);
        }

        return true;
    }

    /**
     * Load the settings into this (ApiConfig) Object.
     *
     * Call `validateConfig()` before calling this function. The data passed to this function should have
     * already been verified by running `validateConfig()`!
     *
     * @param Array $config Array of config options from `config.php`.
     *
     * @return bool Returns true indicating the settings were loaded.
     */
    private function loadSettings(Array $config)
    {
        // load the required baseUri
        $this->baseUri = $config['baseUri'];

        // Check if each optional param is passed

        if ( isset($config['timeout']) ) {
            $this->timeout = $config['timeout'];
        }

        if ( isset($config['headers']) ) {
            $this->headers = $config['headers'];
        }

        if ( isset($config['ajax']) ) {
            $this->ajax = $config['ajax'];
        }

        return true;
    }

    /**
     * Terminate the script execution.
     *
     * @WARNING Slightly modified from terminate used in `Api`!
     *
     * This will use `die()` to return an array with status information. If `$this->ajax` is `true`,
     * if will use `json_encode` to return the array as JSON.
     *
     * It requires at least `status` and `message` to be passed. Other keys will be returned as well.
     *
     * Examples:
     *
     *   `['status' => true/false, 'message' => 'Subscriber has been added!']`
     *       -or-
     *   `['status' => true/false, 'message' => '', 'data' => ['some' => 'array of data']]`
     *
     * @todo Refactor better way to handle `terminate()`.
     *
     * @param array $array An array properly laid out for a JSON message. (see above example)
     */
    public function terminate(Array $array)
    {
        if ( ! isset($array['status'], $array['message']) ) {
            $array = ['status' => false, 'message' => 'Api Error: Malformed terminate()!'];
        }

        $response = ($this->ajax === true) ? json_encode($array) : print_r($array, true);
        die( $response );
    }
}
