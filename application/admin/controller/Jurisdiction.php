<?php
namespace app\admin\controller;

use \base\Base;
use think\Db;
use \app\admin\model\JurisdictionModel;
use \app\admin\model\AdminUserModel;

use think\Request;

class Jurisdiction extends Base
{

    public function __construct(){
        parent::__construct();
        $this->model = new AdminUserModel();
        // $this->admin = new AdminUserModel();
    }
	/*
    *  显示模块权限
    *
    */
    public function index()
    {
        $allinfo = $this->model->getModuleList();
        return view('jurisdiction/index',['all'=>$allinfo]);
    }

    /*
    * 删除模块控制
    */
    public function delModule()
    {
        $mid = input('mid');
        $res =  $this->model->delModule($mid);
        if($res){
            echo '1';
        }else{
            echo '2';
        }
    }

    /*
    * 添加模块控制
    */
    public function addModule()
    {
        //post提交数据为添加，否则就做显示效果
        if (Request::instance()->isPost()){
            
            $modulename = input('name');
            $describle = trim(input('describle'));
            $controll = input('controll');
            $method = input('method');
            $moduletype = input('moduletype');
            $parentid = input('parentid');
            $showorder = input('showorder');
            if($moduletype == 1){
                if(empty($controll) || empty($method)){
                    //实节点 路径不能为空
                    return '模块类型中,实节点控制器和方法名不能为空';
                }
                $xpath = '/'.$controll.'/'.$method;

            }else{
                if(empty($controll) || empty($method)){
                    $xpath = '';
                }else{
                    $xpath = '/'.$controll.'/'.$method;
                }
               
            }
            if(empty($modulename)){
                //模块名不能为空
                return '模块名不能为空';
            }

            
            $res = $this->model->addModule($modulename,$describle,$moduletype,$xpath,$parentid,$showorder);
            if($res > 0){
                return '添加成功';
            }else{
                return '添加失败';
            }

        }else{
            
            $allmodule = $this->model->getModuleList();
            return view('jurisdiction/addmodule',['list'=>$allmodule]);
        }
    }

    /*
    * 
    */
    public function updateModuleInfo()
    {
        if (Request::instance()->isPost()){
            // echo '操作';
            $mid = input('mid');
            $new_moduleinfo['modulename'] = input('name');
            $new_moduleinfo['describle'] = trim(input('describle'));
            $controll = input('controll');
            $method = input('method');
            $new_moduleinfo['moduletype'] = input('moduletype');
            $new_moduleinfo['parentid'] = input('parentid');
            $new_moduleinfo['showorder'] = input('showorder');
            if($new_moduleinfo['moduletype'] == 1){
                if(empty($controll) || empty($method)){
                    //实节点 路径不能为空
                    return '模块类型中,实节点控制器和方法名不能为空';
                }
                $new_moduleinfo['xpath'] = '/'.$controll.'/'.$method;

            }else{
                if(empty($controll) || empty($method)){
                    $new_moduleinfo['xpath'] = '';
                }else{
                    $new_moduleinfo['xpath'] = '/'.$controll.'/'.$method;
                }
               
            }
            if(empty($new_moduleinfo['modulename'])){
                //模块名不能为空
                return '模块名不能为空';
            }
            $bool  = $this->model->updateModuleInfo($mid, $new_moduleinfo);

            if($bool) {
                return '修改成功';
            }else{
                return '修改失败';
            }
        }else{
            echo '显示';
            $mid = input('mid');
            $modulelist = $this->model->getModuleInfo($mid);
            if(!empty($modulelist['xpath'])){
                $arr =  explode('/',$modulelist['xpath']);
                $modulelist['controll'] = $arr['1'];
                $modulelist['method'] =  $arr['2'];
            }else{
                $modulelist['controll'] = '';
                $modulelist['method'] =  '';
            }
            $allmodule = $this->model->getModuleList();
            return view('jurisdiction/addmodule',['list'=>$allmodule,'info'=>$modulelist]);
        }
    
    }
    /*
    * 角色列表
    */
    public function showRole()
    {
        $rolelist = $this->model->getRoleList();
        return view('jurisdiction/rolelist',['list'=>$rolelist]);
    }
    /*
    * 删除角色
    */
    public function delRole()
    {
        $rid = input('rid');
        $res = $this->model->delRole($rid); 
        if($res){
            echo '1';
        }else{
            echo '2';
        }
    }

    /*
    * 添加角色
    */
    public function addRole()
    {
        if (Request::instance()->isPost()){
           $rname =  input('name');
           $describle = input('describle');
           if(empty($rname)){
                return '角色名称不能为空';
           }
           if(false !== $this->model->checkRoleName($rname)){
                return '该角色名已经存在';
           }
           $res = $this->model->addRole($rname,$describle);
           if($res){
                return '添加成功';
           }else{
                return '添加失败';
           }
        }else{
            return view('jurisdiction/addrole');
        }
    }

    /*
    * 角色模板权限分配
    */
    public function roleAddModule()
    {
        if (Request::instance()->isPost()){
            $midlist = array();
            $rid = input('rid');
            $mid = input('mid/a');
            $midlist['modulelist'] = explode(',',$mid[0]);
            $res = $this->model->updatRoleInfo($rid,$midlist);
            // print_r($res);
            if($res){
                return '分配成功';
            }else{
                return '分配失败';
            }
        }else{
            $name = input('name');
            $rid = input('rid');
            $modulelist = $this->model->getModuleList();

            $rolemodulelist = $this->model->getModuleListByRid($rid);
            // var_dump($rolemodulelist);
            $mid = array();
            foreach($rolemodulelist as $key=>$val){
                $mid[] = $val['mid'];
            }
            return view('jurisdiction/roleaddmodule',['rid'=>$rid,'name'=>$name,'list'=>$modulelist,'mid'=>$mid]);
        }
    }

    /*
    * 显示后台用户
    */
    public function showAdminList()
    {
        $adminlist = $this->model->getUserList('');
        $sta = array('100'=>'正常用户','-100'=>'禁用用户');
        // var_dump($adminlist);
        return view('jurisdiction/getadminrole',['list'=>$adminlist,'sta'=>$sta]);
    }

    /*
    *  给用户设置角色
    */
    public function updateUserInfo()
    {
        if (Request::instance()->isPost()){
            $new_userinfo['rolelist']= array(input('rid'));
            $uid = input('uid');
            $res = $this->model->updateUserInfo($uid, $new_userinfo);
            if($res){
                return '分配成功';
            }else{
                return '分配失败';
            }
        }else{
            $uid = input('uid');
            $name = input('name');
            // echo $name;
            $list = $this->model->getRoleList();
            $admin_user = Db::table('t_admin_user_role')->field('f_rid rid')->where('f_uid',$uid)->find();

            return view('jurisdiction/addadminrole',['list'=>$list,'uid'=>$uid,'name'=>$name,'rid'=>$admin_user['rid']]);
        }
    }
}
