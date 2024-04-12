<?php

namespace aogg\phpunit\think\testsDir;

class AllTest extends \aogg\phpunit\think\BaseTestCase
{

    public function test_run_config()
    {
        var_dump(config('app.default_app'));
        $this->assertNotEmpty(\tests\config('app.default_app'));
        $this->assertIsBool(true);
    }

}