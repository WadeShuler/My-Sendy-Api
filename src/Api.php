<?php
namespace Sendy;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

use Sendy\ApiConfig;

/**
 * Api is a Guzzle wrapper.
 *
 * @author Wade Shuler https://github.com/WadeShuler
 */
Class Api
{
    /**
     * Contains the ApiConfig object with all this classes settings.
     * @var ApiConfig
     */
    private $_config;

    /**
     * A Guzzle Client object
     * @var Client
     */
    private $_client;

    /**
     * Api constructor to to build our baselines before use.
     * @param Array $config The Api config array
     */
    public function __construct(Array $config)
    {
        // Store the ApiConfig
        // ApiConfig automatically validates the config data for us.
        $this->_config = new ApiConfig($config);
        $this->_client = $this->getClient();
    }

    /**
     * Getter for `$this->_client`.
     *
     * Returns the Guzzle client if it exists, else creates one.
     * @return Client Returns a Guzzle Client Object
     */
    public function getClient()
    {
        if ( ! isset($this->_client) )
        {
            $clientConfig = [
                'base_uri' => $this->_config->baseUri,
                'timeout' => $this->_config->timeout,
            ];

            // @todo Test passing headers here into Client...
            if ( isset($this->_config->headers) && ! empty($this->_config->headers) && is_array($this->_config->headers) ) {
                $clientConfig['headers'] = $this->_config->headers;
            }

            $this->_client = new Client($clientConfig);
        }

        return $this->_client;
    }

    /**
     * Setter for `$this->_client`.
     *
     * @question Maybe this should be private or protected?
     *
     * @param Client $client A Guzzle Client object
     */
    public function setClient(Client $client)
    {
        $this->_client = $client;

        return $this->_client;
    }

    /**
     * Getter for `$this->_config`.
     *
     * @question Maybe this should be private or protected?
     *
     * @return ApiConfig Returns an ApiConfig Object
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Setter for `$this->_config`.
     *
     * @question Maybe this should be private or protected?
     *
     * @param ApiConfig $config An ApiConfig Object.
     */
    public function setConfig(ApiConfig $config)
    {
        $this->_config = $config;

        return $this->_config;
    }

    /**
     * Terminate the script execution.
     *
     * This will use `die()` to return an array with status information. If `$this->_config->ajax` is `true`,
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

        $response = ($this->_config->ajax === true) ? json_encode($array) : print_r($array, true);
        die( $response );
    }

}
