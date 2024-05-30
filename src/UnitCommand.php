<?php
/**
 * User: aogg
 * Date: 2020/7/31
 */

namespace aogg\phpunit\think;



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

        // 允许访问到output
        $GLOBALS['phpunit_tp_app'] = $this->getApp();
        bind('phpunit_input', $input);
        bind('phpunit_output', $output);
        bind('phpunit_command', $this);
//        Blacklist::$blacklistedClassNames = [];

        if (class_exists(\PHPUnit\TextUI\Application::class)) { // phpunit 10

            $code = static::fork_run(function ()use($argv){
                (new \PHPUnit\TextUI\Application)->run($argv);
            });
        }else{
            $code = (new \PHPUnit\TextUI\Command())->run($argv, false);
        }

        return $code;
    }

    /**
     * 启用子进程
     *
     * @param $childFunc
     * @return bool
     */
    public static function fork_run($childFunc){
        try{

            $pid = pcntl_fork();
        }catch (\Exception | \Throwable $exception){
            echo '创建PHPUnit子进程失败--异常，请检查' . PHP_EOL;

            return false;
        }

        if ($pid < 0) {
            echo '创建PHPUnit子进程失败，请检查' . PHP_EOL;

            return false;
        }

        if ($pid == 0) {
            /*      子进程代码      */
            cli_set_process_title(
                "php child process aogg/composer-PHPUnit-ThinkPHP");
            return call_user_func($childFunc, $pid);
//            exit;
        }else{
            pcntl_wait($status);
        }

        return $pid;
    }
}
