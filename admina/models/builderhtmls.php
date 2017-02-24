<?php
/**
 * 建造HTML元素类（建造者模式操作类）
 * 该类用于将一个复杂对象的构建与它的表示分离,使用同样的构建过程可以创建不同的表示
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class _08_BuilderHtmls
{
    /**
     * 当前建造者对象句柄
     *
     * @var object
     */
    private $instance = null;

    /**
     * 要建造的配置数据
     *
     * @var array
     */
    private $config = array();

    /**
     * 建立视图表格
     *
     * @param array $config 建立参数,索引分别有：
     *                      title      string 整个表单显示的标题名称
     *                      formname   string 表单名称，不设置则默认为当前方法名称
     *                      formurl    string 表单链接，不设置则默认为当前URL
     *                      tabletitle string 表格标题名称
     *                      showdatas  array  显示数据，key为第一列数据，value为后面的列
     *                      submits    array  表单按钮，key为名称，value为值
     *
     * @since 1.0
     */
    public function buildTable( array $config )
    {
        if( empty($config['formname']) )
        {
            $config['formname'] = strtolower(__FUNCTION__);
        }

        // 把数据传递给视图
        foreach($config as $key => $value)
        {
            $this->instance->assign($key, $value);
        }

        // 指定视图模板
        return $this->instance->display('tables', '.tpl');
    }

    /**
     * 建立表单select选项
     *
     * @param array $select_config 建立参数,索引分别有：
     *                             selectname   string select选项名称
     *                             selectdatas  array  select选项显示的数据数组
     *                             selectedkey  mixed  select选项选中的值
     *                             defulatvalue mixed  select选项默认值
     *                             selectstr    string select其它属性信息
     * @param int   $type          select类型，默认0为HTML默认的select标签样式
     *
     *
     * @since 1.0
     */
    public function buildSelect( array $select_config, $type = 0 )
    {
        $select_config['type'] = $type;
        // 把数据传递给视图
        foreach($select_config as $key => $value)
        {
            $this->instance->assign($key, $value);
        }
        // 指定视图模板
        return $this->instance->display('select', '.tpl');
    }

    /**
     * 建立表单select选项
     *
     * @param array $config    TableTree数据数组
     * @param mixed $menu_list TableTree类目
     *
     * @since 1.0
     */
    public function buildTableTree( array $config, $menu_list = array() )
    {
        // 把数据传递给视图
        $this->instance->assign('tableTree', $config);
        $this->instance->assign('cms_abs', _08_CMS_ABS);
        $this->instance->assign('menu_list', $menu_list);
        // 指定视图模板
        return $this->instance->display('table_tree', '.tpl');
    }

    public function __call( $name, $arguments )
    {
        $name = 'build' . ucfirst($name);
        if( method_exists($this, $name) )
        {
            if ( $arguments )
            {
                return call_user_func_array(array($this, $name), $arguments);
            }
            else
            {
            	return call_user_func(array($this, $name));
            }
        }
    }

    public function __construct( _08_View $instance )
    {
        $this->instance = $instance;
    }
}