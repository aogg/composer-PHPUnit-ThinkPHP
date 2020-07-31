<?php
/**
 * User: aogg
 * Date: 2020/7/31
 */

namespace aogg\phpunit\think;


class Service extends \think\Service
{
    public function register()
    {
        $this->commands([
            'unit' => UnitCommand::class,
        ]);
    }
}
