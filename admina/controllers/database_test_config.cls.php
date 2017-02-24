<?php
/**
 * 数据测试配置参数页面
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
class cls_database_test_config extends cls_AdminHeader
{
    /**
     * 文档模型数据信息
     *
     * @var array
     */
    private $channels = array();

    /**
     * 文档测试的配置缓存信息
     *
     * @var array
     */
    private $arccache = array();

    /**
     * 交互测试的配置缓存信息
     *
     * @var array
     */
    private $commucache = array();

    /**
     * 数据测试公共类句柄
     *
     * @var object
     */
    private $test = null;

    /**
     * 当前操作的模型ID
     *
     * @var int
     */
    private $chid = 0;

    /**
     * 当前操作的配置数组
     *
     * @var array
     */
    private $config = array();

    public function __construct()
    {
        @set_time_limit(0);
        // 定义当前使用权限
        parent::__construct('database');
        // 文档模型
        $this->channels = cls_channel::InitialInfoArray();
        $this->arccache = cls_cache::Read('database_test_arc');
        $this->commucache = cls_cache::Read('database_test_commu');
        isset($this->_params['chid']) && $this->chid = intval($this->_params['chid']);
        if( submitcheck('saveconfig') )
        {
            $this->saveConfig();
            exit;
        }
        if( submitcheck('savecommuconfig') )
        {
            $this->saveCommuConfig();
            exit;
        }

        foreach(array('members', 'coclasses', 'compilation', 'commumembers') as $value)
        {
            if( submitcheck($value) )
            {
                $this->test = new cls_database_test();
                $function = 'action' . ucfirst($value);
                method_exists($this, $function) && $this->$function();
                exit;
            }
        }
    }

    /**
     * 构造配置会员模型界面
     */
    public function configArc()
    {
        // 会员模型
        $members = $this->getMchannels();

        $mchannels = isset($this->arccache['config']['mchannels']) ? $this->arccache['config']['mchannels'] : array();

        $this->config['showdatas'] = array();
        foreach($this->channels as $channel)
        {
            // 不显示非系统内置的未启用模型
            if( !$channel['cname'] || !$channel['available'] || (isset($channel['issystem']) && !$channel['issystem']) )
            {
                continue;
            }

            $member_str = makeselect(
                "mchannels[{$channel['chid']}][]",
                makeoption($members, isset($mchannels[$channel['chid']]) ? $mchannels[$channel['chid']] : 0),
                'multiple="multiple" class="w100"'
            );

            $this->config['showdatas'][$channel['chid']] = array(
                $channel['cname'],
                $member_str,
                '<a href="?entry=database_test_config&action=configcoclasses&chid=' . $channel['chid'] . '" onclick="return floatwin(\'open_generate_config\', this);" style="color:#134D9D">配置类系</a>',
                '<a href="?entry=database_test_config&action=configcompilation&chid=' . $channel['chid'] . '" onclick="return floatwin(\'open_generate_config\', this);" style="color:#134D9D">配置合辑</a>'
            );
        }

        // 获取数据
        $this->config['title'] = '文档模型测试数据配置（注：如果数据比较多可能时间会比较长）';
        $this->config['tabletitle'] = array('模型ID', '模型名称', '关联会员模型', '关联类系', '关联合辑');
        $this->config['submits'] = array('saveconfig' => '保存配置', 'members' => '开始关联会员模型');

        $this->_build->table( $this->config );
    }

    /**
     * 开始关联会员模型
     */
    public function actionMembers()
    {
        /**
         * 配置文档关联的会员模型信息
         */
        if( isset($this->_params['mchannels']) )
        {
            $this->test->setArchiveMembers($this->_params['mchannels']);
        }
        cls_message::show('操作完成！', axaction(2));
    }

    /**
     * 构造配置合辑界面
     */
    public function configCompilation()
    {
        $abrels = cls_cache::Read('abrels');
        if(empty($abrels) || !is_array($abrels)) cls_message::show('没有合辑项目或未更新系统缓存！', M_REFERER);

        $this->config['title'] = '关联文档模型测试数据与合辑配置（注：如果数据比较多可能时间会比较长）';
        $this->config['tabletitle'] = array('合辑ID', '合辑名称', '来源文档模型', '关联比例(单位：%)');
        $this->config['submits'] = array('saveconfig' => '保存配置', 'compilation' => '开始关联合辑');
        // 获取配置参数
        if(isset($this->arccache['config']['compilation']))
        {
            if(isset($this->arccache['config']['compilation']['proportion']))
            {
                $proportion = $this->arccache['config']['compilation']['proportion'];
            }
            else
            {
                $proportion = array();
            }

            if(isset($this->arccache['config']['compilation']['channels']))
            {
                $channelses = $this->arccache['config']['compilation']['channels'];
            }
            else
            {
                $channelses = array();
            }
        }

        $channels = array('0' => '请选择来源文档模型');
        foreach( $this->channels as $channel)
        {
            // 不显示非系统内置的未启用模型
            if( !$channel['cname'] || !$channel['available'] || (isset($channel['issystem']) && !$channel['issystem']) || $this->chid == $channel['chid'] )
            {
                continue;
            }
            $channels[$channel['chid']] = $channel['cname'];
        }

        foreach($abrels as $abrel)
        {
            if($abrel['available'])
            {
                if( isset($proportion[$this->chid][$abrel['arid']]) )
                {
                    $default_value = (int)$proportion[$this->chid][$abrel['arid']];
                }
                else
                {
                    $default_value = 100;
                }
                if( isset($channelses[$this->chid][$abrel['arid']]) )
                {
                    $channel = $channelses[$this->chid][$abrel['arid']];
                }
                else
                {
                    $channel = 0;
                }

                $input = '<input type="text" name="proportion['.$abrel['arid'].']" class="w80" value="'. $default_value. '" />';
                $this->config['showdatas'][$abrel['arid']] = array(
                    $abrel['cname'],
                    makeselect( "channels[{$abrel['arid']}]", makeoption($channels, $channel) ),
                    $input
                );
            }
        }

        $this->_build->table( $this->config );
    }

    /**
     * 开始关联合辑模型
     */
    public function actionCompilation()
    {
        /**
         * 配置文档关联的合辑模型信息
         */
        if( !empty($this->_params['proportion']) )
        {
            $this->test->setArchiveCompilation(
                $this->chid,
                $this->_params['channels'],
                $this->_params['proportion']
            );
        }
        cls_message::show('操作完成！', axaction(2));
    }

    /**
     * 构造配置类系界面
     */
    public function configCoclasses()
    {
        if( empty($this->chid) ) cls_message::show('请指定正确的参数！', axaction(2));
        isset($this->channels[$this->chid]['stid']) && $stid = $this->channels[$this->chid]['stid'];
        if( empty($stid) ) cls_message::show('该模型没有关联任何类系！', axaction(2));
        $cotypes = cls_cache::Read('cotypes');
        $splitbls = cls_cache::Read('splitbls');
        $arccache = $this->arccache['config']['co_proportion'];

        // 获取界面显示数据
        foreach($cotypes as $k => $cotype)
        {
            if( isset($splitbls[$stid]['coids']) && in_array($k, $splitbls[$stid]['coids']) )
            {
                // 如果该值未定义或是小于或等于0时就自动设为0
                if( !isset($arccache[$this->chid][$k]) || ((int)$arccache[$this->chid][$k] <= 0) )
                {
                    $default_value = 0;
                }
                // 否则如果该值大于或等于100时就自动调为100
                else if( (int)$arccache[$this->chid][$k] >= 100 )
                {
                    $default_value = 100;
                }
                else
                {
                    $default_value = (int) $arccache[$this->chid][$k];
                }
                $input = '<input type="text" name="co_proportion['.$k.']" class="w80" value="'. $default_value. '" />';

                $this->config['showdatas'][$k] = array($cotype['cname'], $input);
            }
        }

        // 获取数据
        $this->config['title'] = '关联文档模型测试数据与类系配置（注：如果数据比较多可能时间会比较长）';
        $this->config['tabletitle'] = array('类系ID', '类系名称', '生成比例(单位：%)');
        $this->config['submits'] = array('saveconfig' => '保存配置', 'coclasses' => '开始关联类系');

        $this->_build->table( $this->config );
    }

    /**
     * 开始关联类系
     */
    public function actionCoclasses()
    {
        /**
         * 配置文档关联类系
         */
        if( isset($this->_params['co_proportion']) )
        {
            $this->test->setArchiveCotypes("#__archives{$this->chid}", $this->_params['co_proportion']);
        }
        cls_message::show('操作完成！', axaction(2));
    }

    /**
     * 获取所有会员模型
     *
     * @return array $members 返回所有会员模型ID和名称
     * @static
     */
    public function getMchannels()
    {
        // 会员模型
        $mchannels = cls_mchannel::InitialInfoArray();
        $members = array();
        foreach($mchannels as $mchannel)
        {
            $members[$mchannel['mchid']] = $mchannel['cname'];
        }
        return $members;
    }

    /**
     * 构造交互配置界面
     */
    public function configCommu()
    {
        $commus = cls_commu::InitialInfoArray();
        if( empty($commus) ) cls_message::show('没有交互模型！', axaction(2));

        $mchannels = isset($this->commucache['config']['mchannels']) ? $this->commucache['config']['mchannels'] : array();
        $members = $this->getMchannels();
        $chinnels = $this->getChannels();

        foreach($commus as $commu)
        {
            if( empty($commu['available']) ) continue;
            $member_str = $this->_build->select(
                array(
                    'selectname' => "mchannels[{$commu['cuid']}]",
                    'selectdatas' => $members,
                    'selectedkey' => (isset($mchannels[$commu['cuid']]) ? $mchannels[$commu['cuid']] : 0)
                )
            );
//            if(!empty($commu['cfgs']['chids']))
//            {
//                $chinnel_str = $this->_build->select(
//                    array(
//                        'selectname' => "channels[{$commu['cuid']}][]",
//                        'selectdatas' => $chinnels,
//                        'selectedkey' => isset($channels[$commu['cfgs']['chids']]) ? $channels[$commu['cfgs']['chids']] : 0
//                    )
//                );
//            }
//            else
//            {
//                $chinnel_str = '';
//            }

            $this->config['showdatas'][$commu['cuid']] = array(
                $commu['cname'],
                $member_str,
          //      $chinnel_str
            );
        }
        // 获取数据
        $this->config['title'] = '关联交互模型测试数据配置（注：如果数据比较多可能时间会比较长）';
        $this->config['tabletitle'] = array('模型ID', '模型名称', '关联会员模型'/*, '关联文档模型'*/);
        $this->config['submits'] = array('savecommuconfig' => '保存配置', 'commumembers' => '开始关联模型');

        $this->_build->table( $this->config );
    }

    /**
     * 执行交互模型与会员模型关联
     */
    public function actionCommuMembers()
    {
        if( isset($this->_params['mchannels']) )
        {
            $this->test->setCommuMembers($this->_params['mchannels']);
        }
        cls_message::show('操作完成！', axaction(2));
    }

    /**
     * 保存交互配置
     */
    public function saveCommuConfig()
    {
        if( isset($this->_params['mchannels']) )
        {
            $this->_params['mchannels'] = array_map('intval', $this->_params['mchannels']);
            $this->commucache['config']['mchannels'] = $this->_params['mchannels'];
        }
        cls_CacheFile::Save($this->commucache, 'database_test_commu');
        cls_message::show('操作完成！', M_REFERER);
    }

    /**
     * 获取文档模型ID与名称
     *
     * @return array $channels 返回文档模型数据数组
     */
    public function getChannels()
    {
        $channels = array();
        foreach($this->channels as $channel)
        {
            // 不显示非系统内置的未启用模型
            if( !$channel['cname'] || !$channel['available'] || (isset($channel['issystem']) && !$channel['issystem']) )
            {
                continue;
            }
            $channels[$channel['chid']] = $channel['cname'];
        }
        return $channels;
    }

    /**
     * 保存配置参数
     */
    public function saveConfig()
    {
        /**
         * 配置文档关联的合辑信息
         */
        if( isset($this->_params['proportion']) && isset($this->_params['channels']) )
        {
            if( is_array($this->_params['proportion']) )
            {
                // 让比例保持在0%-100%之间
                foreach($this->_params['proportion'] as &$proportion)
                {
                    if($proportion < 0) {
                        $proportion = 0;
                    } else if($proportion > 100) {
                        $proportion = 100;
                    } else {
                        $proportion = (int) $proportion;
                    }
                }
            }
            $this->_params['channels'] = array_map('intval', array_unique($this->_params['channels']));
            $this->arccache['config']['compilation']['proportion'][$this->chid] = $this->_params['proportion'];
            $this->arccache['config']['compilation']['channels'][$this->chid] = $this->_params['channels'];
        }

        /**
         * 配置文档关联的会员模型信息
         */
        if( isset($this->_params['mchannels']) )
        {
            $this->arccache['config']['mchannels'] = $this->_params['mchannels'];
        }

        /**
         * 保存关联类系配置
         */
        if( isset($this->_params['co_proportion']) )
        {
            if( empty($this->chid) ) cls_message::show('请指定正确的参数！', axaction(2));

            $this->_params['co_proportion'] = array_map('intval', $this->_params['co_proportion']);
            $sum = array_sum($this->_params['co_proportion']);

            #if( $sum < 0 || $sum > 100 ) cls_message::show('所有比例之和只能是0-100%以内。', M_REFERER);
            $this->arccache['config']['co_proportion'][$this->chid] = $this->_params['co_proportion'];
        }
        cls_CacheFile::Save($this->arccache, 'database_test_arc');
        cls_message::show('操作完成！', M_REFERER);
    }
}