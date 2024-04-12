# composer-PHPUnit-ThinkPHP
ThinkPHP6 use PHPUnit  
ThinkPHP6使用PHPUnit


# 介绍
> 1. [phpunit](https://github.com/aogg/composer-PHPUnit-ThinkPHP/blob/master/src/phpunit)可以给PHPStorm指定phpunit的路径
> 2. 可以通过php think unit执行命令
> 3. [BaseTestCase](https://github.com/aogg/composer-PHPUnit-ThinkPHP/blob/master/src/BaseTestCase.php)是测试基类，继承phpunit的测试基类，并提供ThinkPHP6专用方法
> 4. 继承\aogg\phpunit\think\BaseTestCase基类





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
        $data = $this->get($this->getRequestUrlString('product/list', ['limit' => 1]));

        return $data;
    }
```




# 配置PHPStorm的PHPUnit
![配置PHPStorm的PHPUnit](https://raw.githubusercontent.com/aogg/composer-PHPUnit-ThinkPHP/master/docs/PHPStorm%E9%85%8D%E7%BD%AEPHPUnit.jpg)


![代码示例](https://github.com/aogg/composer-PHPUnit-ThinkPHP/assets/8998031/4a5d476a-9b9a-41f6-9e2a-0ef0d490d5d2)


## 配置PHPStorm的远端PHPUnit
<img width="285" alt="image" src="https://github.com/aogg/composer-PHPUnit-ThinkPHP/assets/8998031/d602c6df-75d6-4536-add0-e38c883ab890">  
选择指定目录 /vendor/aogg/think-phpunit/src/phpunit  
<img width="202" alt="image" src="https://github.com/aogg/composer-PHPUnit-ThinkPHP/assets/8998031/57067794-a4d3-4769-a31e-bd82718d0148">  



# 本地调试composer类库
```json
{

  "repositories": [

    {
      "type": "path",

      "url": "/app/origin/my/github/composer-PHPUnit-ThinkPHP"

    }

  ]
}
```
```bash
composer require aogg/think-phpunit:dev-master
```

