#�Լ��Լ����̨TAB
# v3 ע������
# ע�� �� db_config.php ��sys_config.php ��������ɾ��
git update-index --assume-unchanged public/db_config.php
git update-index --assume-unchanged public/sys_config.php
#�������ݿ��fanwe_img_list_nav    2015-4-2 09:54
/*
CREATE TABLE IF NOT EXISTS `fanwe_img_list_nav` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `name` char(100) NOT NULL,
  `url` char(200) NOT NULL,
  `nav_url` char(150) NOT NULL,
  `target` tinyint(2) NOT NULL COMMENT '0��1��',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;
INSERT INTO `fanwe_img_list_nav` (`id`, `name`, `url`, `nav_url`, `target`) VALUES
(21, '1.gif', 'app/Tpl/blue/images/1.gif', 'http://www.baidu.com', 1),
(22, '2.jpg', 'app/Tpl/blue/images/2.jpg', '#', 1),
(23, '3.jpg', 'app/Tpl/blue/images/3.jpg', '#', 1),
(24, '4.jpg', 'app/Tpl/blue/images/4.jpg', '#', 1);
*/