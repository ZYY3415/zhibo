<?php
namespace Jump;
use think\Request;
use think\Url;
trait Jump
{
    /**
     * 操作返回错误数据方法
     * @param mixed $data   返回的数据
     * @param string $msg   提示信息
     * @param string $url   跳转的url
     * @return json
     **/
    protected function return_error($data = '',$msg = 'error',$url = null)
    {
        if (is_null($url) && !is_null(Request::instance()->server('HTTP_REFERER'))) {
            $url = Request::instance()->server('HTTP_REFERER');
        } elseif ('' !== $url && !strpos($url, '://') && 0 !== strpos($url, '/')) {
            $url = Url::build($url);
        }

        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
        ];

        return json($result,200,['Content-Type' => 'application/json']);
    }

    /**
     * 操作返回成功数据方法
     * @param mixed $data   返回的数据
     * @param string $msg   提示信息
     * @param string $url   跳转的url
     * @return json
     **/
    protected function return_success($data = '',$msg = 'success',$url = null)
    {
        if (is_null($url) && !is_null(Request::instance()->server('HTTP_REFERER'))){
            $url = Request::instance()->server('HTTP_REFERER');
        } elseif ('' !== $url && !strpos($url, '://') && 0 !== strpos($url, '/')) {
            $url = Url::build($url);
        }

        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
        ];
        return json($result,200,['Content-Type' => 'application/json']);
    }



}

