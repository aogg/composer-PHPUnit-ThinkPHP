<?php
/**
 * User: aogg
 * Date: 2020/8/3
 */

namespace aogg\phpunit\think;


use think\db\exception\PDOException;

abstract class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    use traits\CrawlerTrait,
        traits\AssertTrait,
        traits\RunTestClassTrait;

    /**
     * 异常报错
     *
     * @param \Throwable $t
     * @return never
     * @throws \Throwable
     * @throws PDOException
     */
    protected function onNotSuccessfulTest(\Throwable $t): never /* The :void return type declaration that should be here would cause a BC issue */
    {
        if ($t instanceof \think\db\exception\PDOException) {
            $failure = new \PHPUnit\Framework\OutputError((data_get($t->getData(), 'Database Status.Error SQL')));
            $this->getTestResultObject()->addFailure($this, $failure, time());
        }

        throw $t;
    }


    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); // @see \PHPUnit\Framework\TestCase::setUpBeforeClass

        foreach (\aogg\phpunit\think\ThinkHelperClass::class_uses_recursive(static::class) as $trait) {
            $method = 'bootUpBeforeClass'.basename(str_replace('\\', '/', $trait));
            if (is_callable([static::class, $method])) {
                call_user_func([static::class, $method]);
            }
        }
    }

    protected function getRequestUrlString(string $url = '', array $vars = [], $prefix = '/api/')
    {
        return url(
            rtrim($prefix, '/') . '/' . ltrim($url, '/'), $vars, false,
            parse_url(config('app.app_host')?:'', PHP_URL_HOST)?:false
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
     * @param $commandNamespace
     * @return \think\console\Command
     * @inheritDoc
     */
    public function getNewCommandObject($commandNamespace)
    {
        if (0){
            return new $commandNamespace();
        }

        /** @var \think\console\Command $object */
        $object = new $commandNamespace();

        $reflectionClass = new \ReflectionClass($object);
        $property = $reflectionClass->getProperty('input');
        $property->setAccessible(true);
        $property->setValue($object, $this->getOriginAppInput());
        $property->setAccessible(false);

        $property = $reflectionClass->getProperty('output');
        $property->setAccessible(true);
        $property->setValue($object, $this->getOriginAppOutput());
        $property->setAccessible(false);

        $object->setApp($this->getOriginApp());

        return $object;
    }


    /**
     * 获取tp think的入口的app
     *
     * @return \think\App
     */
    public function getOriginApp()
    {
        return $GLOBALS['phpunit_tp_app'];
    }

    /**
     * 获取tp think的入口的output
     *
     * @return \think\console\Output
     */
    public function getOriginAppOutput()
    {
        return $this->getOriginApp()->get('phpunit_output');
    }

    /**
     * 获取tp think的入口的Input
     *
     * @return \think\console\Input
     */
    public function getOriginAppInput()
    {
        return $this->getOriginApp()->get('phpunit_input');
    }

    /**
     * 获取tp think的入口的command
     *
     * @return \aogg\phpunit\think\UnitCommand
     */
    public function getOriginAppCommand()
    {
        return $this->getOriginApp()->get('phpunit_command');
    }

}