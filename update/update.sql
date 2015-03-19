3.2;
ALTER TABLE `%DB_PREFIX%user` ADD COLUMN `user_type`  tinyint NOT NULL COMMENT '用户类型 0普通用户 1 企业用户';
ALTER TABLE `%DB_PREFIX%deal` ADD COLUMN `risk_security`  text NOT NULL COMMENT '风险保障' AFTER `risk_rank`;
CREATE TABLE `%DB_PREFIX%user_company` (
  `user_id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL COMMENT '公司名称',
  `contact` varchar(50) NOT NULL default '' COMMENT '法人代表',
  `officetype` varchar(50) NOT NULL COMMENT '公司类别',
  `officedomain` varchar(50) NOT NULL COMMENT '公司行业',
  `officecale` varchar(50) NOT NULL COMMENT '公司规模',
  `register_capital` varchar(50) NOT NULL COMMENT '注册资金',
  `asset_value` varchar(100) NOT NULL COMMENT '资产净值',
  `officeaddress` varchar(255) NOT NULL COMMENT '公司地址',
  `description` text NOT NULL COMMENT '公司简介'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公司信息标';

UPDATE `%DB_PREFIX%conf` SET `tip`='请输入帮助类文章编号' WHERE `name`='AGREEMENT' or `name`='PRIVACY';
UPDATE `%DB_PREFIX%conf` SET `tip`='帮助类文章编号，在我要借款填写资料处显示' WHERE `name`='BORROW_AGREEMENT';

ALTER TABLE `%DB_PREFIX%deal_agency` ADD COLUMN `view_info`  text NOT NULL COMMENT '资料展示' AFTER `bind_verify`;

ALTER TABLE `%DB_PREFIX%article` ADD COLUMN `icon`  varchar(255) NOT NULL COMMENT '图标' AFTER `title`;

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `%DB_PREFIX%ecv`
-- ----------------------------
DROP TABLE IF EXISTS `%DB_PREFIX%ecv`;
CREATE TABLE `%DB_PREFIX%ecv` (
  `id` int(11) NOT NULL auto_increment,
  `sn` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `use_limit` int(11) NOT NULL,
  `use_count` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `begin_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `money` decimal(20,4) NOT NULL,
  `ecv_type_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unk_sn` (`sn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='优惠券列表';

-- ----------------------------
-- Records of `%DB_PREFIX%ecv
-- ----------------------------

-- ----------------------------
-- Table structure for `%DB_PREFIX%ecv_type`
-- ----------------------------
DROP TABLE IF EXISTS `%DB_PREFIX%ecv_type`;
CREATE TABLE `%DB_PREFIX%ecv_type` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `money` decimal(20,4) NOT NULL,
  `use_limit` int(11) NOT NULL,
  `begin_time` int(11) NOT NULL,
  `end_time` int(11) NOT NULL,
  `gen_count` int(11) NOT NULL,
  `send_type` tinyint(1) NOT NULL,
  `exchange_score` int(11) NOT NULL,
  `exchange_limit` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='优惠券类型';


ALTER TABLE `%DB_PREFIX%payment_notice`
ADD COLUMN `pay_date`  date NULL DEFAULT NULL COMMENT '收款日期',
ADD INDEX `idx_pn_001` (`pay_date`) ;

update `%DB_PREFIX%payment_notice` set pay_date = FROM_UNIXTIME( pay_time + 8 * 3600, '%Y%m%d');

UPDATE `%DB_PREFIX%conf` SET `group_id`='7',`sort`='0' WHERE `name`='USER_LOCK_MONEY';
UPDATE `%DB_PREFIX%conf` SET `is_conf`='1' WHERE `name`='USER_REGISTER_MONEY';
update `%DB_PREFIX%conf` set is_conf=0 where name='OPEN_IPS';
update `%DB_PREFIX%conf` set is_conf=0 where name='IPS_MERCODE';
update `%DB_PREFIX%conf` set is_conf=0 where name='IPS_KEY';

ALTER TABLE `%DB_PREFIX%payment` MODIFY COLUMN `fee_amount`  decimal(20,4) NOT NULL COMMENT '手续费用的计费值' ;
ALTER TABLE `%DB_PREFIX%deal_repay` ADD COLUMN `is_site_bad`  tinyint(1) NOT NULL COMMENT '是否坏账  0不是，1坏账 管理员看到的' ;
ALTER TABLE `%DB_PREFIX%deal_repay` CHANGE COLUMN `mange_impose_money` `manage_impose_money`  decimal(20,4) NOT NULL COMMENT '逾期管理费';


CREATE TABLE `%DB_PREFIX%deal_repay_log` (
  `id` int(11) NOT NULL auto_increment,
  `repay_id` int(11) NOT NULL COMMENT '账单ID',
  `log` text NOT NULL COMMENT '日志',
  `adm_id` int(11) NOT NULL COMMENT '操作管理员',
  `user_id` int(11) NOT NULL COMMENT '操作用户',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



CREATE TABLE `%DB_PREFIX%quota_submit` (
  `id` int(11) NOT NULL auto_increment,
  `money` int(11) NOT NULL COMMENT '申请金额',
  `referraler` varchar(100) NOT NULL COMMENT '推荐人',
  `memo` text NOT NULL COMMENT '详细说明',
  `other_memo` text NOT NULL COMMENT ' 其他地方借款详细说明',
  `create_time` int(11) NOT NULL COMMENT '申请时间',
  `status` tinyint(1) NOT NULL COMMENT '0未处理 1申请通过  2申请失败',
  `op_time` int(11) NOT NULL COMMENT '审核时间',
  `bad_msg` text NOT NULL COMMENT '失败原因',
  `user_id` int(11) NOT NULL,
  `msg` text NOT NULL COMMENT '备注',
   `note` text COMMENT '操作备注',
  PRIMARY KEY  (`id`),
  KEY `idx0` (`status`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='额度申请表';

CREATE TABLE `%DB_PREFIX%user_money_log` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL COMMENT '关联用户',
  `money` decimal(20,2) NOT NULL COMMENT '操作金额',
  `account_money` decimal(20,2) NOT NULL COMMENT '当前账户余额',
  `memo` text  NOT NULL COMMENT '操作备注',
  `type` tinyint(2) NOT NULL COMMENT '0结存，1充值，2投标成功，3招标成功，4偿还本息，5回收本息，6提前还款，7提前回收，8申请提现，9提现手续费，10借款管理费，11逾期罚息，12逾期管理费，13人工充值，14借款服务费，15出售债权，16购买债权，17债权转让管理费，18开户奖励，19流标还返，20投标管理费，21投标逾期收入，22兑换，23邀请返利，24投标返利，25签到成功，26逾期罚金（垫付后），27其他费用',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  `create_time_ymd` date NOT NULL COMMENT '操作时间 ymd',
  `create_time_ym` int(6) NOT NULL COMMENT '操作时间 ym',
  `create_time_y` int(4) NOT NULL COMMENT '操作时间 y',
  PRIMARY KEY  (`id`),
  KEY `idx0` (`user_id`,`type`,`create_time`),
  KEY `idx1` (`user_id`,`type`,`create_time_ymd`),
  KEY `idx2` (`user_id`,`type`),
  KEY `create_time_ymd` (`create_time_ymd`),
  KEY `idx3` (`type`,`create_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员资金日志表';

INSERT INTO `%DB_PREFIX%user_money_log` (`user_id`,`account_money`,`create_time`,`create_time_ymd`,`create_time_ym`,`create_time_y`,`memo`) SELECT id,`money`,unix_timestamp()-28800,FROM_UNIXTIME(UNIX_TIMESTAMP(),'%Y-%m-%d'),FROM_UNIXTIME(UNIX_TIMESTAMP(),'%Y%m'),FROM_UNIXTIME(UNIX_TIMESTAMP(),'%Y'),'升级3.2'  FROM `%DB_PREFIX%user`;

CREATE TABLE `%DB_PREFIX%user_point_log` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL COMMENT '关联用户',
  `point` decimal(20,2) NOT NULL COMMENT '操作信用积分',
  `account_point` decimal(20,2) NOT NULL COMMENT '当前账户信用积分',
  `memo` text NOT NULL COMMENT '操作备注',
  `type` tinyint(2) NOT NULL COMMENT '0结存，4偿还本息，5回收本息，6提前还款，7提前回收，8申请认证，11逾期还款，13人工充值，14借款服务费，18开户奖励，22兑换，23邀请返利，24投标返利，25签到成功',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  `create_time_ymd` date NOT NULL COMMENT '操作时间 ymd',
  `create_time_ym` int(6) NOT NULL COMMENT '操作时间 ym',
  `create_time_y` int(4) NOT NULL COMMENT '操作时间 y',
  PRIMARY KEY  (`id`),
  KEY `idx0` (`user_id`,`type`,`create_time`),
  KEY `idx1` (`user_id`,`type`,`create_time_ymd`),
  KEY `idx2` (`user_id`,`type`),
  KEY `create_time_ymd` (`create_time_ymd`),
  KEY `idx3` (`type`,`create_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员信用积分日志表';

INSERT INTO `%DB_PREFIX%user_point_log` (`user_id`,`account_point`,`create_time`,`create_time_ymd`,`create_time_ym`,`create_time_y`,`memo`) SELECT id,`point`,unix_timestamp()-28800,FROM_UNIXTIME(UNIX_TIMESTAMP(),'%Y-%m-%d'),FROM_UNIXTIME(UNIX_TIMESTAMP(),'%Y%m'),FROM_UNIXTIME(UNIX_TIMESTAMP(),'%Y'),'升级3.2'  FROM `%DB_PREFIX%user`;


CREATE TABLE `%DB_PREFIX%site_money_log` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL COMMENT '关联用户',
  `money` decimal(20,2) NOT NULL COMMENT '操作金额',
  `memo` text NOT NULL COMMENT '操作备注',
  `type` tinyint(2) NOT NULL COMMENT '7提前回收，9提现手续费，10借款管理费，12逾期管理费，13人工充值，14借款服务费，17债权转让管理费，18开户奖励，20投标管理费，22兑换，23邀请返利，24投标返利，25签到成功，26逾期罚金（垫付后），27其他费用',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  `create_time_ymd` date NOT NULL COMMENT '操作时间 ymd',
  `create_time_ym` int(6) NOT NULL COMMENT '操作时间 ym',
  `create_time_y` int(4) NOT NULL COMMENT '操作时间 y',
  PRIMARY KEY  (`id`),
  KEY `idx0` (`user_id`,`type`,`create_time`),
  KEY `idx1` (`user_id`,`type`,`create_time_ymd`),
  KEY `idx2` (`user_id`,`type`),
  KEY `create_time_ymd` (`create_time_ymd`),
  KEY `idx3` (`type`,`create_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='网站收益日志表';


ALTER TABLE `%DB_PREFIX%deal` ADD COLUMN `uloadtype`  tinyint(1) NOT NULL COMMENT '用户投标类型 0按金额，1 按份数';
ALTER TABLE `%DB_PREFIX%deal` ADD COLUMN `portion`  int(11) NOT NULL COMMENT '分成多少份';
ALTER TABLE `%DB_PREFIX%deal` ADD COLUMN `max_portion`  int(11) NOT NULL COMMENT '最多买多少份';

INSERT INTO `%DB_PREFIX%conf` (`name`, `value`, `group_id`, `input_type`, `is_effect`, `is_conf`, `sort`, `tip`,`value_scope`) VALUES ('IPS_3DES_IV', '', '4', '0', '1', '0', '71', '', '');
INSERT INTO `%DB_PREFIX%conf` (`name`, `value`, `group_id`, `input_type`, `is_effect`, `is_conf`, `sort`, `tip`,`value_scope`) VALUES ('IPS_3DES_KEY', '', '4', '0', '1', '0', '71', '', '');
INSERT INTO `%DB_PREFIX%conf` (`name`, `value`, `group_id`, `input_type`, `is_effect`, `is_conf`, `sort`, `tip`,`value_scope`) VALUES ('IPS_FEE_TYPE', '', '4', '1', '1', '0', '71', '谁付IPS手续费', '1,2');

INSERT INTO `%DB_PREFIX%msg_template` (`name`,`content`,`type`) VALUES ('TPL_QUOTA_SUCCESS_SMS', '尊敬的{$notice.site_name}用户{$notice.user_name}，您的{$notice.quota_money}元信用额度申请已成功，感谢您的关注和支持。', '0');
INSERT INTO `%DB_PREFIX%msg_template` (`name`,`content`,`type`) VALUES ('TPL_QUOTA_FAILED_SMS', '尊敬的{$notice.site_name}用户{$notice.user_name}，您的{$notice.quota_money}元信用额度申请 因 “{$notice.msg}” 审核失败了。', '0');

ALTER TABLE `%DB_PREFIX%generation_repay` ADD COLUMN `status`  tinyint NOT NULL COMMENT '0待收款 1已收款 ';
ALTER TABLE `%DB_PREFIX%generation_repay` ADD COLUMN `memo`  text NOT NULL COMMENT '操作备注';

ALTER TABLE `%DB_PREFIX%user_carry` MODIFY COLUMN `status`  tinyint(1) NOT NULL COMMENT '0待审核，1已付款，2未通过，3待付款';

UPDATE `%DB_PREFIX%article_cate` SET `sort`='19' WHERE (`id`='14');
UPDATE `%DB_PREFIX%article_cate` SET `sort`='18' WHERE (`id`='15');
UPDATE `%DB_PREFIX%article_cate` SET `sort`='17' WHERE (`id`='16');
UPDATE `%DB_PREFIX%article_cate` SET `sort`='16' WHERE (`id`='17');
UPDATE `%DB_PREFIX%article_cate` SET `sort`='15' WHERE (`id`='18');
UPDATE `%DB_PREFIX%article_cate` SET `sort`='14' WHERE (`id`='19');


ALTER TABLE `%DB_PREFIX%deal_load` ADD COLUMN `is_old_loan`  tinyint(1) NOT NULL COMMENT '历史投标 0 不是  1 是 ';
UPDATE `%DB_PREFIX%deal_load` SET `is_old_loan` = 1;

INSERT INTO `%DB_PREFIX%msg_template` (`name`,`content`,`type`) VALUES ('TPL_SMS_REPAY_SUCCESS_MSG', '尊敬的{$notice.site_name}用户{$notice.user_name}，您的借款“{$notice.deal_name}”在第{$notice.index}期{$notice.status}还款{$notice.all_money}元，感谢您的关注和支持。', '0');

ALTER TABLE `%DB_PREFIX%email_verify_code` MODIFY COLUMN `email`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `%DB_PREFIX%user` ADD COLUMN `referer_memo`  varchar(255) NOT NULL  COMMENT '邀请备注' AFTER `pid`;

UPDATE `%DB_PREFIX%conf` SET `is_conf`='0', `group_id`='0' ,`sort`='1' WHERE `name`='INVITE_REFERRALS';
UPDATE `%DB_PREFIX%conf` SET `group_id`='0', `is_conf`='0' WHERE `name`='REFERRAL_IP_LIMIT';
INSERT INTO `%DB_PREFIX%conf` (`name`, `value`, `group_id`, `input_type`, `is_effect`, `is_conf`, `sort`) VALUES ('INVITE_REFERRALS_MIN', '10', '0', '0', '1', '0', '2');
INSERT INTO `%DB_PREFIX%conf` (`name`, `value`, `group_id`, `input_type`, `is_effect`, `is_conf`, `sort`) VALUES ('INVITE_REFERRALS_MAX', '20', '0', '0', '1', '0', '3');
INSERT INTO `%DB_PREFIX%conf` (`name`, `value`, `group_id`, `input_type`, `is_effect`, `is_conf`, `sort`) VALUES ('INVITE_REFERRALS_RATE', '1', '0', '0', '1', '0', '4');
DELETE FROM `%DB_PREFIX%conf` WHERE `name`='ONLINE_MSN';
UPDATE `%DB_PREFIX%conf` SET `is_conf`='0', `group_id`='0'  WHERE `name`='ONLINE_QQ';

UPDATE `%DB_PREFIX%message_type` SET `show_name`='普通贷款' WHERE `type_name`='deal';
INSERT INTO `%DB_PREFIX%message_type` (`type_name`, `is_fix`, `show_name`, `is_effect`, `sort`) VALUES ('transfer', '1', '债券转让', '1', '0');

DROP TABLE IF EXISTS `%DB_PREFIX%bank`;
CREATE TABLE `%DB_PREFIX%bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL COMMENT '银行名称',
  `is_rec` tinyint(1) NOT NULL COMMENT '是否推荐',
  `day` int(11) NOT NULL COMMENT '处理时间',
  `sort` int(11) NOT NULL COMMENT '银行排序',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of `%DB_PREFIX%bank
-- ----------------------------
INSERT INTO `%DB_PREFIX%bank` VALUES ('1', '中国工商银行', '1', '3', '0', './public/bank/1.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('2', '中国农业银行', '1', '3', '0', './public/bank/2.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('3', '中国建设银行', '1', '3', '0', './public/bank/3.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('4', '招商银行', '1', '3', '0', './public/bank/4.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('5', '中国光大银行', '1', '3', '0', './public/bank/5.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('6', '中国邮政储蓄银行', '1', '3', '0', './public/bank/6.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('7', '兴业银行', '1', '3', '0', './public/bank/7.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('8', '中国银行', '0', '3', '0', './public/bank/8.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('9', '交通银行', '0', '3', '3', './public/bank/9.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('10', '中信银行', '0', '3', '0', './public/bank/10.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('11', '华夏银行', '0', '3', '0', './public/bank/11.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('12', '上海浦东发展银行', '0', '3', '1', './public/bank/12.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('13', '城市信用社', '0', '3', '0', './public/bank/13.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('14', '恒丰银行', '0', '3', '0', './public/bank/14.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('15', '广东发展银行', '0', '3', '0', './public/bank/15.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('16', '深圳发展银行', '0', '3', '2', './public/bank/16.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('17', '中国民生银行', '0', '3', '0', './public/bank/17.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('18', '中国农业发展银行', '0', '3', '0', './public/bank/18.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('19', '农村商业银行', '0', '3', '0', './public/bank/19.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('20', '农村信用社', '0', '3', '0', './public/bank/20.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('21', '城市商业银行', '0', '3', '0', './public/bank/21.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('22', '农村合作银行', '0', '3', '0', './public/bank/22.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('23', '浙商银行', '0', '3', '0', './public/bank/23.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('24', '上海农商银行', '0', '3', '0', './public/bank/24.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('25', '中国进出口银行', '0', '3', '0', './public/bank/25.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('26', '渤海银行', '0', '3', '0', './public/bank/26.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('27', '国家开发银行', '0', '3', '0', './public/bank/27.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('28', '村镇银行', '0', '3', '0', './public/bank/28.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('29', '徽商银行股份有限公司', '0', '3', '0', './public/bank/29.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('30', '南洋商业银行', '0', '3', '0', './public/bank/30.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('31', '韩亚银行', '0', '3', '0', './public/bank/31.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('32', '花旗银行', '0', '3', '0', './public/bank/32.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('33', '渣打银行', '0', '3', '0', './public/bank/33.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('34', '华一银行', '0', '3', '0', './public/bank/34.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('35', '东亚银行', '0', '3', '0', './public/bank/35.jpg');
INSERT INTO `%DB_PREFIX%bank` VALUES ('36', '苏格兰皇家银行', '1', '1', '26', './public/bank/36.jpg');

ALTER TABLE `%DB_PREFIX%deal_load`
ADD COLUMN `create_date`  date NOT NULL COMMENT '记录投资日期,方便统计使用',
ADD COLUMN `rebate_money`  decimal(20,2) NOT NULL DEFAULT 0 COMMENT '返利金额',
ADD INDEX `idx_dl_001` (`create_date`) ;

update `%DB_PREFIX%deal_load` set create_date = FROM_UNIXTIME(create_time + 28800,'%Y-%m-%d');
update `%DB_PREFIX%deal_load` s set rebate_money = money*(select CONVERT(user_bid_rebate,DECIMAL)  from `%DB_PREFIX%deal` where id=s.deal_id) *0.01;


ALTER TABLE `%DB_PREFIX%deal_load_repay` MODIFY COLUMN `repay_manage_impose_money`  decimal(20,4) NOT NULL COMMENT '借款者均摊下来的逾期管理费';


CREATE TABLE `%DB_PREFIX%deal_quota_submit` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `titlecolor` varchar(10) NOT NULL COMMENT '标题颜色',
  `name` varchar(255) NOT NULL COMMENT '贷款标题',
  `sub_name` varchar(255) NOT NULL COMMENT '短名称',
  `view_info` text NOT NULL COMMENT '资料展示',
  `citys` text NOT NULL COMMENT '城市（序列化）',
  `cate_id` int(11) NOT NULL COMMENT '贷款分类',
  `agency_id` int(11) NOT NULL COMMENT '担保机构',
  `warrant` tinyint(4) NOT NULL,
  `guarantor_margin_amt` decimal(20,2) NOT NULL COMMENT '担保保证金',
  `guarantor_amt` decimal(20,2) NOT NULL COMMENT '担保金额',
  `guarantor_pro_fit_amt` decimal(20,2) NOT NULL COMMENT '担保收益',
  `icon` varchar(255) NOT NULL COMMENT '图标',
  `type_id` int(11) NOT NULL COMMENT '借款用途',
  `loantype` int(11) NOT NULL,
  `borrow_amount` decimal(20,2) NOT NULL COMMENT '申请额度',
  `guarantees_amt` decimal(20,2) NOT NULL COMMENT '借款保证金',
  `enddate` int(11) NOT NULL COMMENT '筹款日期',
  `rate` decimal(10,2) NOT NULL COMMENT '借款利率',
  `services_fee` varchar(20) NOT NULL COMMENT '成交服务费',
  `manage_fee` varchar(20) NOT NULL COMMENT '借款者管理费',
  `user_loan_manage_fee` varchar(20) NOT NULL COMMENT '投资者管理费',
  `manage_impose_fee_day1` varchar(20) NOT NULL COMMENT '普通逾期管理费',
  `manage_impose_fee_day2` varchar(20) NOT NULL COMMENT '逾期管理费总额',
  `impose_fee_day1` varchar(20) NOT NULL COMMENT '普通逾期罚息',
  `impose_fee_day2` varchar(20) NOT NULL COMMENT '严重逾期罚息',
  `user_load_transfer_fee` varchar(20) NOT NULL COMMENT '债权转让管理费',
  `user_bid_rebate` varchar(20) NOT NULL COMMENT '投资人返利',
  `compensate_fee` varchar(20) NOT NULL COMMENT '提前还款补偿',
  `generation_position` varchar(20) NOT NULL COMMENT '申请延期比率',
  `description` text NOT NULL COMMENT '借款内容',
  `risk_rank` tinyint(4) NOT NULL COMMENT '风险等级',
  `risk_security` text NOT NULL COMMENT '风险控制',
  `is_effect` tinyint(1) NOT NULL COMMENT '默认是否有效',
  `status` tinyint(4) NOT NULL COMMENT '0待处理，1审核通过，2审核失败',
  `op_memo` text NOT NULL COMMENT '操作备注',
  `bad_msg` text NOT NULL COMMENT '失败原因',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  `update_time` int(11) NOT NULL COMMENT '处理时间',
  PRIMARY KEY  (`id`),
  KEY `idx0` (`user_id`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='授信额度申请';


ALTER TABLE `%DB_PREFIX%deal_repay`
ADD COLUMN `repay_date`  date NOT NULL COMMENT '预期还款日期,日期格式方便统计' AFTER `is_site_bad`,
ADD COLUMN `true_repay_date`  date NOT NULL COMMENT '实际还款日期,日期格式方便统计' AFTER `repay_date`,
ADD COLUMN `true_repay_money`  double(20,2) NOT NULL DEFAULT 0 COMMENT '实还金额' AFTER `true_repay_date`,
ADD INDEX `idx_dr_001` (`true_repay_date`),
ADD INDEX `idx_dr_002` (`repay_date`);


update `%DB_PREFIX%deal_repay` set repay_date = FROM_UNIXTIME(repay_time + 28800,'%Y-%m-%d');
update `%DB_PREFIX%deal_repay` set true_repay_date = FROM_UNIXTIME(true_repay_time + 28800,'%Y-%m-%d') WHERE has_repay=1;


update `%DB_PREFIX%deal_repay` a set a.true_repay_money =
 (select sum(l.repay_money + l.impose_money + l.repay_manage_money + l.repay_manage_impose_money) from `%DB_PREFIX%deal_load_repay` l where l.has_repay = 1 and l.deal_id = a.deal_id and l.repay_id = a.id)
;

ALTER TABLE `%DB_PREFIX%deal_load_repay`
MODIFY COLUMN `repay_time`  int(11) NOT NULL COMMENT '预计回款时间' AFTER `impose_money`,
MODIFY COLUMN `true_repay_time`  int(11) NOT NULL COMMENT '实际回款时间' AFTER `repay_time`,
ADD COLUMN `true_repay_date`  date NOT NULL COMMENT '实际回款时间,方便统计使用' AFTER `true_repay_time`,
ADD COLUMN `repay_date`  date NOT NULL COMMENT '预计回款时间,方便统计' AFTER `repay_time`;

update `%DB_PREFIX%deal_load_repay` set repay_date = FROM_UNIXTIME(repay_time + 28800,'%Y-%m-%d');
update `%DB_PREFIX%deal_load_repay` set true_repay_date = FROM_UNIXTIME(true_repay_time + 28800,'%Y-%m-%d');

ALTER TABLE `%DB_PREFIX%deal_load_repay`
ADD INDEX `idx_dl_001` (`repay_date`) ,
ADD INDEX `idx_dl_002` (`true_repay_date`) ;

ALTER TABLE `%DB_PREFIX%deal_repay`
ADD COLUMN `self_money`  decimal(20,2) NOT NULL DEFAULT 0 COMMENT '需还本金' AFTER `true_repay_money`;

update `%DB_PREFIX%deal_repay` a set a.self_money = (select sum(l.self_money) from `%DB_PREFIX%deal_load_repay` l where l.deal_id = a.deal_id and l.repay_id = a.id);

ALTER TABLE `%DB_PREFIX%deal`
ADD COLUMN `apart_borrow_amount`  decimal(10,0) NOT NULL COMMENT '拆标后的原借款标金额' AFTER `borrow_amount`;


DROP TABLE IF EXISTS `%DB_PREFIX%user_contacter`;

ALTER TABLE `%DB_PREFIX%deal_load_transfer`
ADD COLUMN `create_date`  date NOT NULL COMMENT '发布时间,日期格式,方便统计' AFTER `pMerBillNo`,
ADD COLUMN `transfer_date`  date NOT NULL COMMENT '承接时间,日期格式,方便统计' AFTER `create_date`,
ADD INDEX `idx_dlt_001` (`create_date`),
ADD INDEX `idx_dlt_002` (`transfer_date`);


update `%DB_PREFIX%deal_load_transfer` set create_date  = FROM_UNIXTIME(create_time + 28800, '%Y-%m-%d');
update `%DB_PREFIX%deal_load_transfer` set transfer_date  = FROM_UNIXTIME(transfer_time + 28800, '%Y-%m-%d');


ALTER TABLE `%DB_PREFIX%deal`
ADD COLUMN `start_date`  date NOT NULL COMMENT '开始投标时间，日期格式，方便统计' AFTER `max_portion`,
ADD COLUMN `repay_start_date`  date NOT NULL COMMENT '满标放款,支出奖励时间,日期格式,方便统计' AFTER `start_date`,
ADD COLUMN `bad_date`  date NOT NULL COMMENT '流标时间,日期格式,方便统计' AFTER `repay_start_date`,
ADD INDEX `idx_d_001` (`start_date`),
ADD INDEX `idx_d_002` (`repay_start_date`),
ADD INDEX `idx_d_003` (`bad_date`);

ALTER TABLE `%DB_PREFIX%user` ADD COLUMN `register_ip`  varchar(50) NOT NULL COMMENT '注册IP' AFTER `user_type`;



ALTER TABLE `%DB_PREFIX%deal_repay`
ADD COLUMN `true_self_money`  decimal(20,2) NOT NULL COMMENT '实际还款本金' AFTER `true_repay_money`,
ADD COLUMN `interest_money`  decimal(20,2) NOT NULL COMMENT '待还利息   repay_money - self_money' AFTER `true_self_money`,
ADD COLUMN `true_interest_money`  decimal(20,2) NOT NULL COMMENT '实际还利息' AFTER `interest_money`,
ADD COLUMN `true_manage_money`  decimal(20,2) NOT NULL COMMENT '实际管理费' AFTER `true_interest_money`;

update `%DB_PREFIX%deal_repay` set interest_money = repay_money-self_money;
update `%DB_PREFIX%deal_repay` a set true_self_money = (select sum(l.self_money) from `%DB_PREFIX%deal_load_repay` l where l.deal_id = a.deal_id and l.repay_id = a.id) where has_repay=1;
update `%DB_PREFIX%deal_repay` set true_interest_money = true_repay_money-true_self_money where has_repay=1;
update `%DB_PREFIX%deal_repay` set true_manage_money = manage_money where has_repay=1;

ALTER TABLE `%DB_PREFIX%deal_load_repay`
ADD COLUMN `true_repay_money`  decimal(20,2) NOT NULL COMMENT '真实还款本息' AFTER `true_repay_date`,
ADD COLUMN `true_self_money`  decimal(20,2) NOT NULL COMMENT '真实还款本金' AFTER `true_repay_money`,
ADD COLUMN `interest_money`  decimal(20,2) NOT NULL COMMENT '利息   repay_money - self_money' AFTER `true_self_money`,
ADD COLUMN `true_interest_money`  decimal(20,2) NOT NULL COMMENT '实际利息' AFTER `interest_money`,
ADD COLUMN `true_manage_money`  decimal(20,2) NOT NULL COMMENT '实际管理费' AFTER `true_interest_money`,
ADD COLUMN `true_repay_manage_money`  decimal(20,2) NOT NULL AFTER `true_manage_money`;

UPDATE `%DB_PREFIX%deal_load_repay` SET true_repay_money = repay_money WHERE has_repay=1;
UPDATE `%DB_PREFIX%deal_load_repay` SET true_self_money = self_money WHERE has_repay=1;
UPDATE `%DB_PREFIX%deal_load_repay` SET interest_money = repay_money-self_money;
UPDATE `%DB_PREFIX%deal_load_repay` SET true_interest_money = true_repay_money - true_self_money WHERE has_repay=1;
UPDATE `%DB_PREFIX%deal_load_repay` SET true_manage_money = manage_money WHERE has_repay=1;
UPDATE `%DB_PREFIX%deal_load_repay` SET true_repay_manage_money = repay_manage_money WHERE has_repay=1;

ALTER TABLE `%DB_PREFIX%generation_repay`
ADD COLUMN `self_money`  decimal(20,2) NOT NULL AFTER `agency_id`,
ADD COLUMN `interest_money`  decimal(20,2) NOT NULL AFTER `self_money`,
ADD COLUMN `create_date`  date NOT NULL AFTER `create_time`;
update `%DB_PREFIX%generation_repay` set create_date  = FROM_UNIXTIME(create_time + 28800, '%Y-%m-%d');
update `%DB_PREFIX%generation_repay` a set self_money  = (select sum(l.true_self_money) from `%DB_PREFIX%deal_load_repay` l where l.deal_id = a.deal_id and l.repay_id = a.repay_id and l.is_site_repay > 0 and l.has_repay=1) ;
update `%DB_PREFIX%generation_repay` a set interest_money  = (select sum(l.true_interest_money) from `%DB_PREFIX%deal_load_repay` l where l.deal_id = a.deal_id and l.repay_id = a.repay_id and l.is_site_repay > 0 and l.has_repay=1) ;

ALTER TABLE `%DB_PREFIX%deal_repay` ADD COLUMN `loantype` int NOT NULL COMMENT '还款方式';
ALTER TABLE `%DB_PREFIX%deal_load_repay` ADD COLUMN `loantype` int NOT NULL COMMENT '还款方式';

UPDATE `%DB_PREFIX%deal_repay` s SET loantype = (select loantype from `%DB_PREFIX%deal` where id=s.deal_id) ;
UPDATE `%DB_PREFIX%deal_load_repay` s SET loantype = (select loantype from `%DB_PREFIX%deal` where id=s.deal_id) ;

UPDATE `%DB_PREFIX%conf` SET `value` = REPLACE (`value`,'{if $deal.repay_time_type eq 0}{function name="to_date" v=$deal.type_next_repay_time f="Y-m-d"}{else}{function name="to_date" v="$deal.end_repay_time" f="Y年m月d日"}{/if}','{function name="to_date" v="$deal.end_repay_time" f="Y年m月d日"}') WHERE `name` = 'CONTRACT_0';
UPDATE `%DB_PREFIX%conf` SET `value` = REPLACE (`value`,'{if $deal.repay_time_type eq 0}{function name="to_date" v=$deal.type_next_repay_time f="Y-m-d"}{else}每月    {function name="to_date" v="$deal.repay_start_time" f="d"}{/if}','{if $deal.repay_time_type neq 0}每月    {/if}{function name="to_date" v="$deal.end_repay_time" f="Y年m月d日"}') WHERE `name` = 'CONTRACT_0';

UPDATE `%DB_PREFIX%conf` SET `value` = REPLACE (`value`,'{if $deal.repay_time_type eq 0}{function name="to_date" v=$deal.type_next_repay_time f="Y-m-d"}{else}{function name="to_date" v="$deal.end_repay_time" f="Y年m月d日"}{/if}','{function name="to_date" v="$deal.end_repay_time" f="Y年m月d日"}') WHERE `name` = 'CONTRACT_1';
UPDATE `%DB_PREFIX%conf` SET `value` = REPLACE (`value`,'{if $deal.repay_time_type eq 0}{function name="to_date" v=$deal.type_next_repay_time f="Y-m-d"}{else}每月    {function name="to_date" v="$deal.repay_start_time" f="d"}{/if}','{if $deal.repay_time_type neq 0}每月    {/if}{function name="to_date" v="$deal.end_repay_time" f="Y年m月d日"}') WHERE `name` = 'CONTRACT_1';

ALTER TABLE `%DB_PREFIX%deal_inrepay_repay` MODIFY COLUMN `repay_money`  decimal(20,2) NOT NULL COMMENT '提前还款多少',
MODIFY COLUMN `manage_money`  decimal(20,2) NOT NULL COMMENT '提前还款管理费',
MODIFY COLUMN `impose`  decimal(20,2) NOT NULL COMMENT '提前还款罚息',
ADD COLUMN `self_money`  decimal(20,2) NOT NULL COMMENT '提前还款本金';





ALTER TABLE `%DB_PREFIX%payment`
ADD COLUMN `pay_fee_type`  tinyint(1) NOT NULL DEFAULT 0 COMMENT '支付公司收费类型' AFTER `fee_type`,
ADD COLUMN `pay_fee_amount`  decimal(20,2) NOT NULL DEFAULT 0 COMMENT '支付公司收费收费' AFTER `pay_fee_type`;

ALTER TABLE `%DB_PREFIX%payment_notice`
ADD COLUMN `bank_id`  varchar(50) NULL COMMENT '直联银行编号' AFTER `pay_date`;

ALTER TABLE `%DB_PREFIX%payment_notice`
ADD COLUMN `fee_amount`  decimal(20,2) NOT NULL DEFAULT 0 COMMENT '收用户手续费' AFTER `bank_id`,
ADD COLUMN `pay_fee_amount`  decimal(20,2) NOT NULL DEFAULT 0 COMMENT '平台付支付公司手续费' AFTER `fee_amount`;

ALTER TABLE `%DB_PREFIX%payment_notice`
ADD COLUMN `create_date`  date NOT NULL COMMENT '记录充值下单时间,方便统计使用' AFTER `pay_date`,
ADD INDEX `idx_pn_002` USING BTREE  (`create_date`);

update `%DB_PREFIX%payment_notice` set create_date = FROM_UNIXTIME(create_time + 28800,'%Y-%m-%d');


ALTER TABLE `%DB_PREFIX%user_carry`
ADD COLUMN `create_date`  date NOT NULL COMMENT '记录提现提交日期，方便统计使用' AFTER `region_lv4`,
ADD INDEX `idx_uc_001` USING BTREE (`create_date`);

update `%DB_PREFIX%user_carry` set create_date = FROM_UNIXTIME(create_time + 28800,'%Y-%m-%d');


ALTER TABLE `%DB_PREFIX%user`
ADD COLUMN `create_date`  date NOT NULL COMMENT '记录注册日期，方便统计使用' AFTER `user_type`,
ADD INDEX `idx_u_001` USING BTREE (`create_date`);

update `%DB_PREFIX%user` set create_date = FROM_UNIXTIME(create_time + 28800,'%Y-%m-%d');


ALTER TABLE `%DB_PREFIX%generation_repay`
ADD COLUMN `create_date`  date NOT NULL COMMENT '记录代还时间，方便统计使用' AFTER `memo`,
ADD INDEX `idx_gr_001` USING BTREE (`create_date`);

update `%DB_PREFIX%generation_repay` set create_date = FROM_UNIXTIME(create_time + 28800,'%Y-%m-%d');


CREATE TABLE `%DB_PREFIX%user_lock_money_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '关联用户',
  `lock_money` decimal(20,2) NOT NULL COMMENT '操作金额',
  `account_lock_money` decimal(20,2) NOT NULL COMMENT '当前账户余额',
  `memo` text NOT NULL COMMENT '操作备注',
  `type` tinyint(2) NOT NULL COMMENT '0结存，1充值，2投标成功，8申请提现，9提现手续费，10借款管理费，18开户奖励，19流标还返',
  `create_time` int(11) NOT NULL COMMENT '操作时间',
  `create_time_ymd` date NOT NULL COMMENT '操作时间 ymd',
  `create_time_ym` int(6) NOT NULL COMMENT '操作时间 ym',
  `create_time_y` int(4) NOT NULL COMMENT '操作时间 y',
  PRIMARY KEY (`id`),
  KEY `idx0` (`user_id`,`type`,`create_time`),
  KEY `idx1` (`user_id`,`type`,`create_time_ymd`),
  KEY `idx2` (`user_id`,`type`),
  KEY `create_time_ymd` (`create_time_ymd`),
  KEY `idx3` (`type`,`create_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员资金日志表';

INSERT INTO `%DB_PREFIX%user_lock_money_log` (`user_id`,`account_lock_money`,`create_time`,`create_time_ymd`,`create_time_ym`,`create_time_y`,`memo`) SELECT id,`lock_money`,unix_timestamp()-28800,FROM_UNIXTIME(UNIX_TIMESTAMP(),'%Y-%m-%d'),FROM_UNIXTIME(UNIX_TIMESTAMP(),'%Y%m'),FROM_UNIXTIME(UNIX_TIMESTAMP(),'%Y'),'升级3.2'  FROM `%DB_PREFIX%user`;


CREATE TABLE `%DB_PREFIX%ips_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `create_date` date NOT NULL,
  `strxml` text,
  `html` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

UPDATE `%DB_PREFIX%conf` set `value` = '3.2' where name = 'DB_VERSION';
