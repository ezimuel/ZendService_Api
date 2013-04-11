<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Service
 */
namespace ZendService\Api;

use Zend\Http\Client as HttpClient;

/**
 * Class to consume generic API calls using HTTP 
 * 
 * The specification of the API calls are managed by configuration files
 * using PHP associative array
 *  
 */
class Api {
    
    /**
     * Folder path of the API calls
     * 
     * @var string 
     */
    protected $pathApi;
    
    /**
     * Error message of the last HTTP call
     * 
     * @var string 
     */
    protected $errorMsg;
    
    /**
     * Status code of the last HTTP call
     * 
     * @var integer 
     */
    protected $statusCode;
    
    /**
     * Query parameters of the HTTP call
     * 
     * @var array 
     */
    protected $queryParams = array();
    
    /**
     * Default headers to be used during the HTTP call
     * 
     * @var array 
     */
    protected $headers = array();
    
    /**
     * Constructor
     * 
     * @param  string $pathApi
     * @param  HttpClient $httpClient
     * @throws Exception\InvalidArgumentException 
     */
    public function __construct($pathApi, HttpClient $httpClient = null)
    {
        $this->setPathApi($pathApi);
        $this->setHttpClient($httpClient ?: new HttpClient);
    }
    
    /**
     * Call a webservice
     * 
     * @param  string $name
     * @param  mixed $arguments
     * @throws Exception\InvalidArgumentException 
     * @return mixed|boolean
     */
    public function __call($name, $arguments)
    {
        // Check the API file data
        $fileName = $this->pathApi . '/' . $name . '.php';
        if (!file_exists($fileName)) {
            throw new Exception\RuntimeException("The API method $name doesn't exist");
        }
        
        // Check the API file parameters
        $fileParams = $this->pathApi . '/' . $name . '_params.php';
        if (!file_exists($fileParams)) {
            throw new Exception\RuntimeException("The API parameters file $name doesn't exist");
        } 
        
        // Read the API file parameters
        $params = include ($fileParams);
        $i = 0;
        foreach ($params['params'] as $var => $type) {
            if (!$this->checkType($arguments[$i], $type)) {
                throw new Exception\RuntimeException("The parameter $var must contain a value of $type");
            }
            $params['params'][$var] = $arguments[$i++];
        }
        extract($params);
        
        // Read the API file data
        $request = include ($fileName);
        if (empty($request) || !is_array($request)) {
            throw new Exception\RuntimeException("The API data, stored in $fileName, are not valid");
        }
        
        // HTTP request
        $client = $this->getHttpClient();
        $client->resetParameters();
        $this->errorMsg = null;
        $this->errorCode = null;
        $headers = array();
        if (!empty($this->headers)) {
            $headers = $this->getHeaders();
        }
        if (isset($request['header'])) {
            $headers = array_merge($headers, $request['header']);
        }
        $client->setHeaders($headers);
        $client->setMethod($request['method']);
        if (isset($request['body'])) {
            $client->setRawBody($request['body']);
        }
        $client->setUri($request['uri']);
        if (isset($request['response']['format'])) {
            $formatOutput = strtolower($request['response']['format']);
        }
        $validCodes = array(200);
        if (isset($request['response']['valid_codes'])) {
            $validCodes = $request['response']['valid_codes'];
        }
        if (!empty($this->queryParams)) {
            $client->setParameterGet($this->queryParams);
        }
        $response         = $client->send();
        $this->statusCode = $response->getStatusCode();
        if (in_array($this->statusCode, $validCodes)) {
            if (isset($formatOutput)) {
                if ($formatOuput === 'json') {
                    return json_decode($response->getBody(),true);
                } elseif ($formatOutput === 'xml') {
                    return new \SimpleXMLElement($response->getBody());
                }
            }
            return $response->getBody();
        }
        $this->errorMsg  = $response->getBody();
        return false;
    }
    
    /**
     * Check type of a $value
     * 
     * @param  mixed $value
     * @param  string $type
     * @return boolean 
     */
    protected function checkType($value, $type)
    {
        switch ($type) {
            case 'int' :
            case 'integer' :
                $valid = is_int($value);
                break;
            case 'float' :
            case 'double' :
                $valid = is_double($value);
                break;
            case 'bool' :
            case 'boolean' :
                $valid = is_bool($value);
                break;
            case 'array' :
                $valid = is_array($value);
                break;
            case 'string' :
                $valid = is_string($value);
                break;
            default:
                $valid = $value instanceof $type;
        }
        return $valid;
    }
    
    /**
     * Set the path API
     * 
     * @param  string $pathApi
     * @throws Exception\InvalidArgumentException 
     */
    public function setPathApi($pathApi)
    {
        if (!is_dir($pathApi)) {
            throw new Exception\InvalidArgumentException("Tha path $pathApi specified is not valid");
        }
        $this->pathApi = $pathApi;
        return $this;
    }
    
    /**
     * Get the path API
     * 
     * @return string 
     */
    public function getPathApi()
    {
        return $this->pathApi;
    }
    
    /**
     * Set the HTTP query params
     * 
     * @param  array $query
     * @return Api
     */
    public function setQueryParams(array $query = null)
    {
        $this->queryParams = $query;
        return $this;
    }
    
    /**
     * Get the HTTP query params
     * 
     * @return array 
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }
    
    /**
     * Set the HTTP headers
     * 
     * @param  array $headers
     * @return Api 
     */
    public function setHeaders(array $headers = null)
    {
        $this->headers = $headers;
        return $this;
    }
    
    /**
     * Get the HTTP headers
     * 
     * @return array 
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * @param  HttpClient $httpClient
     * @return Api
     */
    public function setHttpClient(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * get the HttpClient instance
     *
     * @return HttpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }
    
    /**
     * Get the error msg of the last HTTP call
     *
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * Get the status code of the last HTTP call
     *
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    
    /**
     * Success of the last HTTP call
     * 
     * @return boolean 
     */
    public function isSuccess()
    {
        return empty($this->errorMsg);
    }
}