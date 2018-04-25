<?php
//----------------------------------------------
//FILE NAME:  BaseController.php 
//DATE:		  2018-04-25(Wed) 11:11:57
//----------------------------------------------
use \Firebase\JWT\JWT;
class BaseController {

    /**
     * Mocking up user table you can use from db 
     */
    protected $listUser = array(
        'admin@domain.tld' => array('email' => 'admin@domain.tld', 'password' => 'adminPass', 'role' => 'admin'),
        'user@domain.tld' => array('email' => 'user@domain.tld', 'password' => 'userPass', 'role' => 'user')
    );

    /**
     * Security
     * How to gen private and public key from openssl 
     * # private key
     * openssl genrsa -out testkey 4096
     * # public key
     * openssl rsa -in testkey -pubout > testkey.pub
     * 
     */
    public $private_key = __DIR__ . DIRECTORY_SEPARATOR . 'testkey';
    public $public_key = __DIR__ . DIRECTORY_SEPARATOR . 'testkey.pub';
    public $hash_type = 'RS256';

    /**
     * Logged in user
     */
    protected $loggedUser = null;

    /**
     * Check client credentials and return true if found valid, false otherwise
     */
    public function authenticate($credentials, $auth_type)
    {
        switch ($auth_type) {
            case 'Bearer':
                $public_key = file_get_contents($this->public_key);
                $token = JWT::decode($credentials, $public_key, array($this->hash_type));
                if ($token && !empty($token->username) && $this->listUser[$token->username]) {
                    $this->loggedUser = $this->listUser[$token->username];
                    return true;
                }
                break;

            case 'Basic':
            default:
                $email = $credentials['username'];
                if (isset($this->listUser[$email]) && $this->listUser[$email]['password'] == $credentials['password']) {
                    $this->loggedUser = $this->listUser[$email];
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Check if current user is allowed to access a certain method
     */
    public function authorize($method)
    {
        if ('admin' == $this->loggedUser['role']) {
            return true; // admin can access everthing

        } else if ('user' == $this->loggedUser['role']) {
          // user can access selected methods only
            if (in_array($method, array('download'))) {
                return true;
            }
        }

        return false;
    }



}

