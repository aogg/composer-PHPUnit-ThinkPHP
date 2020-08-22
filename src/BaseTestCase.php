<?php
/**
 * User: aogg
 * Date: 2020/8/3
 */

namespace aogg\phpunit\think;


abstract class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    use traits\CrawlerTrait,
        traits\AssertTrait;

    protected function getRequestUrlString(string $url = '', array $vars = [], $prefix = '/api/')
    {
        return url(
            rtrim($prefix, '/') . '/' . ltrim($url, '/'), $vars, false,
            parse_url(config('app.app_host'), PHP_URL_HOST)
        )->build();
    }

    /**
     * 简化版获取url
     *
     * @param string|array $url 数组或者字符串
     * @param string $prefix
     * @return string
     */
    protected function getRequestUrlStringByArr($url, $prefix = '/api/')
    {
        if (is_string($url)) {
            return $this->getRequestUrlString($url, [], $prefix);
        }

        return $this->getRequestUrlString(
            isset($url[0])?$url[0]:'',
            isset($url[1])?$url[1]:[],
            isset($url[2])?$url[2]:$prefix,
        );
    }

    protected function prepareUrlForRequest($uri)
    {
        if (\think\helper\Str::startsWith($uri, '/')) {
            $uri = substr($uri, 1);
        }

        if (!\think\helper\Str::startsWith($uri, 'http')) {
            $uri = $this->baseUrl . '/' . $uri;
        }

        return trim($uri, '/');
    }


    /**
     * 运行测试类
     *
     * @param $theClass
     * @return \PHPUnit\Framework\TestResult
     */
    public function runTestClass($theClass)
    {
        $result = $this->createResult();
        $t      = new \PHPUnit\Framework\TestSuite($theClass);

        $t->run($result);

        $this->assertEquals(0, $result->failureCount(), '运行的测试类failureCount: ' . $result->failureCount());
        $this->assertEquals(0, $result->errorCount(), '运行的测试类errorCount: ' . $result->failureCount());

        return $result;
    }
}
