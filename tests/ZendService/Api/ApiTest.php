<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Service
 */

namespace ZendServiceTest\Api;

use ZendService\Api\Api;
use Zend\Http\Client\Adapter\Test as HttpTest;
use Zend\Http\Client as HttpClient;

/**
 * @category   Zend
 * @package    ZendService\Api
 */
class ApiTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->api   = new Api(__DIR__ . '/_files');
        $httpAdapter = new HttpTest;
        $this->api->getHttpClient()->setAdapter($httpAdapter);
        
        $fileResponse = __DIR__ . '/_files/'. $this->getName() . '.response';
        if (file_exists($fileResponse)) {
            $httpAdapter->setResponse(file_get_contents($fileResponse));
        }
    }
    
    public function testConstruct()
    {
        $api = new Api(__DIR__);
        $this->assertEquals(__DIR__, $api->getPathApi());
        $this->assertTrue($api->getHttpClient() instanceof HttpClient);
    }
    
    /**
     * @expectedException InvalidArgumentException 
     */
    public function testFailConstruct()
    {
        $api = new Api('test');
    }
    
    /**
     * @expectedException RuntimeException 
     */
    public function testWrongApi()
    {
        $result = $this->api->foo('bar');
    }
    
    public function testSetPathApi()
    {
        $result = $this->api->setPathApi(__DIR__);
        $this->assertTrue($result instanceof Api);
        $this->assertEquals(__DIR__, $this->api->getPathApi());
    }
    
    public function testSetUri()
    {
        $url = 'http://localhost';
        $result = $this->api->setUri($url);
        $this->assertTrue($result instanceof Api);
        $this->assertEquals($url, $this->api->getUri());
    }
    
    public function testSetEmptyUri()
    {
        $url = 'http://localhost';
        $result = $this->api->setUri($url);
        $this->assertEquals($url, $this->api->getUri());
        $result = $this->api->setUri();
        $this->assertEquals(null, $this->api->getUri());
    }
    
    public function testSetQueryParams()
    {
        $queryParams = array('foo' => 'bar');
        $result = $this->api->setQueryParams($queryParams);
        $this->assertTrue($result instanceof Api);
        $this->assertEquals($queryParams, $this->api->getQueryParams());
    }
    
    public function testSetEmptyQueryParams()
    {
        $queryParams = array('foo' => 'bar');
        $result = $this->api->setQueryParams($queryParams);
        $this->assertEquals($queryParams, $this->api->getQueryParams());
        $result = $this->api->setQueryParams();
        $this->assertEquals(null, $this->api->getQueryParams());
    }
    
    public function testSetHeaders()
    {
        $headers = array('Content-Type' => 'application/json');
        $result = $this->api->setHeaders($headers);
        $this->assertTrue($result instanceof Api);
        $this->assertEquals($headers, $this->api->getHeaders());
    }
    
    public function testSetEmptyHeaders()
    {
        $headers = array('Content-Type' => 'application/json');
        $result = $this->api->setHeaders($headers);
        $this->assertEquals($headers, $this->api->getHeaders());
        $result = $this->api->setHeaders();
        $this->assertEquals(null, $this->api->getHeaders());
    }
    
    public function testSetHttpClient()
    {
        $httpClient = new HttpClient();
        $result = $this->api->setHttpClient($httpClient);
        $this->assertTrue($result instanceof Api);
        $this->assertEquals($httpClient, $this->api->getHttpClient());
    }
    
    public function testApi()
    {
        $result = $this->api->test('foo', 'bar');
        $this->assertTrue($this->api->isSuccess());
        $this->assertEquals('This is a test!', $result);
    }
    
    public function testError()
    {
        $result = $this->api->test('foo', 'bar');
        $this->assertFalse($this->api->isSuccess());
        $this->assertEquals('Error', $this->api->getErrorMsg());
        $this->assertEquals(500, $this->api->getStatusCode());
        $this->assertTrue(empty($result));
    }
}
