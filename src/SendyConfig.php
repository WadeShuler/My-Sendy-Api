<?php
namespace Sendy;

/**
 * SendyConfig is a Config Helper Class for `SendyApi`.
 *
 * This helps with properly formatting the data for use with the main `SendyApi` class.
 *
 * @NOTE: This class isn't meant to be used directly.
 *
 * @todo Maybe add magic getter to get class variables via `$this->config()->get('param')` ?
 * @todo Refactor better way to handle `terminate()`
 *
 * @author Wade Shuler https://github.com/WadeShuler
 */
Class SendyConfig
{
    /**
     * The base Sendy URL (ex: https://sendy.example.com)
     * @var string
     */
    public $sendyUrl;

    /**
     * The Sendy API Key
     * @var string
     */
    public $apiKey;

    /**
     * The hashed list ID from your backoffice
     * @var string
     */
    public $listId;

    /**
     * Enable for AJAX use.
     *
     * True: This will force all responses to be a JSON encoded array.
     * False: This will just return an array.
     *
     * @var bool
     */
    public $ajax = false;

    /**
     * The connection timeout in seconds for the API request.
     *
     * This timeout value with be used while instantiating the `Guzzle` Client in the `API` class.
     *
     * @var Int
     */
    public $timeout = 30;

    /**
     * Whether or not to automatically lookup and pass the IP Address to Sendy.
     * If IP lookup fails, it will be omitted.
     *
     * @var bool
     */
    public $passIpAddress = false;

    /**
     * Whether or not to automatically pass the Referrer.
     * If if fails, the `referrer` may be omitted or empty.
     *
     * @var bool
     */
    public $passReferrer = false;

    /**
     * Whether or not to automatically pass the `gdpr` param, indicating GDPR compliance.
     * @var bool
     */
    public $passGdpr = false;

    /**
     * SendyConfig constructor to build our baselines before using the `SendyApi` class.
     *
     * @param Array $config A properly formatted options array from your `config.php`
     */
    public function __construct(Array $config)
    {
        $this->validateSendyConfig($config);
        $this->loadSettings($config);
    }

    /**
     * Validates the configuration options for `SendyApi`.
     *
     * @param Array $config Array of config options from `config.php`.
     *
     * @return bool Returns true if everything was valid.
     */
    public function validateSendyConfig(Array $config)
    {
        // Required

        if ( ! isset($config['sendyUrl']) || empty($config['sendyUrl']) ) {
            $this->terminate(['status' => false, 'message' => 'SendyConfig Error: Sendy URL Missing!']);
        }

        if ( ! filter_var($config['sendyUrl'], FILTER_VALIDATE_URL) ) {
            $this->terminate(['status' => false, 'message' => 'SendyConfig Error: Invalid Sendy URL!']);
        }

        if ( ! isset($config['apiKey']) || empty($config['apiKey']) ) {
            $this->terminate(['status' => false, 'message' => 'SendyConfig Error: API Key Missing!']);
        }

        if ( ! is_string($config['apiKey']) ) {
            $this->terminate(['status' => false, 'message' => 'SendyConfig Error: API Key must be a string!']);
        }

        if ( ! isset($config['listId']) || empty($config['listId']) ) {
            $this->terminate(['status' => false, 'message' => 'SendyConfig Error: List ID Missing!']);
        }

        if ( ! is_string($config['listId']) ) {
            $this->terminate(['status' => false, 'message' => 'SendyConfig Error: List ID must be a string!']);
        }

        // Optional

        if ( isset($config['ajax']) && ! is_bool($config['ajax']) ) {
            $this->terminate(['status' => false, 'message' => 'SendyConfig Error: ajax Must be a boolean!']);
        }

        if ( isset($config['timeout']) && ! is_int($config['timeout']) || ! ($config['timeout'] > 0) ) {
            $this->terminate(['status' => false, 'message' => 'SendyConfig Error: timeout Must be a valid integer!']);
        }

        // We use `forceXxx` in the main app's config, but `passXxx` internall, so it makes sense
        // from that viewpoint. In API we want to `pass` it, in config we want to `force` it.
        // @todo Move comment to doc block

        if ( isset($config['forceIpAddress']) && ! is_bool($config['forceIpAddress']) ) {
            $this->terminate(['status' => false, 'message' => 'SendyConfig Error: forceIpAddress Must be a boolean!']);
        }

        if ( isset($config['forceReferrer']) && ! is_bool($config['forceReferrer']) ) {
            $this->terminate(['status' => false, 'message' => 'SendyConfig Error: forceReferrer Must be a boolean!']);
        }

        if ( isset($config['forceGdpr']) && ! is_bool($config['forceGdpr']) ) {
            $this->terminate(['status' => false, 'message' => 'SendyConfig Error: forceGdpr Must be a boolean!']);
        }

        return true;
    }

    public function loadSettings(Array $config)
    {
        // Required

        $this->sendyUrl = $config['sendyUrl'];
        $this->apiKey = $config['apiKey'];
        $this->listId = $config['listId'];

        // Optional

        if ( isset($config['ajax']) ) {
            $this->ajax = $config['ajax'];
        }

        if ( isset($config['timeout']) ) {
            $this->timeout = $config['timeout'];
        }

        // Here is where `forceIpAddress` turns into `passIpAddress`.
        if ( isset($config['forceIpAddress']) ) {
            $this->passIpAddress = $config['forceIpAddress'];
        }

        // Here is where `forceReferrer` turns into `passReferrer`.
        if ( isset($config['forceReferrer']) ) {
            $this->passReferrer = $config['forceReferrer'];
        }

        // Here is where `forceReferrer` turns into `passReferrer`.
        if ( isset($config['forceGdpr']) ) {
            $this->passGdpr = $config['forceGdpr'];
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
