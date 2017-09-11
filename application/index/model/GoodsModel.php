<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 17-4-25
 * Time: 下午9:28
 */
namespace app\index\model;
use think\Model;

class GoodsModel extends Model
{
    /**
     * 检查该用户名是否已注册
     * @param $username
     * @return bool
     */
    public function addCuishine($cname)
    {

    }

	/**
     * 检查该分类是否已经存在
     * @param $username
     * @return bool
     */
     public function checkUserName($cname)
    {
       $table_name = 't_foot_cuisine';
        $ret = Db::name($table_name)->field('f_uid as uid')->where('f_username', $username)->find();
        if (empty($ret)) {
            return false;
        }
        return $ret;
    }
}