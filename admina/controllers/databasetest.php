<?php
/**
 * 数据库测试界面构造脚本
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
class _08_databaseTest extends cls_AdminHeader
{
    public function __construct()
    {
        @set_time_limit(0);
        parent::__construct('database');
        backnav('data','database_test');
        if( submitcheck('save_config') )
        {
            $this->saveConfig();
            exit;
        }
        // 提交生成测试数据
        if( submitcheck('generate_submit') || isset($this->_params['generate']) )
        {
            $this->generateSubmit();
            exit;
        }
    }

    public function init()
    {

        tabheader(
            '生成测试数据'.'<!--<input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)" checked="checked">全选&nbsp;&nbsp;&nbsp;&nbsp;-->，开始ID<input type="text" name="begin_id" value="1" class="w50" /> >> <a href="?entry='. $this->_params['entry'] . '&action=clean_all_file">清空重新生成</a>',
            'generate',
            "?entry={$this->_params['entry']}&action={$this->_params['action']}"
        );
        // 会员模型
        $mchannels = cls_mchannel::InitialInfoArray();
        $this->_view->assign('member_model', '会员模型');
        $this->_view->assign('member_str', self::getShowString('member', $mchannels));

        // 文档模型
        $channels = cls_channel::InitialInfoArray();
        // 不显示未启用的模型
        foreach($channels as $k => $channel)
        {
            if(empty($channel['available']))
            {
                unset($channels[$k]);
            }
        }
        $this->_view->assign('arc_model', '文档模型 （<a href="?entry=database_test_config&action=configarc" onclick="return floatwin(\'open_generate_config\', this);" style="font-weight:bold; color:#134D9D">配置</a>）');
        $this->_view->assign('arc_str', self::getShowString('arc', $channels));

        // 交互模型
        $commus = cls_commu::InitialInfoArray();
        $this->_view->assign('commu_model', '交互模型（<a href="?entry=database_test_config&action=configcommu" onclick="return floatwin(\'open_generate_config\', this);" style="font-weight:bold; color:#134D9D">配置</a>）');
        $this->_view->assign('commu_str', self::getShowString('commu', $commus));

        $this->_view->display('databasetest.tpl');
        echo <<<EOT
        </table><br />
        <input class="btn" type="submit" name="generate_submit" value="开始生成">&nbsp;&nbsp;&nbsp;&nbsp;
        <input class="btn" type="submit" name="save_config" value="保存配置">
        </form>
EOT;
        a_guide('databasetest');
    }

    /**
     * 提交跳转操作
     */
    public function generateSubmit()
    {
        if( !empty($this->_params['generate']) )
        {
            if( !is_array($this->_params['generate']) )
            {
                $this->_params['generate'] = array_flip(explode(',', $this->_params['generate']));
            }
            $test = new cls_database_test();
            $test->setURL("?entry={$this->_params['entry']}&action={$this->_params['action']}");

            /* 配置操作生成会员测试数据参数 */
            if( isset($this->_params['generate']) )
            {
                $config = array(
                    'begin_id' => (isset($this->_params['begin_id']) ? (int)$this->_params['begin_id'] : 1),
                    'current_num' => (!empty($this->_params['current_num']) ? explode(',', $this->_params['current_num']) : array()),
                    'generate' => array_keys($this->_params['generate'])
                );
                $this->getURIConfig( 'members', 'member', 'mchids', $config );
                $this->getURIConfig( 'archives', 'arc', 'chids', $config );
                $this->getURIConfig( 'commus', 'commu', 'cuids', $config );

                if( isset($this->_params['generate']['members']) )
                {
                   $test->generateJumpLogic(
                        'members',
                        $config,
                        'member',
                        'mchids',
                        '会员数据生成完成！'
                    );
                }

                if( isset($this->_params['generate']['archives']) )
                {
                   $test->generateJumpLogic(
                        'archives',
                        $config,
                        'arc',
                        'chids',
                        '文档数据生成完成！'
                    );
                }

                if( isset($this->_params['generate']['commus']) )
                {
                    $test->generateJumpLogic(
                        'commus',
                        $config,
                        'commu',
                        'cuids',
                        '交互数据生成完成！'
                    );
                }
            }
        }
        cls_message::show('所有操作已经完成！', "?entry={$this->_params['entry']}");
    }

    /**
     * 获取URI配置参数
     *
     * @param string $type      获取类型
     * @param string $data_name 类型下标名称
     * @param string $chid_name 类型模型ID名称
     * @param array  $config    配置参数存储区
     *
     * @since 1.0
     */
    public function getURIConfig( $type, $data_name, $chid_name, &$config )
    {
        if( !isset($this->_params['generate'][$type]) ) return false;
        // 处理模型ID
        if( isset($this->_params[$chid_name]) )
        {
            $config[$type][$chid_name] = explode(',', $this->_params[$chid_name]);
        }
        else
        {
            $config[$type][$chid_name] = array_keys($this->_params[$data_name]);
        }
        // 获取请求生成模型数据数量
        if( isset($this->_params[$data_name]) )
        {
            if( is_array($this->_params[$data_name]) )
            {
                $config[$type][$data_name] = $this->_params[$data_name];
            }
            else
            {
                $config[$type][$data_name] = explode(',', $this->_params[$data_name]);
            }
        }
    }

    /**
     * 保存配置
     */
    public function saveConfig()
    {
        $arccache = cls_cache::Read('database_test_arc');
        $membercache = cls_cache::Read('database_test_member');
        $commucache = cls_cache::Read('database_test_commu');

        $members = @$this->_params['member'];
        $archives = @$this->_params['arc'];
        $commus = @$this->_params['commu'];

        $arccache['archives'] = $archives;
        $membercache['members'] = $members;
        $commucache['commus'] = $commus;

        cls_CacheFile::Save($membercache, 'database_test_member');
        cls_CacheFile::Save($arccache, 'database_test_arc');
        cls_CacheFile::Save($commucache, 'database_test_commu');
        cls_message::show('保存成功！', "?entry={$this->_params['entry']}");
    }

    /**
     * 获取小模块显示样式字符串
     *
     * @param  string $name  INPUT的名称
     * @param  array  $value INPUT要操作的数据
     *
     * @return string        返回样式字符串
     * @since  1.0
     */
    public static function getShowString($name, array $array)
    {
        $string = '';
        foreach($array as $value)
        {
            switch($name)
            {
                case 'member' :
                    $g_member = cls_cache::Read('database_test_member');
                    $index = 'mchid';
                    $val = isset($g_member['members'][$value[$index]]) ? $g_member['members'][$value[$index]] : 0;
                break;
                case 'arc' :
                    $g_arc = cls_cache::Read('database_test_arc');
                    $index = 'chid';
                    $val = isset($g_arc['archives'][$value['chid']]) ? $g_arc['archives'][$value['chid']] : 0;
                break;
                case 'commu' :
                    $g_commu = cls_cache::Read('database_test_commu');
                    $index = 'cuid';
                    $val = isset($g_commu['commus'][$value['cuid']]) ? $g_commu['commus'][$value['cuid']] : 0;
                break;
            }

            if( $value['cname'] && ($value['available'] || (isset($value['issystem']) && $value['issystem'])) )
            {
                $string .= <<<EOT
                    <div style="width:210px; float:left;">
                        <span style="font-weight: bold;">{$value['cname']}</span>数量：
                        <input type="text" name="{$name}[{$value[$index]}]" value="{$val}" class="w70" style="margin-right:20px;" />
                    </div>
EOT;
            }
        }

        return $string;
    }

    /**
     * 清除该目录下所有测试数据文件
     *
     * @since 1.0
     */
    public function clean_all_file()
    {
        $file = _08_FilesystemFile::getInstance();
        $path = M_ROOT . 'dynamic' . DS . 'test_data_cache';
        _08_FileSystemPath::checkPath($path, true);
        $file->cleanPathFile($path, 'txt');
        cls_message::show('清除完成！', M_REFERER);
    }
}