针对每个模板页面，定义模板解析的扩展。

*****扩展类文件命名规则*************************
1)普通类模板解析扩展
如模板文件为xxx.html，则扩展类文件命名：xxx_html.php(模板名中的.替换为_)
解析扩展类名为tpl_xxx_html(模板名中的.替换为_)

2)JS动态标签解析扩展
如标签为 array('ename' => 'xxx','tclass' => 'yyy',……)，则扩展类文件命名：xxx_yyy_js.php
解析扩展类名为tpl_xxx_yyy_js
------------------------------------------------

*****扩展类定义规则*************************

<?PHP
defined('M_COM') || exit('No Permission');
class tpl_xxx_html extends cls_Parse{
	# 初始化扩展的样例，如果不需做这个扩展，可删除
	protected function __construct($ParseInitConfig = array()){
		parent::__construct($ParseInitConfig);
		/*
		初始化数据的扩展处理
		*/		
	}


}
--------------------------------------------

*****扩展意图*******************************
可以对单个模板解析的数据及功能进行针对性的扩展处理
$this->_da：页面主体资料，包含$_GET/$_POST内的变量，及页面主体的初始资料（如文档，节点，会员等）
self::$_mp：分页配置资料
尽量对cls_Parse已有的非静态proteced方法进行覆盖定义。
