<?php
namespace Sendy;

use Sendy\Api;
use Sendy\ApiConfig;
use Sendy\ApiHelpers;
use Sendy\SendyActions;
use Sendy\SendyConfig;

/**
 * SendyApi is the Class that is directly used by your frontend app.
 *
 * @todo Move constants to their own class.
 *
 * @author Wade Shuler https://github.com/WadeShuler
 */
Class SendyApi extends Api
{
    /**
     * Contains the SendyConfig object with all this classes settings. (array from your `config.php`).
     * @var array
     */
    private $config;

    /**
     * The Sendy Config object for the API
     * @var SendyConfig
     */
    private $sendyConfig;

    /**
     * The Api Config object for the API
     * @var ApiConfig
     */
    private $apiConfig;

    /**
     * Holds the raw data from $_POST
     * @var array
     */
    private $postData;


    // @TODO Move constants to their own Class


    /**
     * SendyApi constructor to build our baselines before use.
     * @param array $config The Sendy config array
     */
    public function __construct(Array $config)
    {
        If ( ! ApiHelpers::isPost() ) {
            $this->terminate(['status' => false, 'message' => 'SendyApi Error: Invalid POST Data!']);
        }

        // Store the SendyConfig
        // SendyConfig automatically validates the config data for us.
        $this->sendyConfig = new SendyConfig($config);


        // Store the config for later reference
        $this->config = $config;

        // Store the POST data for later reference
        $this->postData = $_POST;


        /*
         all of this is now in SendyConfig

        $this->sendyUrl = $config['sendyUrl'];
        $this->apiKey = $config['apiKey'];
        $this->listId = $config['listId'];

        if ( isset($config['timeout']) ) {
            $this->timeout = $config['timeout'];
        }

        if ( isset($config['forceIpAddress']) ) {
            $this->passIpAddress = $config['forceIpAddress'];
        }

        if ( isset($config['forceReferrer']) ) {
            $this->passReferrer = $config['forceReferrer'];
        }

        if ( isset($config['forceGdpr']) ) {
            $this->passGdpr = $config['forceGdpr'];
        }
        // end of SendyConfig data
        */


        // @todo what about passing headers, here??

        // Build our ApiConfig for Guzzle
        /*
        $this->apiConfig = new ApiConfig([
            'baseUri' => $this->sendyUrl,
            'timeout' => $this->timeout,
        ]);
        */

        $apiConfig = [
            'baseUri' => $this->sendyConfig->sendyUrl,
            'timeout' => $this->sendyConfig->timeout,
            'ajax' => $this->sendyConfig->ajax,
        ];

        parent::__construct($apiConfig);
    }

    /**
     * Subscribes the user to the Sendy list.
     *
     * @return string The response from the Sendy server.
     */
    public function subscribe($returnResponse = false)
    {
        // Do not overwrite `$this->postData`.. ever.. :)
        $postData = $this->postData;

        // Do not overwrite `$this->listId`.. ever.. :)
        $list = $this->sendyConfig->listId;

        // subscribe requires `email`
        if ( ! isset($postData['email']) || ! ApiHelpers::isValidEmail($postData['email']) ) {
            $this->terminate(['status' => false, 'message' => 'SendyApi Error: The subscribe action requires email']);
        }

        /**
         * @NOTE: The point below is to remove un-necessary keys from the `postData`. Everything
         * in `postData` is raw from $_POST sent via the HTML webform on their site.
         */

        // Remove `boolean` param, we will handle it (not useful)
        if ( isset($postData['boolean']) ) {
            unlink($postData['boolean']);
        }

        // Override if `list` was passed from the web form
        if ( isset($postData['list']) ) {
            $list = $postData['list'];      // Grab the `list`
            unlink($postData['list']);      // remove `list` from the $postData array
        }

        // build our base parameters
        $baseParams = [
            'list' => $list,        // set the list in $postData
            'boolean' => 'true',    // required for API use
        ];

        // Check if config is set to pass the IP Address
        if ( $this->sendyConfig->passIpAddress ) {
            if ( $ipAddress = ApiHelpers::getIpAddress() ) {
                $baseParams['ip_address'] = $ipAddress;
            }
        }

        // Check if config is set to pass the Referrer
        if ( $this->sendyConfig->passReferrer ) {
            if ( $referrer = ApiHelpers::getReferrer() ) {
                $baseParams['referrer'] = $referrer;
                if ( isset($postData['referrer']) ) {
                    unlink($postData['referrer']);
                }
            }
        }

        // Check if config is set to passs the GDPR compliant param
        if ( $this->sendyConfig->passGdpr ) {
            $baseParams['gdpr'] = 'true';
        }

        // Merge the cleaned postData with our baseParams
        $data = [
            'form_params' => array_merge($baseParams, $postData),
        ];

        $response = null;

        try
        {
            // @todo rename to remove Guzzle reference.
            $guzzleResponse = $this->getClient()->post(SendyActions::SUBSCRIBE, $data);
            $response = $guzzleResponse->getBody()->getContents();

        } catch (RequestException $e) {

            $this->terminate(['status' => false, 'message' => $e->getResponse]);

        }

        $status = false;

        switch ( $response )
        {
			case 'true':
			case '1':
				$status = true;
                break;

			default:
				$status = false;
                break;
		}

        $responseArray = ['status' => $status, 'message' => $response];

        return json_encode($responseArray);
    }

}
