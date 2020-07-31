<?php
/**
 * User: aogg
 * Date: 2020/7/31
 */

namespace aogg;



class UnitCommand extends \think\console\Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('unit')
            ->setDescription('PHPUnit-ThinkPHP')
            ->ignoreValidationErrors();
    }

    public function execute(\think\console\Input $input, \think\console\Output $output)
    {
        //注册命名空间
        /** @var \Composer\Autoload\ClassLoader $composer */
        $composer = include $this->getApp()->getRootPath() . 'vendor/autoload.php';

        $composer->setPsr4('tests\\', $this->getApp()->getRootPath() . 'tests');
        $composer->register(true);

//        Session::init();
        $argv = $_SERVER['argv'];
        array_shift($argv);
        array_shift($argv);
        array_unshift($argv, 'phpunit');
//        Blacklist::$blacklistedClassNames = [];

        $code = (new \PHPUnit\TextUI\Command())->run($argv, false);

        return $code;
    }
}
