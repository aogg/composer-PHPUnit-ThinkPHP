<?php
/**
 * User: aogg
 * Date: 2020/8/3
 */

namespace aogg\phpunit\think;

/**
 *
 * @deprecated 无效
 * Class RequestInTest
 * @package aogg\phpunit\think
 */
class RequestInTest extends \think\Request
{
    public $env;
    public $post = [];
    public $get = [];
    public $put;
    public $request = [];
    public $cookie = [];
    public $file = [];
    public $server = [];
    public $header = [];
    public $input;

//    public static function create($data, $_SERVER, $env, $get, $post, $cookie, $request_global, $files = null)

    /**
     * @see \think\Request::__make
     * @param $post
     * @return RequestInTest
     */
    public static function create($app, $post, $server)
    {
        $request = new static();
        $request->input = '';

        $request->server  = $server;
        $request->env     = $app->env;
        $request->get     = $get??[];
        $request->post    = $post;
//        $request->post    = $post ?: $request->getInputData($request->input);
        $request->put     = $request->getInputData($request->input);
        $request->request = $request_global??[];
        $request->cookie  = $cookie??[];
        $request->file    = $files ?? [];

        if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
            $header = $result;
        } else {
            $header = [];
            $server = $request->server;
            foreach ($server as $key => $val) {
                if (0 === strpos($key, 'HTTP_')) {
                    $key          = str_replace('_', '-', strtolower(substr($key, 5)));
                    $header[$key] = $val;
                }
            }
            if (isset($server['CONTENT_TYPE'])) {
                $header['content-type'] = $server['CONTENT_TYPE'];
            }
            if (isset($server['CONTENT_LENGTH'])) {
                $header['content-length'] = $server['CONTENT_LENGTH'];
            }
        }

        $request->header = array_change_key_case($header);

        return $request;
    }
}
