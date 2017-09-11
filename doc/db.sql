/*用户管理-用户信息表*/
DROP TABLE IF EXISTS `t_user_info`;
CREATE TABLE `t_user_info` (
  `f_uid` int NOT NULL AUTO_INCREMENT COMMENT '用户uid(自增)',
  `f_nickname` varchar(50) DEFAULT NULL COMMENT '用户昵称',
  `f_mobile` varchar(50) NOT NULL COMMENT '手机号码',
  `f_realname` varchar(200) DEFAULT NULL COMMENT '真实姓名',
  `f_sex` tinyint DEFAULT 0 COMMENT '性别(0-未知,1-男,2-女)',
  `f_idcard` varchar(50) DEFAULT NULL COMMENT '身份证号码',
  `f_auth_status` smallint default 100 COMMENT '实名认证状态(0-未认证,100-已认证,-100-认证失败)',
  `f_usermoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '用户余额',
  `f_freezemoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '冻结金额',
  `f_depositmoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '押金金额',
  `f_user_status` smallint default 200 COMMENT '用户状态(0-默认,100-已实名认证,200-已充值押金,-100-黑名单,-200-已清户(余额为0,押金退回))',
  `f_regtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '注册时间',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_uid`),
  UNIQUE KEY `f_mobile` (`f_mobile`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='用户管理-用户信息表';


/*用户管理-地址信息表*/
DROP TABLE IF EXISTS `t_user_address_info`;
CREATE TABLE `t_user_address_info` (
  `f_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_uid` int(11) NOT NULL COMMENT '用户uid',
  `f_province` varchar(100) NOT NULL COMMENT '省份名称',
  `f_city` varchar(100) NOT NULL COMMENT '城市名称',
  `f_address` varchar(1000) NOT NULL COMMENT '详细地址',
  `f_name` varchar(100) NOT NULL COMMENT '收件人',
  `f_sex` tinyint DEFAULT 1 COMMENT '性别(1-男,0-女)',
  `f_mobile` varchar(50) NOT NULL COMMENT '联系电话',
  `f_isactive` tinyint(4) DEFAULT '0' COMMENT '是否默认地址(0-否,1-是)',
  `f_status` tinyint(4) DEFAULT '0' COMMENT '地址状态(0-有效,-1-无效)',
  `f_addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户管理-地址信息表';


/*用户管理-账户流水表*/
DROP TABLE IF EXISTS `t_user_paylog`;
CREATE TABLE `t_user_paylog` (
  `f_id` int NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_uid` int NOT NULL COMMENT '用户uid',
  `f_inout` tinyint not null comment '出入账类型(1-入账,2-出账)',
  `f_trademoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' comment '交易金额',
  `f_tradetype` smallint NOT NULL COMMENT '交易类型(1001-余额充值,1002-押金充值,1003-订单充值,1004-撤单返款,1101-押金退款解冻,1102-订单支付解冻,1103-订单退款解冻,2001-押金退款冻结,2002-订单支付冻结,2003-订单退款冻结,2101-押金退款(解冻扣款),2102-订单支付(解冻扣款),2103-订单退款(解冻扣款))',
  `f_orderid` varchar(200) default null COMMENT '订单号',
  `f_suborderid` varchar(200) default null COMMENT '子订单号',
  `f_tradenote` varchar(1000) default null comment '交易备注',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户管理-账户流水表';


/*用户管理-充值订单表*/
DROP TABLE IF EXISTS `t_user_recharge_order`;
CREATE TABLE `t_user_recharge_order` (
  `f_id` int NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_uid` int NOT NULL COMMENT '用户uid',
  `f_paymoney` decimal(19,2) unsigned NOT NULL COMMENT '充值金额',
  `f_paytype` smallint NOT NULL COMMENT '充值类型(1001-充值余额,1002-充值押金,1003-订单充值)',
  `f_channel` smallint not NULL COMMENT '充值渠道(1001-支付宝充值,1002-微信充值)',
  `f_suborder` int default 0 comment '子订单号(订单充值时,不能为空)',
  `f_ordertype` tinyint unsigned default 0 comment '子订单类型(0-默认订单，1-加餐订单)',
  `f_account` varchar(200) default NULL COMMENT '充值账号',
  `f_bankorderid` varchar(100) default null comment '第三方订单号',
  `f_suctime` timestamp default '0000-00-00 00:00:00' comment '成功时间',
  `f_bankmoney` decimal(19,2) unsigned default '0.00'null comment '第三方订单金额',
  `f_status` smallint default 0 COMMENT '订单状态(0-默认,100-充值成功,-100-充值失败)',
  `f_paynote` varchar(1000) default null comment '充值备注',
  `f_addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100001 DEFAULT CHARSET=utf8 COMMENT='用户管理-充值订单表';


/*用户管理-提款订单表*/
DROP TABLE IF EXISTS `t_user_draw_order`;
CREATE TABLE `t_user_draw_order` (
  `f_id` int NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_uid` int NOT NULL COMMENT '用户uid',
  `f_drawmoney` decimal(19,2) unsigned NOT NULL COMMENT '提款金额',
  `f_drawtype` smallint NOT NULL default 200 COMMENT '充值类型(100-余额提款,200-押金退款,300-订单退款)',
  `f_channel` smallint default 0 COMMENT '提款渠道(1001-支付宝提款,1002-微信提款)',
  `f_suborder` int default 0 comment '子订单号(订单退款时,不能为空)',
  `f_account` varchar(200) not NULL COMMENT '提款账号',
  `f_bankorderid` varchar(100) default null comment '第三方订单号',
  `f_suctime` timestamp default '0000-00-00 00:00:00' comment '成功时间',
  `f_bankmoney` decimal(19,2) unsigned default '0.00'null comment '第三方订单金额',
  `f_status` smallint default 0 COMMENT '订单状态(0-默认,100-提款成功,-100-提款失败)',
  `f_drawnote` varchar(1000) default null comment '提款备注',
  `f_payorderid` int default null comment '退款对应网站充值订单号',
  `f_paybankorderid` varchar(100) default null comment '退款对应银行充值订单号',
  `f_addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB AUTO_INCREMENT=100001 DEFAULT CHARSET=utf8 COMMENT='用户管理-提款订单表';


/*用户管理-登录信息表*/
DROP TABLE IF EXISTS `t_user_login`;
CREATE TABLE `t_user_login` (
  `f_id` int NOT NULL AUTO_INCREMENT COMMENT '自增长ID',
  `f_usercheck` varchar(200) NOT NULL COMMENT '登录ck',
  `f_uid` int NOT NULL COMMENT '登录用户ID',
  `f_deviceid` varchar(200) DEFAULT NULL COMMENT '登录设备号',
  `f_platform` tinyint DEFAULT 1 COMMENT '平台：1 web主站,2 android, 3 IOS, 4 H5',
  `f_ip` varchar(50) DEFAULT NULL COMMENT '登录ip',
  `f_remark` varchar(500) DEFAULT NULL COMMENT '附属信息',
  `f_expiretime` timestamp not null default '0000-00-00 00:00:00' comment '登录过期时间(默认30天后过期)',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`),
  unique key (`f_usercheck`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '用户管理-登录信息表';


/*门店管理-资金信息表*/
DROP TABLE IF EXISTS `t_dineshop_account`;
CREATE TABLE `t_dineshop_account` (
  `f_id` smallint NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_sid` int NOT NULL COMMENT '门店id',
  `f_depositmoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '押金金额',
  `f_storemoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '账户余额',
  `f_proceeds` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '收益金余额',
  `f_addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='门店管理-资金信息表';


/*后台管理-用户信息表*/
DROP TABLE IF EXISTS `t_admin_userinfo`;
CREATE TABLE `t_admin_userinfo` (
  `f_uid` int NOT NULL AUTO_INCREMENT COMMENT '用户uid(自增)',
  `f_username` varchar(50) NOT NULL COMMENT '用户名',
  `f_realname` varchar(200) DEFAULT NULL COMMENT '真实姓名',
  `f_password` varchar(32) NOT NULL COMMENT '用户密码',
  `f_userstatus` smallint default 100 COMMENT '用户状态(默认100-正常用户,-100-禁用用户)',
  `f_addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_uid`),
  UNIQUE KEY `u_admin_userinfo` (`f_username`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='后台管理-用户信息表';
INSERT INTO `t_admin_userinfo`(`f_username`,`f_realname`,`f_password`,`f_addtime`) VALUE('sysadmin','系统管理员','F59BD65F7EDAFB087A81D4DCA06C4910',NOW());


/*后台管理-登录信息表*/
DROP TABLE IF EXISTS `t_admin_login`;
CREATE TABLE `t_admin_login` (
  `f_id` int NOT NULL AUTO_INCREMENT COMMENT '自增长ID',
  `f_usercheck` varchar(200) NOT NULL COMMENT '登录ck',
  `f_uid` int NOT NULL COMMENT '登录用户ID',
  `f_ip` varchar(50) DEFAULT NULL COMMENT '登录ip',
  `f_expiretime` timestamp not null default '0000-00-00 00:00:00' comment '登录过期时间(默认30min后过期)',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`),
  unique key (`f_usercheck`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 comment '用户管理-登录信息表';


/*后台管理-角色信息表*/
DROP TABLE IF EXISTS `t_admin_role`;
CREATE TABLE `t_admin_role` (
  `f_rid` int NOT NULL AUTO_INCREMENT COMMENT '角色rid(自增)',
  `f_name` varchar(100) NOT NULL COMMENT '角色名称',
  `f_describle` varchar(1000) default NULL COMMENT '角色描述',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_rid`),
  UNIQUE KEY `u_admin_role` (`f_name`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='后台管理-角色信息表';
INSERT INTO `t_admin_role`(`f_name`,`f_describle`) VALUE('管理员','管理员描述');


/*后台管理-模块信息表*/
DROP TABLE IF EXISTS `t_admin_module`;
CREATE TABLE `t_admin_module` (
  `f_mid` int NOT NULL AUTO_INCREMENT COMMENT '模块mid(自增)',
  `f_name` varchar(100) NOT NULL COMMENT '模块名称',
  `f_describle` varchar(1000) default NULL COMMENT '模块描述',
  `f_moduletype` tinyint default 0 comment '模块类型(0-虚节点,1-实节点)',
  `f_xpath` varchar(1000) default NULL COMMENT '模块访问路径(实节点不能为空)',
  `f_parentid` smallint default 0 comment '父模块ID(0为顶级模块)',
  `f_showorder` smallint default 1 comment '显示顺序',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_mid`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='后台管理-模块信息表';
INSERT INTO `t_admin_module`(`f_name`,`f_describle`,`f_moduletype`,`f_xpath`,`f_parentid`) VALUE('权限管理','权限管理描述',0,'',0);
INSERT INTO `t_admin_module`(`f_name`,`f_describle`,`f_moduletype`,`f_xpath`,`f_parentid`) VALUE('用户管理','用户增删改查',1,'/admin/user',10001);
INSERT INTO `t_admin_module`(`f_name`,`f_describle`,`f_moduletype`,`f_xpath`,`f_parentid`) VALUE('角色管理','角色增删改查',1,'/admin/role',10001);
INSERT INTO `t_admin_module`(`f_name`,`f_describle`,`f_moduletype`,`f_xpath`,`f_parentid`) VALUE('模块管理','模块增删改查',1,'/admin/module',10001);


/*后台管理-用户角色关联信息表*/
DROP TABLE IF EXISTS `t_admin_user_role`;
CREATE TABLE `t_admin_user_role` (
  `f_id` int not null AUTO_INCREMENT comment '自增ID',
  `f_uid` int NOT NULL COMMENT '用户ID',
  `f_rid` int NOT NULL COMMENT '角色ID',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`),
  UNIQUE KEY `u_admin_user_role` (`f_uid`,`f_rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台管理-用户角色关联信息表';
INSERT INTO `t_admin_user_role`(`f_uid`,`f_rid`) VALUE(10001,10001);


/*后台管理-角色模块关联信息表*/
DROP TABLE IF EXISTS `t_admin_role_module`;
CREATE TABLE `t_admin_role_module` (
  `f_id` int not null AUTO_INCREMENT comment '自增ID',
  `f_rid` int NOT NULL COMMENT '角色ID',
  `f_mid` int NOT NULL COMMENT '模块ID',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`),
  UNIQUE KEY `u_admin_role_module` (`f_rid`,`f_mid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台管理-角色模块关联信息表';
INSERT INTO `t_admin_role_module`(`f_rid`,`f_mid`) VALUE(10001,10001);
INSERT INTO `t_admin_role_module`(`f_rid`,`f_mid`) VALUE(10001,10002);
INSERT INTO `t_admin_role_module`(`f_rid`,`f_mid`) VALUE(10001,10003);
INSERT INTO `t_admin_role_module`(`f_rid`,`f_mid`) VALUE(10001,10004);


/*菜肴管理-口味信息表*/
DROP TABLE IF EXISTS `t_food_tastes`;
CREATE TABLE `t_food_tastes` (
  `f_tid` int NOT NULL AUTO_INCREMENT COMMENT '口味ID(自增)',
  `f_tname` varchar(200) NOT NULL COMMENT '口味名称',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜肴管理-口味信息表';
insert  into `t_food_tastes`(`f_tid`,`f_tname`) values (1,'不辣'),(2,'微辣'),(3,'超辣');


/*菜肴管理-菜系信息表*/
DROP TABLE IF EXISTS `t_food_cuisine`;
CREATE TABLE `t_food_cuisine` (
  `f_cid` int NOT NULL AUTO_INCREMENT COMMENT '菜系ID(自增)',
  `f_cname` varchar(200) NOT NULL COMMENT '菜系名称',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜肴管理-菜系信息表';
insert  into `t_food_cuisine`(`f_cid`,`f_cname`) values (1,'鲁菜'),(2,'川菜'),(3,'粤菜'),(4,'苏菜'),(5,'浙菜'),(6,'闽菜'),(7,'湘菜'),(8,'徽菜');


/*菜肴管理-菜品信息表*/
DROP TABLE IF EXISTS `t_food_dishes`;
CREATE TABLE `t_food_dishes` (
  `f_id` int NOT NULL AUTO_INCREMENT COMMENT '菜肴ID(自增)',
  `f_name` varchar(200) NOT NULL COMMENT '菜品名称',
  `f_price` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '菜品价格',
  `f_state` smallint NOT NULL COMMENT '菜品状态（-1已停售， 0初始， 1预售，100已售完）',
  `f_tastesid` int NOT NULL COMMENT '口味ID',
  `f_cuisineid` int NOT NULL COMMENT '菜系ID',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜肴管理-菜品信息表';


/*菜肴管理-菜单信息表*/
DROP TABLE IF EXISTS `t_food_menu`;
CREATE TABLE `t_food_menu` (
  `f_id` int NOT NULL AUTO_INCREMENT comment '菜单ID(自增)',
  `f_oid` int NOT NULL comment 'unknow',
  `f_foodid` int NOT NULL COMMENT '菜单ID',
  `f_foodname` varchar(200) DEFAULT NULL COMMENT '菜名',
  `f_foodicon` varchar(200) DEFAULT NULL COMMENT '菜品图片',
  `f_foodprice` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '菜品价格',
  `f_foodnum` varchar(200) DEFAULT NULL COMMENT '菜品数量',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='菜肴管理-菜单信息表';


/*用户管理-短信发送记录表*/
DROP TABLE IF EXISTS `t_user_smslog`;
CREATE TABLE `t_user_smslog` (
  `f_id` int NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_uid` int NOT NULL COMMENT '用户uid',
  `f_mobile` varchar(50) NOT NULL COMMENT '手机号码',
  `f_count` int default 0 COMMENT '发送成功次数',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`),
  UNIQUE KEY (`f_uid`,`f_mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户管理-短信发送记录表';


/*用户管理-冻结解冻表*/
DROP TABLE IF EXISTS `t_user_freezelog`;
CREATE TABLE `t_user_freezelog` (
  `f_id` int NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_uid` int NOT NULL COMMENT '用户uid',
  `f_inout` tinyint not null comment '冻结解冻类型(1-解冻,2-冻结)',
  `f_trademoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' comment '交易金额',
  `f_tradetype` smallint NOT NULL COMMENT '交易类型(1101-押金退款解冻,1102-订单支付解冻,2001-押金退款冻结,2002-订单支付冻结)',
  `f_tradenote` varchar(1000) default null comment '交易备注',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户管理-冻结解冻表';


/*后台管理-餐饮门店表审核表*/
DROP TABLE IF EXISTS `t_admin_dineshop`;
CREATE TABLE `t_admin_dineshop` (
  `f_sid` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '门店ID',
  `f_adduser` int(11) NOT NULL DEFAULT '0' COMMENT '添加用户',
  `f_shopname` varchar(200) NOT NULL COMMENT '门店名字',
  `f_status` int(11) NOT NULL DEFAULT '0' COMMENT '店铺状态（0初始，1审核中，100审核通过，-100审核不通过，-300已下架）',
  `f_shopdesc` varchar(200) DEFAULT '' COMMENT '店铺描述',
  `f_shopicon` varchar(200) DEFAULT '' COMMENT '店铺图标',
  `f_shophone` varchar(200) DEFAULT '' COMMENT '店铺联系电话',
  `f_address` varchar(200) NOT NULL COMMENT '门店地址',
  `f_menulist` varchar(200) DEFAULT NULL,
  `f_cuisineid` int(10) DEFAULT '0' COMMENT '菜系',
  `f_maplon` varchar(30) DEFAULT '' COMMENT '地图坐标-经度',
  `f_maplat` varchar(30) DEFAULT '' COMMENT '地图坐标-纬度',
  `f_sales` int(10) DEFAULT '0' COMMENT '月销量',
  `f_deliveryfee` int(10) DEFAULT '0' COMMENT '配送费',
  `f_minprice` int(10) DEFAULT '0' COMMENT '起送价',
  `f_minconsume` int(10) DEFAULT '0' COMMENT '堂食订单最低消费',
  `f_preconsume` int(10) DEFAULT '0' COMMENT '人均消费',
  `f_servicecharge` smallint default 0 comment '服务费(每就餐人数)',
  `f_isbooking` int(1) DEFAULT '0' COMMENT '是否可预订 0不可预订， 1可预订',
  `f_opentime` varchar(200) DEFAULT '' COMMENT '营业时间',
  `f_isaway` int(1) DEFAULT '0' COMMENT '是否支持外卖 0无外卖 1可外卖配送',
  `f_deliverytime` varchar(200) DEFAULT '' COMMENT '配送时间',
  `f_addtime` varchar(255) DEFAULT '' COMMENT '店铺入驻时间',
  `f_modtime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `f_fontshopid` int(11) DEFAULT NULL COMMENT '前端对应店铺ID',
  PRIMARY KEY (`f_sid`),
  UNIQUE KEY `s_add_name` (`f_adduser`) USING BTREE,
  UNIQUE KEY `s_add_shopname` (`f_shopname`),
  KEY `s_shop_maplat` (`f_maplon`) USING BTREE,
  KEY `s_shop_maplng` (`f_maplat`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台管理-餐饮门店表审核表';


/*菜肴管理-菜品信息表后台审核表*/
DROP TABLE IF EXISTS `t_admin_food_dishes`;
CREATE TABLE `t_admin_food_dishes` (
  `f_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '菜肴ID(自增)',
  `f_adduser` int(11) NOT NULL DEFAULT '0' COMMENT '添加用户',
  `f_status` int(11) NOT NULL DEFAULT '0' COMMENT '店铺状态（0初始，1审核中，100审核通过，-100审核不通过，-300已下架）',
  `f_sid` int(11) NOT NULL COMMENT '店铺ID',
  `f_icon` varchar(200) DEFAULT NULL COMMENT '菜品图片',
  `f_name` varchar(200) NOT NULL COMMENT '菜品名称',
  `f_desc` text COMMENT '描述',
  `f_price` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '菜品价格',
  `f_discount` double(11,2) DEFAULT '1.00' COMMENT '默认折扣（1表示不打折）',
  `f_state` smallint(6) NOT NULL DEFAULT '0' COMMENT '菜品状态（-1已停售， 0初始， 1预售，100已售完）',
  `f_salenum` int(11) default '0' COMMENT '月销量',
  `f_tastesid` varchar(11) DEFAULT NULL COMMENT '口味ID',
  `f_cuisineid` int(11) DEFAULT NULL COMMENT '菜系ID',
  `f_classid` int(11) DEFAULT NULL COMMENT '菜品分类',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `f_fontdishid` int(11) DEFAULT NULL COMMENT '前端菜品ID',
  PRIMARY KEY (`f_id`),
  UNIQUE KEY `food_dishes_name` (`f_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜肴管理-菜品信息表后台审核表';


/*餐饮门店表*/
DROP TABLE IF EXISTS `t_dineshop`;
CREATE TABLE `t_dineshop` (
  `f_sid` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '门店ID',
  `f_shopname` varchar(200) NOT NULL COMMENT '门店名字',
  `f_shopdesc` varchar(200) DEFAULT NULL COMMENT '店铺描述',
  `f_shopicon` varchar(200) DEFAULT NULL COMMENT '店铺图标',
  `f_shophone` varchar(200) DEFAULT NULL COMMENT '店铺联系电话',
  `f_address` varchar(200) NOT NULL COMMENT '门店地址',
  `f_cuisineid` int(10) DEFAULT NULL COMMENT '菜系',
  `f_menulist` varchar(255) DEFAULT NULL COMMENT '菜单列表',
  `f_maplon` varchar(30) DEFAULT NULL COMMENT '地图坐标-经度',
  `f_maplat` varchar(30) DEFAULT NULL COMMENT '地图坐标-纬度',
  `f_sales` int(10) DEFAULT '0' COMMENT '月销量',
  `f_deliveryfee` int(10) DEFAULT '0' COMMENT '配送费',
  `f_minprice` int(10) DEFAULT '0' COMMENT '起送价',
  `f_minconsume` int(10) DEFAULT '0' COMMENT '堂食订单最低消费',
  `f_preconsume` int(10) DEFAULT '0' COMMENT '人均消费',
  `f_servicecharge` smallint default 0 comment '服务费(每就餐人数)',
  `f_isbooking` int(1) DEFAULT '0' COMMENT '是否可预订 0不可预订， 1可预订',
  `f_opentime` varchar(200) DEFAULT NULL COMMENT '营业时间',
  `f_isaway` int(1) DEFAULT '0' COMMENT '是否支持外卖 0无外卖 1可外卖配送',
  `f_deliverytime` varchar(200) DEFAULT NULL COMMENT '配送时间',
  `f_addtime` varchar(255) DEFAULT NULL COMMENT '店铺入驻时间',
  `f_modtime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`f_sid`,`f_shopname`),
  UNIQUE KEY `s_shop_only` (`f_shopname`,`f_address`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='餐饮门店表';


/*门店管理-门店信息表*/
DROP TABLE IF EXISTS `t_dineshop_deskinfo`;
CREATE TABLE `t_dineshop_deskinfo` (
  `f_id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_deskid` varchar(50) not null comment '桌型ID',
  `f_sid` int(11) NOT NULL COMMENT '门店id',
  `f_seatnum` tinyint(4) NOT NULL DEFAULT '1' COMMENT '可坐人数',
  `f_amount` tinyint(4) NOT NULL DEFAULT '1' COMMENT '桌子数量',
  `f_orderamount` tinyint(4) NOT NULL DEFAULT '0' COMMENT '已预订数量',
  `f_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态(1-有效,0-无效/已删除)',
  `f_addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`),
  UNIQUE KEY `t_sid_seatnum` (`f_sid`,`f_deskid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='门店管理-门店信息表';


/*门店管理-折扣信息表*/
DROP TABLE IF EXISTS `t_dineshop_discount`;
CREATE TABLE `t_dineshop_discount` (
  `f_id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_sid` int(11) NOT NULL COMMENT '门店id',
  `f_date` date DEFAULT NULL COMMENT '折扣日期',
  `f_timeslot` int(11) DEFAULT NULL COMMENT '折扣时间段ID',
  `f_discount` varchar(200) DEFAULT NULL COMMENT '折扣信息',
  `f_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态(1-有效,0-无效/已删除)',
  `f_addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`),
  UNIQUE KEY `t_discount_unique` (`f_sid`,`f_date`,`f_timeslot`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='门店管理-折扣信息表';


/*门店管理-折扣时间段*/
DROP TABLE IF EXISTS `t_dineshop_discount_timeslot`;
CREATE TABLE `t_dineshop_discount_timeslot` (
  `f_id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_starttime` varchar(10) DEFAULT NULL COMMENT '折扣开始时间',
  `f_endtime` varchar(10) DEFAULT NULL COMMENT '折扣结束时间',
  `f_addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`),
  UNIQUE KEY `t_startime_endtime` (`f_starttime`,`f_endtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='门店管理-折扣时间段';
insert  into `t_dineshop_discount_timeslot`(`f_starttime`,`f_endtime`,`f_addtime`) values ('13:00','14:00',now()),('16:00','17:00',now()),('17:00','18:00',now()),('00:00','03:00',now()),('17:00','20:00',now());


/*配送员信息表*/
DROP TABLE IF EXISTS `t_dineshop_distripersion`;
CREATE TABLE `t_dineshop_distripersion` (
  `f_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '配送人员ID',
  `f_dineshopid` int(10) DEFAULT NULL COMMENT '店铺ID',
  `f_username` varchar(200) DEFAULT NULL COMMENT '配送人员名字',
  `f_mobile` varchar(200) DEFAULT NULL COMMENT '配送员联系方式',
  `f_state` int(10) NOT NULL COMMENT '配送人员状态（0初始，-1已禁止）',
  `f_addtime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '配送人员添加时间',
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='配送员信息表';


/*餐饮门店表-推荐门店*/
DROP TABLE IF EXISTS `t_dineshop_recom`;
CREATE TABLE `t_dineshop_recom` (
  `f_sid` bigint(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '门店ID',
  `f_shopname` varchar(200) NOT NULL COMMENT '门店名字',
  `f_shopicon` varchar(200) DEFAULT NULL COMMENT '店铺图标',
  `f_sort` int(10) DEFAULT NULL,
  `f_addtime` varchar(255) DEFAULT NULL COMMENT '店铺入驻时间',
  `f_modtime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`f_sid`),
  KEY `s_shop_id` (`f_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='餐饮门店表-推荐门店';


/*门店管理-放号信息表*/
DROP TABLE IF EXISTS `t_dineshop_sellinfo`;
CREATE TABLE `t_dineshop_sellinfo` (
  `f_id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `f_sid` int(11) NOT NULL COMMENT '门店id',
  `f_date` date DEFAULT NULL COMMENT '折扣日期',
  `f_timeslot` int(11) DEFAULT NULL COMMENT '折扣时间段ID',
  `f_sellinfo` varchar(200) DEFAULT NULL COMMENT '折扣信息',
  `f_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态(1-有效,0-无效/已删除)',
  `f_addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '添加时间',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`),
  UNIQUE KEY `t_discount_unique` (`f_sid`,`f_date`,`f_timeslot`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='门店管理-放号信息表';


/*菜肴管理-分类信息管理*/
DROP TABLE IF EXISTS `t_food_classify`;
CREATE TABLE `t_food_classify` (
  `f_cid` int(11) NOT NULL AUTO_INCREMENT COMMENT '口味ID(自增)',
  `f_cname` varchar(200) NOT NULL COMMENT '口味名称',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜肴管理-分类信息管理';
insert  into `t_food_classify`(`f_cid`,`f_cname`) values (1,'招牌推荐'),(2,'开胃前菜'),(3,'美味甜品'),(4,'养生面食'),(5,'老火靓汤'),(6,'酒水饮料'),(7,'主食米饭'),(8,'家常小菜');


/*菜肴管理-菜系信息表*/
DROP TABLE IF EXISTS `t_food_cuisine`;
CREATE TABLE `t_food_cuisine` (
  `f_cid` int(11) NOT NULL AUTO_INCREMENT COMMENT '菜系ID(自增)',
  `f_cname` varchar(200) NOT NULL COMMENT '菜系名称',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_cid`),
  UNIQUE KEY `food_cuisine_name` (`f_cname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜肴管理-菜系信息表';


/*菜肴管理-菜品信息表*/
DROP TABLE IF EXISTS `t_food_dishes`;
CREATE TABLE `t_food_dishes` (
  `f_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '菜肴ID(自增)',
  `f_sid` int(11) NOT NULL DEFAULT '0' COMMENT '店铺ID',
  `f_icon` varchar(200) DEFAULT NULL COMMENT '菜品图片',
  `f_name` varchar(200) NOT NULL COMMENT '菜品名称',
  `f_desc` text COMMENT '描述',
  `f_price` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '菜品价格',
  `f_discount` double(11,2) DEFAULT '1.00' COMMENT '默认折扣（1表示不打折）',
  `f_state` smallint(6) NOT NULL DEFAULT '0' COMMENT '菜品状态（-1已停售， 0初始， 1预售，100已售完）',
  `f_salenum` int(11) default '0' COMMENT '月销量',
  `f_tastesid` varchar(11) DEFAULT NULL COMMENT '口味ID',
  `f_cuisineid` int(11) DEFAULT NULL COMMENT '菜系ID',
  `f_classid` int(11) DEFAULT NULL COMMENT '菜品分类',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`),
  UNIQUE KEY `food_dishes_name` (`f_name`,`f_sid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜肴管理-菜品信息表';


/*菜肴管理-菜品信息表-推荐菜品*/
DROP TABLE IF EXISTS `t_food_dishes_recom`;
CREATE TABLE `t_food_dishes_recom` (
  `f_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '菜肴ID',
  `f_name` varchar(200) NOT NULL COMMENT '菜品名称',
  `f_price` varchar(200) NOT NULL DEFAULT '0' COMMENT '菜品价格',
  `f_state` int(10) NOT NULL COMMENT '菜品状态（-1已停售， 0初始， 1预售，100已售完）',
  `f_addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '添加时间',
  `f_tastesid` int(10) NOT NULL COMMENT '口味ID',
  `f_cuisineid` int(10) NOT NULL COMMENT '菜系ID',
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜肴管理-菜品信息表-推荐菜品';


/*菜肴管理-菜单信息表*/
DROP TABLE IF EXISTS `t_food_menu`;
CREATE TABLE `t_food_menu` (
  `f_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '菜单ID(自增)',
  `f_oid` int(11) NOT NULL COMMENT 'unknow',
  `f_foodid` int(11) NOT NULL COMMENT '菜单ID',
  `f_foodname` varchar(200) DEFAULT NULL COMMENT '菜名',
  `f_foodicon` varchar(200) DEFAULT NULL COMMENT '菜品图片',
  `f_foodprice` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '菜品价格',
  `f_foodnum` varchar(200) DEFAULT NULL COMMENT '菜品数量',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='菜肴管理-菜单信息表';


/*菜肴管理-口味信息表*/
DROP TABLE IF EXISTS `t_food_tastes`;
CREATE TABLE `t_food_tastes` (
  `f_tid` int(11) NOT NULL AUTO_INCREMENT COMMENT '口味ID(自增)',
  `f_tname` varchar(200) NOT NULL COMMENT '口味名称',
  `f_lasttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`f_tid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜肴管理-口味信息表';


/*外卖及预订订单表*/
DROP TABLE IF EXISTS `t_orders`;
CREATE TABLE `t_orders` (
  `f_oid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID,唯一标识',
  `f_shopid` bigint(20) NOT NULL COMMENT '店铺ID',
  `f_userid` int(10) NOT NULL COMMENT '用户ID',
  `f_type` int(2) NOT NULL DEFAULT '1' COMMENT '订单类型（1,外卖订单  2,食堂订单）',
  `f_status` int(2) NOT NULL DEFAULT '1' COMMENT '订单状态（0,初始 1,未付款 2,已付款 3,配送中 4,配送完成 5,用餐中 6,申请打包 90,已打包 100,已完成 -100逾期 -110退款待审核 -120退款审核通过 -130退款审核不通过 -200退款中 -300退款完成， -400已取消）',
  `f_orderdetail` varchar(255) NOT NULL COMMENT '订单详情',
  `f_ordermoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `f_deliverymoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '配送费',
  `f_allmoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单总金额',
  `f_paymoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '已支付金额',
  `f_paytype` varchar(200) DEFAULT '0' COMMENT '支付方式（0余额，1微信，2支付宝）',
  `f_mealsnum` int(10) DEFAULT '0' COMMENT '就餐人数（仅食堂订单有）',
  `f_deskid` varchar(50) default null comment '预订桌型ID（仅食堂订单有）',
  `f_servicemoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '服务费（仅食堂订单有）',
  `f_startime` varchar(200) DEFAULT '' COMMENT '用餐开始时间（仅食堂订单有）',
  `f_endtime` varchar(200) DEFAULT '' COMMENT '用餐结束时间（仅食堂订单有）',
  `f_deliveryid` int(10) DEFAULT '0' COMMENT '配送员ID（仅外卖订单有）',
  `f_deliverytime` varchar(20) DEFAULT '' COMMENT '配送时间',
  `f_addressid` int(10) DEFAULT NULL COMMENT '配送地址ID（仅外卖订单有）',
  `f_hassuborder` tinyint unsigned default 0 comment '是否有子订单(0-无，1-有)',
  `f_addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '订单时间',
  `f_modtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '订单更新时间',
  PRIMARY KEY (`f_oid`)
) ENGINE=InnoDB AUTO_INCREMENT=1000000001 DEFAULT CHARSET=utf8 COMMENT='外卖及预订订单表';

/*堂食子订单表*/
DROP TABLE IF EXISTS `t_sub_orders`;
CREATE TABLE `t_sub_orders` (
  `f_oid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单ID,唯一标识',
  `f_parentid` bigint unsigned not null comment '关联订单ID',
  `f_userid` int(10) NOT NULL COMMENT '用户ID',
  `f_status` int(2) NOT NULL DEFAULT '1' COMMENT '订单状态（0,初始 1,未付款 2,已付款）',
  `f_orderdetail` varchar(255) NOT NULL COMMENT '订单详情',
  `f_ordermoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '订单金额',
  `f_paymoney` decimal(19,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '已支付金额',
  `f_paytype` varchar(200) DEFAULT '0' COMMENT '支付方式（0余额，1微信，2支付宝）',
  `f_addtime` varchar(200) DEFAULT '' COMMENT '订单时间',
  `f_modtime` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '订单更新时间',
  PRIMARY KEY (`f_oid`)
) ENGINE=InnoDB AUTO_INCREMENT=1000000001 DEFAULT CHARSET=utf8 COMMENT='堂食子订单表';