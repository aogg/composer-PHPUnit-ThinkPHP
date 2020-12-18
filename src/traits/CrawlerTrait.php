<?php
/**
 * User: aogg
 * Date: 2020/8/3
 */

namespace aogg\phpunit\think\traits;

use think\Exception;
use think\exception\ThrowableError;
use think\facade\App;
use think\facade\Cookie;
use think\facade\Route;
use think\helper\Arr;
use think\helper\Str;

/**
 * 调用控制器
 *
 * @see https://github.com/top-think/think-testing/blob/2.0/src/CrawlerTrait.php 参考
 * @package aogg\phpunit\think\traits
 */
trait CrawlerTrait
{

    protected $currentUri;

    protected $baseUrl = '';

    protected $serverVariables = [];

    /** @var  \think\Response */
    protected $response;

    protected $saveCookieKey = '';

    protected $app;

    /**
     * 内部缓存用
     * 与app隔离开
     *
     * @var \think\cache\Driver
     */
    protected static $consoleCacheStore;

    public function get($uri, array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers, parse_url($uri, PHP_URL_PATH), 'GET');

        $this->call('GET', $uri, [], $server);

        return $this;
    }

    public function post($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers, parse_url($uri, PHP_URL_PATH), 'POST');

        $this->call('POST', $uri, $data, $server);

        return $this;
    }

    public function put($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers, parse_url($uri, PHP_URL_PATH), 'PUT');

        $this->call('PUT', $uri, $data, $server);

        return $this;
    }

    public function delete($uri, array $data = [], array $headers = [])
    {
        $server = $this->transformHeadersToServerVars($headers, parse_url($uri, PHP_URL_PATH), 'DELETE');

        $this->call('DELETE', $uri, $data, $server);

        return $this;
    }

    public function getApp($cache = false, $setApp = true)
    {
        if ($cache && $this->app){
            return $this->app;
        }

        $app = new \think\App();

        $app->initialize();

        if (!$setApp){
            return $app;
        }

        return $this->app = $app;
    }

    /**
     * 获取在test的session
     * 只有$this->setSaveCookieKey()才可以，而且要在之后发生session才有值
     *
     * 不会检测session是否过期与失效
     *
     * @return array
     */
    public function getTestSessionAll()
    {
        if (!$this->getSaveCookieKey()) {
            return [];
        }

        $sessionAll = self::getConsoleCacheStorePri()->get($this->getSaveCookieKey() . '-session');

        return $sessionAll?:[];
    }

    private static function getConsoleCacheStorePri()
    {
        return self::$consoleCacheStore?:cache()->store();
    }

    public function call($method, $uri, $post = [], $server = [])
    {
        $this->currentUri = $this->prepareUrlForRequest($uri);

        // 模拟调用
        // 无法通过新类实现request，因为一般应用会定义新的request，然后注入的时候写这个类名

        $tempGlobals = $GLOBALS;
        unset($GLOBALS['argv']);


        $app = $this->getApp(true);
        $app->phpunit = true;

        // 执行HTTP应用并响应

        $http = $app->http;

        /** @var \think\Request $request */
        $request = $app->make('request');

        $_SERVER = $server;
        parse_str(parse_url($uri, PHP_URL_QUERY), $_GET);
        $_POST = $post;
        $_REQUEST = array_merge($_GET, $_POST);


        $_COOKIE = [];
        /** @var \think\Cache $cache */
        $cache = self::getConsoleCacheStorePri();
        if ($this->getSaveCookieKey()) { // 获取cookie
            $cookie = $cache->get($this->getSaveCookieKey()) ?: [];
            foreach ($cookie as $cookieKey => $cookieItem) {
                $cookieValue = isset($cookieItem[0])?$cookieItem[0]:'';

                $_COOKIE[$cookieKey] = $cookieValue;
            }

            // 处理当前的session
//            $sessionId = $cache->get($this->getSaveCookieKey() . '-session-id') ?: '';
//            $sessionId && $app->session->setId($sessionId);

            // 处理多模块bug，未确定app()和$app
            app()->session->forgetDriver();
        }


        // 暂无
        $_FILES = [];
        $request = $request->__make($app);
        $request->setMethod($method);
        $request->setUrl($this->currentUri);


        $response = $http->run($request);

        if ($this->getSaveCookieKey()){ // 保存cookie
            $cache->set($this->getSaveCookieKey() . '-session', $app->session->all());
//            $cache->set($this->getSaveCookieKey() . '-session-id', $app->session->getId());
            $cache->set($this->getSaveCookieKey(), $app->cookie->getCookie());
        }
        $http->end($response);


        $GLOBALS = $tempGlobals;

        return $this->response = $response;
    }


    protected function transformHeadersToServerVars(array $headers, $uri = '', $method = 'GET')
    {
        $publicPath = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
        // 模拟server
        $server = array (
            'HTTP_X_REQUESTED_WITH' => 'xmlhttprequest',
            'SCRIPT_NAME' => '/index.php',
            'REQUEST_METHOD' => $method,
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SCRIPT_FILENAME' => "{$publicPath}public/index.php",
            'CONTEXT_PREFIX' => '',
            'REQUEST_SCHEME' => 'http',
            'DOCUMENT_ROOT' => "{$publicPath}/public",
            'REMOTE_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '80',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_NAME' => 'localhost',
            'SERVER_SOFTWARE' => 'Apache/2.4.39 (Win64) OpenSSL/1.1.1b mod_fcgid/2.3.9a',
            'SERVER_SIGNATURE' => '',
            'HTTP_ACCEPT_LANGUAGE' => 'zh-CN,zh;q=0.9',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36',
            'HTTP_CONNECTION' => 'close',
            'HTTP_HOST' => 'localhost',
            'REDIRECT_STATUS' => '200',
            'FCGI_ROLE' => 'RESPONDER',
            'PHP_SELF' => '/index.php',
            'REQUEST_TIME_FLOAT' => microtime(true),
            'REQUEST_TIME' => time(),
            'HTTP_X_PHPUNIT' => 1, // phpunit的标识
        );

        if (!empty($uri)) {
            $server['REQUEST_URI'] = isset($server['REQUEST_URI']) ? $server['REQUEST_URI'] : $uri;
            $server['QUERY_STRING'] = isset($server['QUERY_STRING']) ? $server['QUERY_STRING'] : $uri;
            $server['REDIRECT_QUERY_STRING'] = isset($server['REDIRECT_QUERY_STRING']) ? $server['REDIRECT_QUERY_STRING'] : $uri;
            $server['REDIRECT_URL'] = isset($server['REDIRECT_URL']) ? $server['REDIRECT_URL'] : $uri;
        }


        $prefix = 'HTTP_';

        foreach ($headers as $name => $value) {
            $name = strtr(strtoupper($name), '-', '_');

            if (!\think\helper\Str::startsWith($name, $prefix) && 'CONTENT_TYPE' != $name) {
                $name = $prefix . $name;
            }

            $server[$name] = $value;
        }


        // \think\app\MultiApp::getScriptName的bug
        $_SERVER['SCRIPT_FILENAME'] = $server['SCRIPT_FILENAME'];


        return $server;
    }

    public function seeJson($data = null, $negate = false)
    {
        if (is_null($data)) {
            $this->assertJson(
                $this->response->getContent(), "JSON was not returned from [{$this->currentUri}]."
            );

            return $this;
        }

        return $this->seeJsonContains($data, $negate);
    }

    /**
     * 对比json是否相等
     *
     * @param array $data
     * @return $this
     */
    public function seeJsonEquals(array $data)
    {
        $actual = json_encode(Arr::sortRecursive(
            json_decode($this->response->getContent(), true)
        ));

        $this->assertEquals(json_encode(Arr::sortRecursive($data)), $actual);

        return $this;
    }

    protected function seeJsonContains(array $data, $negate = false)
    {
        $method = $negate ? 'assertFalse' : 'assertTrue';

        $actual = json_decode($this->response->getContent(), true);

        if (is_null($actual) || false === $actual) {
            return $this->fail('Invalid JSON was returned from the route. Perhaps an exception was thrown?');
        }

        $actual = json_encode(Arr::sortRecursive(
            (array) $actual
        ));

        foreach (Arr::sortRecursive($data) as $key => $value) {
            $expected = $this->formatToExpectedJson($key, $value);

            $this->{$method}(
                Str::contains($actual, $expected),
                ($negate ? 'Found unexpected' : 'Unable to find') . " JSON fragment [{$expected}] within [{$actual}]."
            );
        }

        return $this;
    }

    /**
     * Format the given key and value into a JSON string for expectation checks.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return string
     */
    protected function formatToExpectedJson($key, $value)
    {
        $expected = json_encode([$key => $value]);

        if (Str::startsWith($expected, '{')) {
            $expected = substr($expected, 1);
        }

        if (Str::endsWith($expected, '}')) {
            $expected = substr($expected, 0, -1);
        }

        return $expected;
    }

    /**
     * @param string $saveCookieKey
     * @return $this
     */
    public function setSaveCookieKey($saveCookieKey = 'phpunit-cookie')
    {
        $this->saveCookieKey = $saveCookieKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getSaveCookieKey()
    {
        return $this->saveCookieKey;
    }

    protected function seeStatusCode($status)
    {
        $this->assertEquals($status, $this->response->getCode());
        return $this;
    }

    protected function seeHeader($headerName, $value = null)
    {
        $headers = $this->response->getHeader();

        $this->assertTrue(!empty($headers[$headerName]), "Header [{$headerName}] not present on response.");

        if (!is_null($value)) {
            $this->assertEquals(
                $headers[$headerName], $value,
                "Header [{$headerName}] was found, but value [{$headers[$headerName]}] does not match [{$value}]."
            );
        }

        return $this;
    }

//    protected function seeCookie($cookieName, $value = null)
//    {
//
//        $exist = Cookie::has($cookieName);
//
//        $this->assertTrue($exist, "Cookie [{$cookieName}] not present on response.");
//
//        if (!is_null($value)) {
//            $cookie = Cookie::get($cookieName);
//            $this->assertEquals(
//                $cookie, $value,
//                "Cookie [{$cookieName}] was found, but value [{$cookie}] does not match [{$value}]."
//            );
//        }
//
//        return $this;
//    }

}