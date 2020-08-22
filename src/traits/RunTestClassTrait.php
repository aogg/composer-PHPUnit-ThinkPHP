<?php
/**
 * User: aogg
 * Date: 2020/8/22
 */

namespace aogg\phpunit\think\traits;


trait RunTestClassTrait
{
    /**
     * 依赖的测试类文件
     * 定义此方法
     *
     * @var array
     */
    public static $upRunTestClasses = [];

    /**
     * @var \PHPUnit\Framework\TestResult[]
     */
    protected static $upRunTestClassesResult = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass(); // @see \PHPUnit\Framework\TestCase::setUpBeforeClass

        $currentObject = new static;
        foreach (static::$upRunTestClasses as $upRunTestClass) {

            static::$upRunTestClassesResult[$upRunTestClass] = $currentObject->runTestClass($upRunTestClass);
        }
    }

    /**
     * 获取依赖测试类成功返回时的return结果
     * 每个执行的结果都可以获取到
     *
     * @param $classMethodName
     * @param string $class
     * @return bool
     */
    public function getUpTestClassPassResult($classMethodName, $class = '')
    {
        $arr = explode('::', $classMethodName);
        $method = isset($arr[0]) ? $arr[0] : '';
        $class = $class ?: (isset($arr[1]) ? $arr[1] : '');

        if (empty($method)) {
            return false;
        }

        foreach (static::$upRunTestClassesResult as $className => $classArr) {
            if (
                ltrim($className, "\\") === ltrim($class, "\\") ||
                empty($class) // 返回第一个
            ){
                $passedArr = $classArr->passed();

                $key = $className . '::' . $method;
                if (isset($passedArr[$key]) && isset($passedArr[$key]['result'])) {
                    return $passedArr[$key]['result'];
                }
            }
        }

        return false;
    }

    /**
     * 运行测试类
     *
     * @param $theClass
     * @return \PHPUnit\Framework\TestResult
     */
    public function runTestClass($theClass)
    {
        /** @var \PHPUnit\Framework\TestResult $result */
        $result = $this->createResult();
        $t      = new \PHPUnit\Framework\TestSuite($theClass);

        $t->run($result);

        $this->assertEquals(0, $result->failureCount(), '运行的测试类failureCount: ' . $result->failureCount());
        $this->assertEquals(0, $result->errorCount(), '运行的测试类errorCount: ' . $result->failureCount());

        return $result;
    }
}
