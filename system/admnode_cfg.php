<?php 
return array( 
	"Index,Statistics"	=>	array(
		"name"	=>	"系统首页", 
		"node"	=>	array( 
			"statistics"	=>	array("name"=>"网站数据统计","module"=>"Index","action"=>"statistics"),
			"manage_carry"	=>	array("name"=>"管理员提现列表","module"=>"Index","action"=>"manage_carry"),
			"de_manage_carry"	=>	array("name"=>"管理员提现删除执行","module"=>"Index","action"=>"de_manage_carry"),
			"index"	=>	array("name"=>"借款统计","module"=>"Statistics","action"=>"index"),
		)
	),
	
	
	"Log"	=>	array(
		"name"	=>	"系统日志", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"系统日志列表","action"=>"index"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			
		)
	),
	
	
	"Deal,Option"	=>	array(
		"name"	=>	"贷款管理", 
		"node"	=>	array(	
			"index"	=>	array("name"=>"全部贷款列表","module"=>"Deal","action"=>"index"),
			"publish"	=>	array("name"=>"待审核贷款","module"=>"Deal","action"=>"publish"),
			"wait"	=>	array("name"=>"订单材料贷款","module"=>"Deal","action"=>"wait"),
			"ing"	=>	array("name"=>"进行的贷款","module"=>"Deal","action"=>"ing"),
			"flow"	=>	array("name"=>"流标的贷款","module"=>"Deal","action"=>"flow"),
			"full"	=>	array("name"=>"满标的贷款","module"=>"Deal","action"=>"full"),
			"inrepay"	=>	array("name"=>"还款中贷款","module"=>"Deal","action"=>"inrepay"),
			"over"	=>	array("name"=>"已完成贷款","module"=>"Deal","action"=>"inrepay"),
			"three"	=>	array("name"=>"待还款账单","module"=>"Deal","action"=>"three"),	
			"export_csv_three"	=>	array("name"=>"导出待还款账单","module"=>"Deal","action"=>"three"),	
			"three_msg"	=>	array("name"=>"催款短信提醒","module"=>"Deal","action"=>"three_msg"),
			"yuqi"	=>	array("name"=>"逾期待收款","module"=>"Deal","action"=>"yuqi"),
			"export_csv_yuqi"	=>	array("name"=>"导出逾期待收款","module"=>"Deal","action"=>"export_csv_yuqi"),
			"generation_repay"	=>	array("name"=>"垫付待收款","module"=>"Deal","action"=>"generation_repay"),
			"op_generation_repay_status"	=>	array("name"=>"垫付待收款操作","module"=>"Deal","action"=>"op_generation_repay_status"),
			"export_csv_generation"	=>	array("name"=>"导出垫付待收款","module"=>"Deal","action"=>"export_csv_generation"),
			"user_loads_repay"	=>	array("name"=>"收款信息","module"=>"Deal","action"=>"user_loads_repay"),
			"repay_log"	=>	array("name"=>"借贷记录日志","module"=>"Deal","action"=>"repay_log"),
			"repay_plan"	=>	array("name"=>"还款计划","module"=>"Deal","action"=>"repay_plan"),
			"do_site_repay"	=>	array("name"=>"网站代还款","module"=>"Deal","action"=>"do_site_repay"),
			"show_detail"	=>	array("name"=>"投标详情和操作","module"=>"Deal","action"=>"show_detail"),
			"apart"	=>	array("name"=>"拆标操作","module"=>"Deal","action"=>"apart"),
			"insert"	=>	array("name"=>"添加贷款","module"=>"Deal","action"=>"insert"),
			"update"	=>	array("name"=>"编辑贷款","module"=>"Deal","action"=>"update"),
			"delete"	=>	array("name"=>"删除","module"=>"Deal","action"=>"delete"),		
			"trash"	=>	array("name"=>"贷款回收站","module"=>"Deal","action"=>"trash"),
			"restore"	=>	array("name"=>"恢复","module"=>"Deal","action"=>"restore"),
			"foreverdelete"	=>	array("name"=>"永久删除","module"=>"Deal","action"=>"foreverdelete"),			
			"set_effect"	=>	array("name"=>"设置生效","module"=>"Deal","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"排序","module"=>"Deal","action"=>"set_sort"),			
		)
	),
	"loads"	=>	array(
		"name"	=>	"投标记录", 
		"node"	=>	array(	
			"index"	=>	array("name"=>"所有投标","action"=>"index"),
			"hand"	=>	array("name"=>"手动投标","action"=>"hand"),
			"auto"	=>	array("name"=>"自动投标","action"=>"auto"),
			"sussess"	=>	array("name"=>"成功的投标","action"=>"sussess"),
			"failed"	=>	array("name"=>"失败的投标","action"=>"failed"),
		)
	),
	
	"Transfer"	=>	array(
		"name"	=>	"债权转让", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"所有转让","action"=>"index"),
			"ing"	=>	array("name"=>"正在转让","action"=>"ing"),
			"sussess"	=>	array("name"=>"成功转让","action"=>"sussess"),
			"back"	=>	array("name"=>"撤销转让","action"=>"back"),	
			"reback"	=>	array("name"=>"撤销操作","action"=>"reback"),	
		)
	),
	
	"Message"	=>	array(
		"name"	=>	"留言管理", 
		"node"	=>	array( 			
			"index"	=>	array("name"=>"留言列表","action"=>"index"),
			"update"	=>	array("name"=>"回复留言","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	
	
	"User,Option"	=>	array(
		"name"	=>	"会员管理", 
		"node"	=>	array( 
			"index"				=>	array("name"=>"普通会员","module"=>"User","action"=>"index"),
			"register"			=>	array("name"=>"待审核普通会员","module"=>"User","action"=>"register"),
			"info"				=>	array("name"=>"普通会员信息","module"=>"User","action"=>"info"),
			"trash"				=>	array("name"=>"普通会员回收站","module"=>"User","action"=>"trash"),
			"company_index"		=>	array("name"=>"企业会员","module"=>"User","action"=>"company_index"),
			"company_register"	=>	array("name"=>"待审核企业会员","module"=>"User","action"=>"company_register"),
			"company_info"		=>	array("name"=>"企业会员信息","module"=>"User","action"=>"company_info"),
			"company_trash"		=>	array("name"=>"企业会员回收站","module"=>"User","action"=>"company_trash"),
			"company_manage"	=>	array("name"=>"公司列表","module"=>"User","action"=>"company_manage"),
			"work_manage"		=>	array("name"=>"工作列表","module"=>"User","action"=>"work_manage"),
			"work"				=>	array("name"=>"工作信息","module"=>"User","action"=>"work"),
			"bank_manage"		=>	array("name"=>"银行卡管理","module"=>"User","action"=>"bank_manage"),
			"de_bank"			=>	array("name"=>"删除银行卡","module"=>"User","action"=>"de_bank"),
			"passed"			=>	array("name"=>"认证信息","module"=>"User","action"=>"passed"),
			"info_down"			=>	array("name"=>"资料下载","module"=>"User","action"=>"info_down"),
			"view_info"			=>	array("name"=>"资料展示","module"=>"User","action"=>"view_info"),
			"insert"			=>	array("name"=>"添加执行","module"=>"User","action"=>"insert"),
			"update"			=>	array("name"=>"编辑执行","module"=>"User","action"=>"update"),
			"delete"			=>	array("name"=>"删除","module"=>"User","action"=>"delete"),
			"export_csv"		=>	array("name"=>"导出csv","module"=>"User","action"=>"export_csv"),
			"foreverdelete"		=>	array("name"=>"永久删除","module"=>"User","action"=>"foreverdelete"),
			"account_detail"	=>	array("name"=>"帐户详情","module"=>"User","action"=>"account_detail"),
			"restore"			=>	array("name"=>"恢复","module"=>"User","action"=>"restore"),
			"set_effect"		=>	array("name"=>"设置生效","module"=>"User","action"=>"set_effect"),
			
		)
	),
	
	"UserField"	=>	array(
		"name"	=>	"会员字段", 
		"node"	=>	array( 
			"index"			=>	array("name"=>"会员字段列表","action"=>"index"),
			"insert"		=>	array("name"=>"添加执行","action"=>"insert"),
			"update"		=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"set_sort"		=>	array("name"=>"排序","action"=>"set_sort"),
		)
	),
	"UserGroup"	=>	array(
		"name"	=>	"会员组别", 
		"node"	=>	array( 
			"index"			=>	array("name"=>"会员组别列表","action"=>"index"),
			"insert"		=>	array("name"=>"添加执行","action"=>"insert"),
			"update"		=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	"UserLevel"	=>	array(
		"name"	=>	"会员等级", 
		"node"	=>	array( 
			"index"			=>	array("name"=>"会员等级列表","action"=>"index"),
			"insert"		=>	array("name"=>"添加提交","action"=>"insert"),
			"update"		=>	array("name"=>"编辑提交","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	
	"MsgSystem"	=>	array(
		"name"	=>	"站内消息群发", 
		"node"	=>	array( 
			"index"			=>	array("name"=>"消息列表","action"=>"index"),
			"insert"		=>	array("name"=>"添加提交","action"=>"insert"),
			"update"		=>	array("name"=>"编辑提交","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	
	"MsgBox"	=>	array(
		"name"	=>	"消息记录", 
		"node"	=>	array(
			"index"			=>	array("name"=>"记录列表","action"=>"index"),
			"view"			=>	array("name"=>"查看记录","action"=>"view"), 
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	
	"PaymentNotice,BankReconciliation"	=>	array(
		"name"	=>	"充值管理", 
		"node"	=>	array( 
			"PaymentNotice_index"		=>	array("name"=>"在线充值单","module"=>"PaymentNotice","action"=>"index"),
			"BankReconciliation_index"		=>	array("name"=>"在线充值日账单","module"=>"BankReconciliation","action"=>"index"),
			"online"	=>	array("name"=>"线下充值单","module"=>"PaymentNotice","action"=>"online"),
			"update"	=>	array("name"=>"管理员收款","module"=>"PaymentNotice","action"=>"update"),
		)
	),
	
	
	
	"UserCarry"	=>	array(
		"name"	=>	"提现申请管理", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"所有申请","action"=>"index"),
			"wait"	=>	array("name"=>"待审申请","action"=>"wait"),
			"waitpay"	=>	array("name"=>"代付申请","action"=>"waitpay"),
			"success"	=>	array("name"=>"成功申请","action"=>"success"),
			"failed"	=>	array("name"=>"失败申请","action"=>"success"),
			"reback"	=>	array("name"=>"撤销的申请","action"=>"reback"),
			"edit"	    =>	array("name"=>"查看/处理 ","action"=>"edit"),
			"delete"	=>	array("name"=>"永久删除","action"=>"delete"),
		)
	),
	
	"User,Deal"	=>	array(
		"name"	=>	"资金日志", 
		"node"	=>	array( 
			"fund_management"	=>	array("name"=>"会员资金日志","module"=>"User","action"=>"fund_management"),
			"site_money"	    =>	array("name"=>"网站收支","module"=>"Deal","action"=>"site_money"),
		)
	),
	"User,MoneyLog"	=>	array(
		"name"	=>	"手动操作", 
		"node"	=>	array( 
			"update_hand_recharge"	=>	array("name"=>"快速充值","module"=>"User","action"=>"update_hand_recharge"),
			"update_hand_overdue"	=>	array("name"=>"快速扣款","module"=>"User","action"=>"update_hand_overdue"),
			"update_hand_freeze"	=>	array("name"=>"冻结资金","module"=>"User","action"=>"update_hand_freeze"),
			"update_hand_integral"	=>	array("name"=>"变更积分","module"=>"User","action"=>"update_hand_integral"),
			"update_hand_quota"     =>	array("name"=>"变更额度","module"=>"User","action"=>"update_hand_quota"),
		)
	),
	
	"Ipslog,IpsRelation,IpsFullscale,IpsTransfer,IpsProfit"	=>	array(
		"name"	=>	"IPS托管对账", 
		"node"	=>	array( 			
			"Ipslog_create"	=>	array("name"=>"开户列表","module"=>"Ipslog","action"=>"create"),
			"Ipslog_trade"	=>	array("name"=>"标的登记","module"=>"Ipslog","action"=>"trade"),
			"Ipslog_creditor"	=>	array("name"=>"投标记录","module"=>"Ipslog","action"=>"creditor"),
			"Ipslog_guarantor"	=>	array("name"=>"担保方记录","module"=>"Ipslog","action"=>"guarantor"),
			"Ipslog_recharge"	=>	array("name"=>"充值记录","module"=>"Ipslog","action"=>"recharge"),
			"Ipslog_transfer"	=>	array("name"=>"提现记录","module"=>"Ipslog","action"=>"transfer"),
			"IpsRelation_repayment"	=>	array("name"=>"还款单","module"=>"IpsRelation","action"=>"repayment"),
			"IpsFullscale_index"	    =>	array("name"=>"满标放款","module"=>"IpsFullscale","action"=>"index"),
			"IpsTransfer_index"     =>	array("name"=>"债权转让","module"=>"IpsTransfer","action"=>"index"),
			"IpsProfit_index"	    =>	array("name"=>"担保收益","module"=>"IpsProfit","action"=>"index"),
		)
	),
	
	
	"GenerationRepaySubmit"	=>	array(
		"name"	=>	"续约申请", 
		"node"	=>	array(	
			"index"	=>	array("name"=>"申请列表","action"=>"index"),
			"edit"	=>	array("name"=>"申请处理","action"=>"edit"),	
		)
	),
	
	"DealQuotaSubmit"	=>	array(
		"name"	=>	"授信额度申请", 
		"node"	=>	array( 
			"index"  =>	array("name"=>"申请列表","action"=>"index"),
			"edit"	 =>	array("name"=>"查看/处理 ","action"=>"edit"),
			"delete" =>	array("name"=>"永久删除","action"=>"delete"),
		)
	),
	
	
	"QuotaSubmit"	=>	array(
		"name"	=>	"信用额度申请", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"申请列表","action"=>"index"),
			"edit"	=>	array("name"=>"查看/处理 ","action"=>"edit"),
			"delete"	=>	array("name"=>"永久删除","action"=>"delete"),
		)
	),
	
	
	"Reportguy"	=>	array(
		"name"	=>	"举报管理", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"举报列表","action"=>"index"),
			"edit"	=>	array("name"=>"举报处理 ","action"=>"edit"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	
	"Credit,Option"	=>	array(
		"name"	=>	"认证管理", 
		"node"	=>	array( 
			"user"	=>	array("name"=>"所有认证 ","module"=>"Credit","action"=>"user"),
			"user_wait"	=>	array("name"=>"待审的认证 ","module"=>"Credit","action"=>"user_wait"),
			"user_success"	=>	array("name"=>"通过的认证 ","module"=>"Credit","action"=>"user_success"),
			"user_bad"	=>	array("name"=>"失败的认证 ","module"=>"Credit","action"=>"user_bad"),
			"op_passed"	=>	array("name"=>"认证操作 ","module"=>"Credit","action"=>"op_passed"),
		)
	),
	
	"Referrals,CreateRelevance,PromotionHuman"	=>	array(
		"name"	=>	"邀请返利", 
		"node"	=>	array( 
			"Referrals_index"			=>	array("name"=>"邀请返利列表","module"=>"Referrals","action"=>"index"),
			"Referrals_pay"			=>	array("name"=>"发放返利","module"=>"Referrals","action"=>"pay"),
			"CreateRelevance_index"			=>	array("name"=>"建立关联列表","module"=>"CreateRelevance","action"=>"index"),
			"CreateRelevance_edit"			=>	array("name"=>"建立关联","module"=>"CreateRelevance","action"=>"edit"),
			"CreateRelevance_del_referrals"	=>	array("name"=>"取消关联","module"=>"CreateRelevance","action"=>"del_referrals"),
			"PromotionHuman_index"	=>	array("name"=>"推广人列表","module"=>"PromotionHuman","action"=>"index"),
		)
	),
	
	
	"StatisticsBorrow"	=>	array(
		"name"	=>	"借出统计", 
		"node"	=>	array( 			
			"tender_total"	=>	array("name"=>"借出总统计","action"=>"tender_total"),
			"tender_usernum_total"	=>	array("name"=>"投资人数","action"=>"tender_usernum_total"),
			"tender_account_total"	=>	array("name"=>"投资金额","action"=>"tender_account_total"),
			"tender_borrow_type"	=>	array("name"=>"标种投资","action"=>"tender_borrow_type"),
			"tender_hasback_total"	=>	array("name"=>"已回款","action"=>"tender_hasback_total"),
			"tender_tobe_receivables"	=>	array("name"=>"待收款","action"=>"tender_tobe_receivables"),
			"tender_rank_list"	=>	array("name"=>"投资排名","action"=>"tender_rank_list"),
		)
	),
	
	"StatisticsLoan"	=>	array(
		"name"	=>	"借入统计", 
		"node"	=>	array( 			
			"loan_total"			=>	array("name"=>"借入总统计","action"=>"loan_total"),
			"loan_usernum_total"	=>	array("name"=>"借款人数","action"=>"loan_usernum_total"),
			"loan_account_total"	=>	array("name"=>"借款金额","action"=>"loan_account_total"),
			"loan_borrow_type"		=>	array("name"=>"标种借款","action"=>"loan_borrow_type"),
			"loan_hasback_total"	=>	array("name"=>"已还款","action"=>"loan_hasback_total"),
			"loan_tobe_receivables"	=>	array("name"=>"待还款","action"=>"loan_tobe_receivables"),
			"loan_repay_late_total"	=>	array("name"=>"逾期还款","action"=>"loan_repay_late_total"),
		)
	),
	
	"StatisticsClaims"	=>	array(
		"name"	=>	"债权统计", 
		"node"	=>	array( 			
			"change_account_total"	=>	array("name"=>"债权转让","action"=>"change_account_total"),
		)
	),
	
	"WebsiteStatistics"	=>	array(
		"name"	=>	"平台统计", 
		"node"	=>	array( 			
			"website_recharge_total"	=>	array("name"=>"充值统计","action"=>"website_recharge_total"),
			"website_extraction_cash"	=>	array("name"=>"提现统计","action"=>"website_extraction_cash"),
			"website_users_total"		=>	array("name"=>"用户统计","action"=>"website_users_total"),
			"website_advance_total"		=>	array("name"=>"网站垫付统计","action"=>"website_advance_total"),
			"website_cost_total"	=>	array("name"=>"网站费用统计","action"=>"website_cost_total"),
		)
	),
	
	
	"MsgTemplate"	=>	array(
		"name"	=>	"消息模板", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"消息模板管理","action"=>"index"),
			"update"	=>	array("name"=>"保存","action"=>"update"),
			"load_tpl"	=>	array("name"=>"载入对应模板","action"=>"load_tpl"),
		)
	),
	
	
	"MailServer"	=>	array(
		"name"	=>	"邮件服务器", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"邮件服务器列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),			
			"send_demo"	=>	array("name"=>"发送测试邮件","action"=>"send_demo"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			
		)
	),
	
	"Sms"	=>	array(
		"name"	=>	"短信接口", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"短信接口列表","action"=>"index"),
			"insert"	=>	array("name"=>"安装保存","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"uninstall"	=>	array("name"=>"卸载","action"=>"uninstall"),
			"send_demo"	=>	array("name"=>"发送测试短信","action"=>"send_demo"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
		)
	),
	
	
	"PromoteMsg"	=>	array(
		"name"	=>	"推广邮件短信", 
		"node"	=>	array( 
			"add_mail"	=>	array("name"=>"添加邮件页面","action"=>"add_mail"),
			"add_sms"	=>	array("name"=>"添加短信页面","action"=>"add_sms"),
			"edit_mail"	=>	array("name"=>"编辑邮件页面","action"=>"edit_mail"),
			"edit_sms"	=>	array("name"=>"编辑短信页面","action"=>"edit_sms"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"insert_mail"	=>	array("name"=>"添加邮件执行","action"=>"insert_mail"),
			"insert_sms"	=>	array("name"=>"添加短信执行","action"=>"insert_sms"),
			"mail_index"	=>	array("name"=>"邮件列表","action"=>"mail_index"),
			"sms_index"	=>	array("name"=>"短信列表","action"=>"sms_index"),
			"update_mail"	=>	array("name"=>"编辑邮件执行","action"=>"update_mail"),
			"update_sms"	=>	array("name"=>"编辑短信执行","action"=>"update_sms"),
		)
	),
	
	
	"DealMsgList"	=>	array(
		"name"	=>	"业务群发队列", 
		"node"	=>	array( 
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"index"	=>	array("name"=>"业务队列列表","action"=>"index"),
			"send"	=>	array("name"=>"手动发送","action"=>"send"),
			"show_content"	=>	array("name"=>"显示内容","action"=>"show_content"),
		)
	),
	
	"PromoteMsgList"	=>	array(
		"name"	=>	"推广群发队列", 
		"node"	=>	array( 
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"index"	=>	array("name"=>"推广队列列表","action"=>"index"),
			"send"	=>	array("name"=>"手动发送","action"=>"send"),
			"show_content"	=>	array("name"=>"显示内容","action"=>"show_content"),
		)
	),
	
	
	"Article"	=>	array(
		"name"	=>	"文章模块", 
		"node"	=>	array( 			
			"index"	=>	array("name"=>"文章列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),			
			"delete"	=>	array("name"=>"删除","action"=>"delete"),
			"trash"	=>	array("name"=>"回收站","action"=>"trash"),	
		)
	),
	"ArticleCate"	=>	array(
		"name"	=>	"文章分类", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"文章分类列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"delete"	=>	array("name"=>"删除","action"=>"delete"),			
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"设置排序","action"=>"set_sort"),
			"trash"	=>	array("name"=>"回收站","action"=>"trash"),	
		)
	),
	
	"Nav"	=>	array(
		"name"	=>	"导航菜单", 
		"node"	=>	array( 			
			"index"	=>	array("name"=>"导航菜单列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
		)
	),
	
	"Vote"	=>	array(
		"name"	=>	"投票调查", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"投票调查列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"add_ask_row"	=>	array("name"=>"添加问题","action"=>"add_ask_row"),
			"do_vote_ask"	=>	array("name"=>"保存问卷","action"=>"do_vote_ask"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"排序","action"=>"set_sort"),
			"vote_ask"	=>	array("name"=>"编辑问卷","action"=>"vote_ask"),
			"vote_result"	=>	array("name"=>"查看结果","action"=>"vote_result"),
		)
	),
	
	"Adv"	=>	array(
		"name"	=>	"广告模块", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"广告列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"load_adv_id"	=>	array("name"=>"读取广告位","action"=>"load_adv_id"),
			"load_file"	=>	array("name"=>"读取页面","action"=>"load_file"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
		)
	),
	
	"LinkGroup"	=>	array(
		"name"	=>	"友情链接分组", 
		"node"	=>	array( 		
			"index"	=>	array("name"=>"友情链接分组列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),			
			"set_effect"	=>	array("name"=>"设置有效性","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"设置排序","action"=>"set_sort"),
			
		)
	),
	"Link"	=>	array(
		"name"	=>	"友情链接", 
		"node"	=>	array(				
			"index"	=>	array("name"=>"友情链接列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加提交","action"=>"insert"),
			"update"	=>	array("name"=>"编辑提交","action"=>"update"),						
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
			"set_effect"	=>	array("name"=>"设置有效性","action"=>"set_effect"),
			"set_sort"	=>	array("name"=>"设置排序","action"=>"set_sort"),
		)
	),
	
	
	/*"EcvType"	=>	array(
		"name"	=>	"优惠券类型管理", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"类型列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行 ","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"send"	=>	array("name"=>"优惠券发放","action"=>"send"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	
	"Ecv"	=>	array(
		"name"	=>	"优惠券管理", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"优惠券列表","action"=>"index"),
			"export_csv"	=>	array("name"=>"优惠券导出","action"=>"export_csv"),
			"foreverdelete"	=>	array("name"=>"永久删除","action"=>"foreverdelete"),
		)
	),
	*/
	
	"Conf,UserCarry,Bank,Credit"	=>	array(
		"name"	=>	"系统配置", 
		"node"	=>	array( 
			"Conf_index"	=>	array("name"=>"系统配置","module"=>"Conf","action"=>"index"),
			"Conf_update"	=>	array("name"=>"更新配置","module"=>"Conf","action"=>"update"),
			"Conf_referrals"	=>	array("name"=>"邀请返利配置","module"=>"Conf","action"=>"referrals"),
			"Conf_update_referrals"	=>	array("name"=>"更新邀请返利配置","module"=>"Conf","action"=>"update_referrals"),
			"Conf_qq"	=>	array("name"=>"QQ客服配置","module"=>"Conf","action"=>"qq"),
			"Conf_update_qq"	=>	array("name"=>"更新QQ客服配置","module"=>"Conf","action"=>"update_qq"),
			"UserCarry_config"	=>	array("name"=>"提现手续费设置","module"=>"UserCarry","action"=>"config"),
			"UserCarry_saveconfig"	=>	array("name"=>"提现手续费保存","module"=>"UserCarry","action"=>"saveconfig"),
			"Bank_index"	=>	array("name"=>"提现银行设置","module"=>"Bank","action"=>"index"),
			"Bank_insert"	=>	array("name"=>"添加提现银行执行","module"=>"Bank","action"=>"insert"),
			"Bank_update"	=>	array("name"=>"编辑提现银行执行","module"=>"Bank","action"=>"update"),
			"Bank_delete"	=>	array("name"=>"删除提现银行","module"=>"Bank","action"=>"delete"),
			"Credit_index"	=>	array("name"=>"认证类型","module"=>"Credit","action"=>"index"),
			"Credit_insert"	=>	array("name"=>"类型添加执行","module"=>"Credit","action"=>"insert"),
			"Credit_update"	=>	array("name"=>"类型编辑执行","module"=>"Credit","action"=>"update"),
		)
	),
	
	"DealCate,DealLoanType,City,DealAgency"	=>	array(
		"name"	=>	"贷款配置", 
		"node"	=>	array( 
			"DealCate_index"	=>	array("name"=>"贷款分类列表","module"=>"DealCate","action"=>"index"),
			"DealCate_insert"	=>	array("name"=>"贷款分类添加执行","module"=>"DealCate","action"=>"insert"),
			"DealCate_update"	=>	array("name"=>"贷款分类编辑执行","module"=>"DealCate","action"=>"update"),
			"DealCate_delete"	=>	array("name"=>"贷款分类删除","module"=>"DealCate","action"=>"delete"),
			"DealCate_set_effect"	=>	array("name"=>"贷款分类设置生效","module"=>"DealCate","action"=>"set_effect"),
			"DealCate_set_sort"	=>	array("name"=>"贷款分类排序","module"=>"DealCate","action"=>"set_sort"),
			"DealCate_trash"	=>	array("name"=>"贷款分类回收站","module"=>"DealCate","action"=>"trash"),	
			
			"DealLoanType_index"	=>	array("name"=>"借款类型列表","module"=>"DealLoanType","action"=>"index"),
			"DealLoanType_insert"	=>	array("name"=>"借款类型添加执行","module"=>"DealLoanType","action"=>"insert"),
			"DealLoanType_update"	=>	array("name"=>"借款类型编辑执行","module"=>"DealLoanType","action"=>"update"),
			"DealLoanType_delete"	=>	array("name"=>"借款类型删除","module"=>"DealLoanType","action"=>"delete"),
			"DealLoanType_set_effect"	=>	array("name"=>"借款类型设置生效","module"=>"DealLoanType","action"=>"set_effect"),
			"DealLoanType_set_sort"	=>	array("name"=>"借款类型排序","module"=>"DealLoanType","action"=>"set_sort"),		
			"DealLoanType_trash"	=>	array("name"=>"借款类型回收站","module"=>"DealLoanType","action"=>"trash"),	
			
			"City_index"	=>	array("name"=>"城市列表","module"=>"City","action"=>"index"),
			"City_insert"	=>	array("name"=>"城市添加执行","module"=>"City","action"=>"insert"),
			"City_update"	=>	array("name"=>"城市编辑执行","module"=>"City","action"=>"update"),
			"City_delete"	=>	array("name"=>"城市删除","module"=>"City","action"=>"delete"),
			"City_foreverdelete"	=>	array("name"=>"城市永久删除","module"=>"City","action"=>"foreverdelete"),	
			"City_set_effect"	=>	array("name"=>"城市设置生效","module"=>"City","action"=>"set_effect"),		
			"City_trash"	=>	array("name"=>"城市回收站","module"=>"City","action"=>"trash"),	
			
			"DealAgency_index"	=>	array("name"=>"机构列表","module"=>"DealAgency","action"=>"index"),
			"DealAgency_insert"	=>	array("name"=>"机构添加执行","module"=>"DealAgency","action"=>"insert"),
			"DealAgency_update"	=>	array("name"=>"机构编辑执行","module"=>"DealAgency","action"=>"update"),
			"DealAgency_delete"	=>	array("name"=>"机构删除","module"=>"DealAgency","action"=>"delete"),
			"DealAgency_foreverdelete"	=>	array("name"=>"机构永久删除","module"=>"DealAgency","action"=>"foreverdelete"),	
			"DealAgency_set_effect"	=>	array("name"=>"机构设置生效","module"=>"DealAgency","action"=>"set_effect"),			
		)
	),
	
	"Conf,Payment,ApiLogin,Integrate" =>	array(
		"name"	=>	"接口设置", 
		"node"	=>	array( 
			"Conf_money_index"	=>	array("name"=>"资金托管设置","module"=>"Conf","action"=>"money_index"),
			"Conf_update_money"	=>	array("name"=>"资金托管保存","module"=>"Conf","action"=>"update_money"),	
			
			"Payment_index"	=>	array("name"=>"支付接口列表","module"=>"Payment","action"=>"index"),
			"Payment_insert"	=>	array("name"=>"支付接口安装保存","module"=>"Payment","action"=>"insert"),
			"Payment_update"	=>	array("name"=>"支付接口编辑执行","module"=>"Payment","action"=>"update"),
			"Payment_uninstall"	=>	array("name"=>"支付接口卸载","module"=>"Payment","action"=>"uninstall"),
			
			"ApiLogin_index"	=>	array("name"=>"会员第三方登录列表","module"=>"ApiLogin","action"=>"index"),
			"ApiLogin_insert"	=>	array("name"=>"会员第三方登录插件安装","module"=>"ApiLogin","action"=>"insert"),
			"ApiLogin_update"	=>	array("name"=>"会员第三方登录插件编辑","module"=>"ApiLogin","action"=>"update"),
			"ApiLogin_uninstall"	=>	array("name"=>"会员第三方登录插件卸载","module"=>"ApiLogin","action"=>"uninstall"),
			
			"Integrate_index"	=>	array("name"=>"会员整合插件","module"=>"Integrate","action"=>"index"),
			"Integrate_install"	=>	array("name"=>"会员整合插件安装","module"=>"Integrate","action"=>"install"),
			"Integrate_save"	=>	array("name"=>"会员整合插件保存","module"=>"Integrate","action"=>"save"),
			"Integrate_uninstall"	=>	array("name"=>"会员整合插件卸载","module"=>"Integrate","action"=>"uninstall"),
		)
	),


	"Conf,MAdv"	=>	array(
		"name"	=>	"移动平台设置", 
		"node"	=>	array( 
			"Conf_mobile"	=>	array("name"=>"手机端配置","module"=>"Conf","action"=>"mobile"),
			"Conf_savemobile"	=>	array("name"=>"保存手机端配置","module"=>"Conf","action"=>"savemobile"),		
			
			"MAdv_index"	=>	array("name"=>"手机端广告列表","module"=>"MAdv","action"=>"index"),
			"MAdv_insert"	=>	array("name"=>"手机端广告添加","module"=>"MAdv","action"=>"insert"),
			"MAdv_update"	=>	array("name"=>"手机端广告编辑","module"=>"MAdv","action"=>"update"),
			"MAdv_foreverdelete"	=>	array("name"=>"删除手机端广告","module"=>"MAdv","action"=>"foreverdelete"),
		)
	),
	
	
	"Role"	=>	array(
		"name"	=>	"系统管理员", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"角色管理","action"=>"index"),
			"insert"	=>	array("name"=>"添加执行","action"=>"insert"),
			"update"	=>	array("name"=>"编辑执行","action"=>"update"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),
			"delete"	=>	array("name"=>"删除","action"=>"delete"),
		)
	),
	
	"Admin"	=>	array(
		"name"	=>	"管理员", 
		"node"	=>	array( 
			"index"	=>	array("name"=>"管理员列表","action"=>"index"),
			"insert"	=>	array("name"=>"添加","action"=>"insert"),
			"update"	=>	array("name"=>"编辑","action"=>"update"),
			"delete"	=>	array("name"=>"删除","action"=>"delete"),
			"set_default"	=>	array("name"=>"设置默认管理员","action"=>"set_default"),
			"set_effect"	=>	array("name"=>"设置生效","action"=>"set_effect"),			
		)
	),
	
	
	"Database"	=>	array(
		"name"	=>	"数据库", 
		"node"	=>	array( 
			"delete"	=>	array("name"=>"删除备份","action"=>"delete"),
			"dump"	=>	array("name"=>"备份数据","action"=>"dump"),
			"execute"	=>	array("name"=>"执行SQL语句","action"=>"execute"),
			"index"	=>	array("name"=>"数据库备份列表","action"=>"index"),
			"restore"	=>	array("name"=>"恢复备份","action"=>"restore"),
			"sql"	=>	array("name"=>"SQL操作","action"=>"sql"),
		)
	),
	
	
	"Cache"	=>	array(
		"name"	=>	"缓存处理", 
		"node"	=>	array( 
			"clear_admin"	=>	array("name"=>"清空后台缓存","action"=>"clear_admin"),
			"clear_data"	=>	array("name"=>"清空数据缓存","action"=>"clear_data"),
			"clear_image"	=>	array("name"=>"清空图片缓存","action"=>"clear_image"),
			"clear_parse_file"	=>	array("name"=>"清空脚本样式缓存","action"=>"clear_parse_file"),
			"index"	=>	array("name"=>"缓存处理","action"=>"index"),
		)
	),
	
	
	"File"	=>	array(
		"name"	=>	"文件管理", 
		"node"	=>	array( 
			"deleteImg"	=>	array("name"=>"删除图片","action"=>"deleteImg"),
			"do_upload"	=>	array("name"=>"编辑器图片上传","action"=>"do_upload"),
			"do_upload_img"	=>	array("name"=>"图片控件上传","action"=>"do_upload_img"),
		)
	),
	
	
);
?>