<?php
namespace Sendy;

/**
 * Handy helpers for the API
 *
 * @author Wade Shuler https://github.com/WadeShuler
 */
Class ApiHelpers
{
    /**
     * Ensures the request is POST.
     *
     * @return bool Returns true if method is POST, otherwise false.
     */
    public static function isPost()
    {
        if ( isset($_POST) && ! empty($_POST) ) {
            return true;
        }

        return false;
    }

    /**
     * Check if the request is an AJAX request.
     * This looks for the `X-Requested-With` header with the value of `XMLHttpRequest`.
     * It is recommended to ensure your scripts pass this header with all AJAX requests.
     *
     * @return boolean Whether or not the request is an AJAX request
     */
    public static function isAjax()
    {
        return self::getHeader('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Get the user's IP Address with proxy detection support.
     *
     * @return string|bool Returns the IP Address, or false if it wasn't detected.
     */
    public static function getIpAddress()
    {
        foreach ( ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'] as $key) {
            if ( array_key_exists($key, $_SERVER) === true ) {
                foreach ( array_map('trim', explode(',', $_SERVER[$key])) as $ip ) {
                    if ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false ) {
                        return $ip;
                    }
                }
            }
        }

        return false;
    }

    public static function getReferrer()
    {
        return self::getHeader('Referer');
    }

    public static function ajaxHeaders()
    {
        header( 'Content-Type: application/json' );
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Request-With' );
        header( 'Access-Control-Allow-Methods: POST, GET' );
        header( 'Access-Control-Allow-Credentials: true' );
    }

    /**
     * Check whether an email address is valid or not.
     *
     * @param string $email An email address to check
     * @return boolean Whether or not the email address is valid
     */
    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Get the key's value from the $_POST global.
     *
     * `Example: ArrayHelpers::getPost('email')`
     *
     * @param string $key The key to retrieve from $_POST
     * @return mixed Returns the value from $_POST, or empty string if key not found
     */
    public static function getPost($key)
    {
        if ( isset($_POST[$key]) && ! empty($_POST[$key]) ) {
            return $_POST[$key];
        }

        return '';
    }

    /**
     * Get the key's value from the $_GET global.
     *
     * `Example: ArrayHelpers::getGet('email')`
     *
     * @param string $key The key to retrieve from $_GET
     * @return mixed Returns the value from $_GET, or empty string if key not found
     */
    public static function getGet($key)
    {
        if ( isset($_GET[$key]) && ! empty($_GET[$key]) ) {
            return $_GET[$key];
        }

        return '';
    }

    public static function getHeader($key)
    {
        if ( function_exists('getallheaders') )
        {
            $headers = getallheaders();

            foreach ($headers as $header => $value)
            {
                if ( strtolower($header) === strtolower($key) ) {
                    return $value;
                }
            }

        } else if ( function_exists('http_get_request_headers') ) {

            $headers = http_get_request_headers();

            foreach ($headers as $header => $value)
            {
                if ( strtolower($header) === strtolower($key) ) {
                    return $value;
                }
            }

        } else {

            foreach ($_SERVER as $header => $value)
            {
                // Grab only headers that start with `HTTP_`
                if ( strncmp($header, 'HTTP_', 5) === 0 )
                {
                    $header = str_replace(' ', '-', ucwords( strtolower( str_replace('_', ' ', substr($header, 5)) )));
                    if ( strtolower($header) === strtolower($key) ) {
                        return $value;
                    }
                }
            }

        }

        return false;
    }

}
