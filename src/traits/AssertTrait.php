<?php
/**
 * User: aogg
 * Date: 2020/8/6
 */

namespace aogg\phpunit\think\traits;

/**
 * 自定义断言
 */
trait AssertTrait
{

    /**
     * 通过$whereIds查询数据库的值是不是$expecteds的值
     *
     * @param array $expecteds 断言，key=>value结构
     * @param string|array $actual 检测的值，列表或者单行数据。数组就检测断言的每个key是否存在
     * @param \think\Model|string $model
     * @param int|string|array $whereIds 自带的where条件，默认主键值。如果为空则用$actual的
     * @param null|string $whereIdsFieldName $whereIds的字段名，默认主键
     */
    protected function seeModelEquals(array $expecteds, $whereIds, $model, $whereIdsFieldName = null)
    {
        /** @var \think\Model $model */
        $model = is_string($model) ? new $model() : $model;



        $tempSelect = [];
        if (isset($whereIdsFieldName)) { // 指定where条件
            $list = $model->where($whereIdsFieldName, is_array($whereIds)?'in':'=', $whereIds);
        }else{
            $tempSelect = $whereIds;
            $list = $model;
        }

        /** @var object $list */
        $list = $list->select($tempSelect);
        /** @var \think\Model[] $list */

        $this->assertNotEmpty($list);

        // 开始
        foreach ($list as $item) {

            foreach ($expecteds as $expectedKey => $expectedValue) {
                $this->assertArrayHasKey($expectedKey, $item);

                if (isset($item[$expectedKey])) {
                    $this->assertEquals($expectedValue, $item[$expectedKey]);
                }

            }

        }
    }

    /**
     * 通过$whereIds去数据库查询值
     * 根据$expectedFieldNames，检测$actual是否存在$filedKeys
     *
     * $expectedFieldNames是数组时，$actual也必须时列表数据
     *
     * @param string|array $filedKeys 断言，值的数组和字符串。如'id'或者['id']
     * @param string|array $actual 检测的值，列表或者单行数据。数组就检测断言的每个key是否存在
     * @param \think\Model|string $model
     * @param int|string|array $whereIds 自带的where条件，默认主键值。如果为空则用$actual的
     * @param null|string $whereIdsFieldName $whereIds的字段名，默认主键
     */
    protected function assertModelHasKey($filedKeys, $actual, $model, $whereIds = [], $whereIdsFieldName = null)
    {
        /** @var \think\Model $model */
        $model = is_string($model) ? new $model() : $model;
        $model = $model->field($filedKeys);

        $isList = is_array($actual) && is_array(current($actual));

        if (empty($whereIds)) { // 默认主键
            $tempPk = isset($whereIdsFieldName)?$whereIdsFieldName:$model->getPk();
            if ($isList) { // 列表
                $whereIds = array_column($actual, $tempPk);
            }else{ // 单个id
                $this->assertArrayHasKey($tempPk, $actual);
                if (isset($actual[$tempPk])) {
                    $whereIds = $actual[$tempPk];
                }
            }
        }

        $tempSelect = [];
        if (isset($whereIdsFieldName)) { // 指定where条件
            $list = $model->where($whereIdsFieldName, is_array($whereIds)?'in':'=', $whereIds);
        }else{
            $tempSelect = $whereIds;
            $list = $model;
        }

        /** @var object $list */
        $list = $list->select($tempSelect);
        /** @var \think\Model[] $list */

        $this->assertNotEmpty($list);

        // 开始
        foreach ($list as $item) {

            if (is_array($filedKeys)) {
                foreach ($filedKeys as $key) {
                    $this->assertArrayHasKey($key, $item);

                    if ($isList) { // 列表
                        foreach ($actual as $actualItem) {
                            $this->assertArrayHasKey($key, $actualItem);
                        }
                    }else{
                        $this->assertArrayHasKey($key, $actual);
                    }

                }
            }else{ // 单个
                $this->assertArrayHasKey($filedKeys, $item);
            }

        }
    }
}
