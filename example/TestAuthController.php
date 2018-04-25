<?php
/* 
*********************************************************************
****************************** NOTICE  ******************************
*********************************************************************

Before testing this example, you must full fill following requirements

1. Generate private and public keys pair in same directory as (testkey and testkey.pub)
2. install php-jwt ie. run `composer require firebase/php-jwt`

*/

use \Firebase\JWT\JWT;
use \Jacwright\RestServer\RestException;

class TestAuthController extends BaseController 
{

    /**
     * To get JWT token client can post his username and password to this method
     *
     * @noAuth
     * @url POST /login
     */
    public function login($data = array())
    {
        $username = isset($data['username']) ? $data['username'] : null;
        $password = isset($data['password']) ? $data['password'] : null;
        // // only if we have valid user
        if (isset($this->listUser[$username]) && $this->listUser[$username]['password'] == $password) {
            $token = array(
                "iss" => 'My Website',
                "iat" => time(),
                "nbf" => time(),
                "exp" => time() + (60 * 60 * 24 * 30 * 12 * 1), // valid for one year
                "username" => $this->listUser[$username]['email']
            );

            // return jwt token
            $private_key = file_get_contents($this->private_key);
            return JWT::encode($token, $private_key, $this->hash_type);
        }

        throw new RestException(401, "Invalid username or password");
    }


    
    /**
    *@noAuth
    *@url GET /checkkey
    */
    public function checkkey($data){
      $header = $_SERVER['HTTP_AUTHORIZATION'];
      list(,$token) = explode(' ',$header);
      $public_key = file_get_contents($this->public_key);
      $token = JWT::decode($token, $public_key, array($this->hash_type));
      return $token;
    }
    
    
    /**
    *@url POST /testtoken
    */
    public function testtoken($data){
      return [
        'status'=>'OK',
        'data' => $data,
        'loginuser'=>$this->loggedUser
      ];
    }
    
    

    /**
     * Upload a file
     *
     * @url PUT /files/$filename
     */
    public function upload($filename, $data, $mime)
    {
        $storage_dir  = sys_get_temp_dir();
        $allowedTypes = array('pdf' => 'application/pdf', 'html' => 'plain/html', 'wav' => 'audio/wav');
        if (in_array($mime, $allowedTypes)) {
          if (!empty($data)) {
            $file_path = $storage_dir . DIRECTORY_SEPARATOR . $filename;
            file_put_contents($file_path, $data);
            return $filename;
          } else {
            throw new RestException(411, "Empty file");
          }
        } else {
          throw new RestException(415, "Unsupported File Type");
        }
    }

    /**
     * Download a file
     *
     * @url GET /files/$filename
     */
    public function download($filename)
    {
        $storage_dir = sys_get_temp_dir();
        $file_path = $storage_dir . DIRECTORY_SEPARATOR . $filename;
        if (file_exists($file_path)) {
          return SplFileInfo($file_path);
        } else {
          throw new RestException(404, "File not found");
        }
    }

}
