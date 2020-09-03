# composer-PHPUnit-ThinkPHP
ThinkPHP6 use PHPUnit  
ThinkPHP6使用PHPUnit


# 介绍
> 1. [phpunit](https://github.com/aogg/composer-PHPUnit-ThinkPHP/blob/master/src/phpunit)可以给PHPStorm指定phpunit的路径
> 2. 可以通过php think unit执行命令
> 3. [BaseTestCase](https://github.com/aogg/composer-PHPUnit-ThinkPHP/blob/master/src/BaseTestCase.php)是测试基类，继承phpunit的测试基类，并提供ThinkPHP6专用方法





## 安装

```bash
composer require aogg/think-phpunit:dev-master
```


# 详细

## BaseTestCase

支持测试类中调用控制器的方法
```php
    /**
     * 商品列表
     *
     * @return array|mixed
     */
    public function testStoreVerifyFinishList()
    {
        $data = $this->get($this->getApiUrlString('product/list', ['limit' => 1]));

        return $data;
    }
```




# 配置PHPStorm的PHPUnit
![配置PHPStorm的PHPUnit](https://raw.githubusercontent.com/aogg/composer-PHPUnit-ThinkPHP/master/docs/PHPStorm%E9%85%8D%E7%BD%AEPHPUnit.jpg)


