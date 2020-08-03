<?php
/**
 * User: aogg
 * Date: 2020/8/3
 */

namespace aogg\phpunit\think;


abstract class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    use traits\CrawlerTrait;


    protected function getApiUrlString(string $url = '', array $vars = [])
    {
        return url(
            '/api/' . ltrim($url, '/'), $vars, false,
            parse_url(config('app.app_host'), PHP_URL_HOST)
        )->build();
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
}
