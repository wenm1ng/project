<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    // 应用命名空间
    'app_namespace'          => 'app',
    // 应用调试模式
    'app_debug'              => true,
    // 应用Trace
    'app_trace'              => false,
    // 应用模式状态
    'app_status'             => '',
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 扩展函数文件
    'extra_file_list'        => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'PRC',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => '',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'index',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由
    'url_route_on'           => true,
    // 路由使用完整匹配
    'route_complete_match'   => false,
    // 路由配置文件（支持配置多个）
    'route_config_file'      => ['route'],
    // 是否强制使用路由
    'url_route_must'         => false,
    // 域名部署
    'url_domain_deploy'      => true,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => true,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],

    // 视图输出字符串内容替换
    'view_replace_str'       => [
    '__PUBLIC__' => '/public',
    ],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => true,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '',

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'                    => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => [],
        'apart_level' => ['error','debug'],
        'time_format' => ' d ',
        'file_size'   => 104857600,
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace'                  => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'                  => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'think',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
        //过期时间
        'expire'         => 86400,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
    ],
    
    //系统变量
    //'baseurl' => Env::get('baseurl','http://shanwei.boss.com/'),
    'sms_sendurl' => 'https://api.netease.im/sms/sendcode.action', //发送短信验证码接口
    'sms_verifyurl' => 'https://api.netease.im/sms/verifycode.action', //短信验证码验证接口
    'sms_appkey' => '018edc4085a14db5b25d6ada4e7ad12b', //发送短信验证码appkey
    'sms_appsecret' => 'd3a142e98b6d', //发送短信验证码App Secret

    //支付宝配置
    'alipay' => array(
        'APPID' => '2017051507242674', //支付宝应用ID
        'UUID' => '2088022218641659', //商户ID
        'GATEWAY' => 'https://openapi.alipay.com/gateway.do', //支付宝网关地址
        'NOTIFY_URL' => 'http://www.yee.website/notify/alipay', //异步通知地址
        'RSA_PUBLIC_KEY' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA6BPyYcB8VR82H2kZr6FwYDxhGdEJGSdlFD25fplxqFPkrdjfeWi1u1rPJ0nYMl5ElNwehV6nx3TfBsjDtaSGV0qRUXwYUi22VYr6eHQ+i3ANrq8zYHOMnzT38FDI8MvJfhgFeOIdGeX/QKsaMJ0azChDnh1VPtVeOssNVfp9uP81tZe0JRSgSOQbrwXiXSQ5bFB5LzYpPnOBrnXq/zDHLGMV//OJ7EOUUcOq1JECSjeOZsdis8NjxAFRG7m790YyjMVC1BhpjoppRnJ+EP89tu+Nr1Rmk+18K0o4qxt1qMWUNffyAqJ12aEPVuH4Zh5y2Yh1Ko7VALYbVAwhW2XZfQIDAQAB', //RSA公钥
        'RSA_PRIVATE_KEY' => 'MIIEpAIBAAKCAQEA6BPyYcB8VR82H2kZr6FwYDxhGdEJGSdlFD25fplxqFPkrdjfeWi1u1rPJ0nYMl5ElNwehV6nx3TfBsjDtaSGV0qRUXwYUi22VYr6eHQ+i3ANrq8zYHOMnzT38FDI8MvJfhgFeOIdGeX/QKsaMJ0azChDnh1VPtVeOssNVfp9uP81tZe0JRSgSOQbrwXiXSQ5bFB5LzYpPnOBrnXq/zDHLGMV//OJ7EOUUcOq1JECSjeOZsdis8NjxAFRG7m790YyjMVC1BhpjoppRnJ+EP89tu+Nr1Rmk+18K0o4qxt1qMWUNffyAqJ12aEPVuH4Zh5y2Yh1Ko7VALYbVAwhW2XZfQIDAQABAoIBAH26uLiOoI05IIg510mYK5pne6+R2N0Aw7kIi6LznGi2MpCgislqmfILi2jcj70R5xPCgOJ+WmUrgtxZDfYtUP6fjkTX9xEmZL7JUVLKn0vJhBAcKLhbQVbLSnuuOH6D2QBwIR7RWTS7ruKpD8JAitEKCz/w4krtK2SstufakhwTVy3oT6xKwYxrr92+R48jsGEqtByLKRE7XMqRpxg6VQpWJOSn0eQJkFPafo5BKXINqw6h6EZY5dbGW0Oh4P+mqI1ViHDWwfORVnl/A0NJMCzK07wBmIjPxT/dUIDT6udkFOSS1k7LRc9FpY2sYIZ00AzFQi4Ymre40YgTE9Tkc+ECgYEA+F2n9HYeTy+Y7lWhhxeDWdVZ4Ov5wPyfAG0THNtV668dIzGmnUyhm2RKh2Z9taIZEZVggHFb0oj0t+0/jT7gcCMG6VuMcZPc6JSVnK/Vg2ZEU7aTiF0HxvxxWb2pbuX8wCJwPCJkWnH0qj8F+gRfo1PCniKHu2dzzq1F55BiiTkCgYEA7zYfxaYxAI8bg+tpudphnw8Zkdf+9QNK4B0uGcie2grfwYLEmJxZSH849tYdORCd0AeCBESwYAXmqjoG7eV08zDCpCbpLVcGShmWAzcQ+L+zExo4k3Dx4NJzbzBwprQj/E/eEYkLesGyhIU3viHpGjHmo8dAnD0mv0m8bX2hZmUCgYEAzgfzcioiTpIvjVbf3k81GWqRWrKmxt9Jj3Lsbf3NsuvbgfyIOOj/DwcNhHETS3+iyCFgomxnPal7SLC5DZThXmTQMPlO8lE06oOH8Sk0OAK4H7HPhUmXUAzOgyGA0oxbNY/cByaIlTb/PdN2Q8cpBzZOthvy+RWCziqTWNs/wpECgYALIBFKN/h/dihpckFwi1+O7jzaM5l/+683zSOfv1zj9y2A6nwKPPtKC+CyWjLxvHwaeL7rQ+aQELYxpJE97zYJOXNT/xtJKIr6V0ZKz/zSFNhXQxjugoo9Uoxb13zlw+sIiQ5i8mS+SNPspeV5ykn1Fe0MIPA4U//BVcSVswoWoQKBgQC3mlTymjCNdj+mD+W4jy0Esdt0rWtE64mMFvYOB78ZTmTFzfuKm95YhZZHdVcBRxTHDeEN0fkh15yfauSTcGMl8MGpyPdhqaEYaCam0ZudSgg5KAk7suj0wCz7/RhE2CRzM94/4em64WzqlhXm8LQDdnNEfmrthKWvwlXCIuuXyQ==', //RSA私钥
        'ALIPAY_RSA_PUBLIC_KEY' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA5tShwx5qU5eVD6/qHXdLIUZy1X/w+Vc/S7ydNiiQ+SZRcdR6Sza62ASETz54CstwobOhjckjoCgs1vddG+Ny6DpogRjXAUVBIb0lU5y+0/trQyrP4xBWKwTyLY+nb8ieU4hs2Dce75ChcqZmN2tyJDzW2Di4G4B4v+rG7fMeJIETwBkA34l4Ma5f5kuLFnsBxN3BcYmotzfUrBTat+Ypgw/6vgq8Q5WeUb+QdnSH+zf9wvOjvllgp94Z14risfeytdbfxQpCIWfKKawC5talXfiLtUmBYP13K/O2fxzHWwThEaSvmPBDEW6ZK/PFqC0a60JX103in3vy3+OnJusrgQIDAQAB', //支付宝RSA公钥
    ),

    //充值提款渠道类型配置
    "paytype" => array(
        "balance" => 1001,  //余额充值
        "deposit" => 1002,  //押金充值
        "order" => 1003,  //订单充值
    ),
    "paychannel" => array(
        "alipay" => 1001,  //支付宝充值
        "wechat" => 1002,  //微信充值
    ),
    "drawtype" => array(
        "balance" => 100,  //余额提款
        "deposit" => 200,  //押金退款
        "order" => 300,  //订单退款
    ),
    "drawchannel" => array(
        "alipay" => 1001,  //支付宝提款
        "wechat" => 1002,  //微信提款
    ),

    //子订单配置
    "suborder" => array(
        "endtime" => 1800,
    ),
    "order" => array(
        "overtime_closed" => 900, //超时未付款自动关闭
        "overtime_repast" => 1800, //逾期未就餐
    ),
    //验证码
    'captcha' => [
        //验证字符集合
        'codeset' => '23456789abcdefghijklmnpqrstuvwxyABCDEFGHIJKLMNPQRSTUVWXY',
        //字体大小
        'fontSize' => 18,
        //是否画混淆曲线
        'useCurve' => true,
        //验证图片码高度
        'imageH' => 40,
        //验证码宽度
        'imageW' => 130,
        //验证码位数
        'lenght' => 4,
        //验证成功后是否重置
        'reset' => true,
    ],
    // //抛出异常
    'http_exception_template'    =>  [  
        // 定义404错误的重定向页面地址  
        404 =>  APP_PATH.'404.html',  
        // 还可以定义其它的HTTP status  
        408 =>  APP_PATH.'408.html',  
        500 =>  APP_PATH.'500.html',
    ],
];
