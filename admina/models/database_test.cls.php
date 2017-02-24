<?php
/**
 * 数据库测试操作公共类（该类只用于测试性能使用，如：测试数据生成等）
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 */
(!defined('M_COM') || !defined('M_ADMIN')) && exit('No Permission');
class cls_database_test
{
    private $db = null;

    private $fp = null;

    /**
     * 要跳转的URL
     *
     * @var string
     */
    private $url = '';

    /**
     * 配置参数
     *
     * @var array
     */
    private $config = array();

    /**
     * 要操作的参数
     *
     * @var array
     */
    private $generate = array();

    /**
     * 数据信息要存储的地址
     *
     * @var string
     */
    private static $save_path = '';

    /**
     * 当前正在操作的ID
     *
     * @var int
     **/
    private $id = 0;

    /**
     * 当前正在操作的模型ID
     *
     * @var int
     */
    private $chid = 0;

    /**
     * 当前操作的aid
     *
     * @var    array
     * @static
     * @since  1.0
     */
    private static $aids = array();

    /**
     * 当前操作的mid
     *
     * @var    array
     * @static
     * @since  1.0
     */
    private static $mids = array();

    /**
     * 一次跳转要处理的个数
     *
     * @var int
     */
    private $page_num = 200;

    /**
     * 生成会员测试数据
     *
     * @since 1.0
     */
    public function generateMembers()
    {
        if(empty($this->id)) return false;
        $mtconfigs = cls_mtconfig::Config();
        $mtcids = array(0);
        foreach($mtconfigs as $mtconfig)
        {
            $mchids = explode(',', $mtconfig['mchids']);
            if( in_array($this->chid, $mchids) )
            {
                $mtcids[$mtconfig['mtcid']] = $mtconfig['mtcid'];
            }
        }
        $datas = $this->getTableData('#__members');
        $datas_sub = $this->getTableData('#__members_sub');
        $datas_update = array(
            'mid' => $this->id,
            'mchid' => $this->chid,
            'mname' => 'test' . $this->id,
            'isfounder' => 0,
            'checked' => 1,
            'regip' => self::createIP(),
            'lastip' => self::createIP(),
            'email' => "test{$this->id}@08cms.com",
            'mspacepath' => '',
            'mtcid' => array_rand($mtcids)
        );
        if($this->id == 1) {
            $datas_update['mname'] = 'admin';
            $datas_update['isfounder'] = 1;
            $datas_update['mtcid'] = 0;
        }

        self::updateArray($datas, $datas_update);
        self::updateArray($datas_sub, array('mid' => $this->id));
        $datas_son = $this->getTableData('#__members_' . $this->chid);
        self::updateArray($datas_son, array('mid' => $this->id));
        $this->dataToFile('members.txt', $datas);
        $this->dataToFile('members_sub.txt', $datas_sub);
        $this->dataToFile("members_{$this->chid}.txt", $datas_son);
    }

    /**
     * 生成文档模型数据
     *
     * @since 1.0
     */
    public function generateArchives()
    {
        if(empty($this->id)) return false;
        // 关联栏目
        $catalogs = cls_cache::Read('catalogs');
        $caids = array();
        foreach($catalogs as $value)
        {
            $chids = explode(',', $value['chids']);
            if( in_array($this->chid, $chids) )
            {
                $caids[] = $value['caid'];
            }
        }
        if( empty($caids) )
        {
            --$this->id;
            return false;
        }
        $caid_index = array_rand($caids);
        $caid = $caids[$caid_index];

        $channels = cls_channel::Config();
        $stid = 0;
        foreach($channels as $value)
        {
            if( $value['chid'] == $this->chid )
            {
                $stid = $value['stid'];
                break;
            }
        }
        if( empty($stid) )
        {
            --$this->id;
            return false;
        }

        $datas_sub = $this->getTableData('#__archives_sub');
        self::updateArray($datas_sub, array('aid' => $this->id, 'chid' => $this->chid));
        // 文档主表
        $datas = $this->getTableData('#__archives' . $this->chid);
        self::updateArray(
            $datas,
            array(
                'aid' => $this->id,
                'chid' => $this->chid,
                'caid' => $caid,
                'checked' => 1,
                'jumpurl' => '',
                'arctpls' => '',
                'relatedaid' => ''
            )
        );
        // 子文档主表
        $datas_son = $this->getTableData('#__archives_' . $stid);
        self::updateArray($datas_son, array('aid' => $this->id));

        $this->dataToFile('archives_sub.txt', $datas_sub);
        $this->dataToFile("archives{$this->chid}.txt", $datas);
        $this->dataToFile("archives_{$stid}.txt", $datas_son);
    }

    /**
     * 生成交互模型数据
     *
     * @since 1.0
     */
    public function generateCommus()
    {
        $commu_datas = cls_cache::Read('database_test_commu');
        if( !empty($commu_datas['config']['mchannels']) )
        {
            if( empty(self::$mids[$this->chid]) )
            {
                $mchid = $commu_datas['config']['mchannels'][$this->chid];
                self::$mids[$this->chid] = $this->getTableIdsData('mid', "#__members_$mchid", "1 LIMIT 300");
            }
        }
        $commus = cls_commu::InitialInfoArray();
        $table_name = "#__{$commus[$this->chid]['tbl']}";
        $datas = $this->getTableData($table_name);
        // 过滤关联的会员模型
        if( in_array($this->chid, array(21, 27, 28, 36, 37)) ) {
            $member_info = $this->getCommuMemberInfo($commus[$this->chid]);
        } else {
            $member_info = array();
        }
        // 如果该交互模型绑定了文档模型则获取文档模型ID
        if(!empty($commus[$this->chid]['cfgs']['chids']) && !in_array($this->chid, array(21, 27, 28, 36, 37)))
        {
            foreach($commus[$this->chid]['cfgs']['chids'] as $chid)
            {
                if(empty(self::$aids[$this->chid]))
                {
                    $this->db->select('aid')->from("#__archives{$chid}")->exec();
                    while($row = $this->db->fetch())
                    {
                        self::$aids[$this->chid][$row['aid']] = $row['aid'];
                    }
                }
            }
            $aid = array_rand(self::$aids[$this->chid]);
            $member_info['chid'] = $this->chid;
        }
        else
        {
            $member_info['chid'] = $aid = 0;
        }
        $member_info['mid'] = array_rand(self::$mids[$this->chid]);
        $member_info['mname'] = 'test' . $member_info['mid'];
        #print_r($member_info); exit;

        if(!empty($commus[$this->chid]['tbl']))
        {

            $update_array = array(
                'cid' => $this->id,
                'cuid' => $this->chid,
                'checked' => 1,
                'mid' => $member_info['mid'],
                'mname' => ($member_info['mid'] == 1 ? 'admin' : $member_info['mname'])
            );
            if(isset($datas['aid'])) $update_array['aid'] = $aid;
            if(isset($datas['chid'])) $update_array['chid'] = $member_info['chid'];
            if(isset($datas['tomid'])) $update_array['tomid'] = isset($member_info['tomid']) ? $member_info['tomid'] : 1;
            if(isset($datas['tomname'])) $update_array['tomname'] = isset($member_info['tomname']) ? $member_info['tomname'] : 'admin';
            if(isset($datas['caid']))
            {
                $catalogs = cls_cache::Read('catalogs');
                $caid = array_rand($catalogs);
                $update_array['caid'] = $caid;
            }

            #var_dump($update_array);exit;
            self::updateArray( $datas, $update_array );
            $this->dataToFile("{$commus[$this->chid]['tbl']}.txt", $datas);
        }
    }

    /**
     * 根据传递的信息获取会员交互信息
     *
     * @param  array $commus      会员交互信息
     */
    public function getCommuMemberInfo( array $commus )
    {
        $member_info = array();
        if(!empty($commus['cfgs']['chids']))
        {
            $mchid_index = array_rand($commus['cfgs']['chids']);
            $mchid = $commus['cfgs']['chids'][$mchid_index];
            $mids = array();
            $this->db->select('mid')->from("#__members_{$mchid}")->exec();
            while($row = $this->db->fetch())
            {
                $mids[$row['mid']] = $row['mid'];
            }
            $member_info['tomid'] = array_rand($mids);
            #$member_info['chid'] = $this->chid;
            $member_info['tomname'] = 'test' . $member_info['tomid'];
        }
        return $member_info;
    }

    /**
     * 创建随机IP地址
     *
     * @param  bool   $return_long_ip 是否返回长整型的IP地址
     * @param  int    $num            位长
     * @return string $ip             IP地址
     *
     * @static
     * @since  1.0
     */
    public static function createIP($return_long_ip = false, $num = 4)
    {
        $ips = array();
        for($i = 0; $i < $num; ++$i)
        {
            $ips[] = mt_rand(0, 255);
        }
        $ip = implode('.', $ips);
        return $return_long_ip ? sprintf("%u", ip2long($ip)) : $ip;
    }

    /**
     * 生成跳转逻辑
     *
     * @param string $type        要生成的类型
     * @param array  $config      从控制器获取到的URI参数
     * @param array  $datas_name  当前要处理的数据个数名称
     * @param array  $chids_name  当前要处理的数据模型ID名称
     * @param string $msg         当处理完该逻辑后显示的信息
     *
     * @since 1.0
     */
    public function generateJumpLogic($type, array $config, $datas_name, $chids_name, $msg)
    {
        $chids = $config[$type][$chids_name];
        if( !isset($config['generate']) || !isset($chids) || !isset($config[$type][$datas_name]) )
        {
            return false;
        }
        $begin_id = $config['begin_id'];
        if( empty($config['current_num']) ) {
            $current_num = array_combine($chids, array_fill(0, count($chids), 0));
        } else {
            $current_num = array_combine($chids, $config['current_num']);
        }

        $datas = array_combine($chids, $config[$type][$datas_name]);
        $config[$type][$datas_name] = $datas;

        for( $this->id = $begin_id; $this->id < $this->page_num + $begin_id; ++$this->id )
        {
            if( empty($chids) )
            {
                $del_key = array_keys($config['generate'], $type);
                unset($config['generate'][$del_key[0]]);
                $this->url .= (empty($config['generate']) ? '' : '&generate=' . implode(',', $config['generate']));
                $this->id = 1;
                $this->url .= $this->setGenerateJumpURL( $config, array() );
                cls_message::show($msg, $this->url);
            }

            // 随机抽出某个模型ID，并将其下标赋给$mchid_index变量
            $chid_index = array_rand($chids);
            $this->chid = $chids[$chid_index];
            $current_num[$this->chid]++;

            // 如果该模型ID已经到达要生成的数量时
            if( $current_num[$this->chid] > $datas[$this->chid] )
            {
                unset($current_num[$this->chid], $datas[$this->chid], $chids[$chid_index]);
                unset($config[$type][$datas_name][$this->chid], $config[$type][$chids_name][$chid_index]);
                --$this->id;
                continue;
            }

            $function = 'generate' . ucfirst($type);
            $this->$function($this->id);
        }

        $this->setGenerateJumpURL( $config, $current_num );
        $this->showMsg( $type );
    }

    /**
     * 显示跳转信息
     *
     * @param string $type 显示类型
     *
     * @since 1.0
     */
    public function showMsg( $type )
    {
        $msg = '';
        switch(strtolower(trim($type)))
        {
            case 'members' :
                $msg = '会员';
            break;
            case 'archives' :
                $msg = '文档';
            break;
            case 'commu' :
                $msg = '交互';
            break;
        }
        #echo $this->url;
        cls_message::show('已经生成 ' . ($this->id - 1) . " 条{$msg}数据！", $this->url, 0);
    }

    /**
     * 按比例设置文档表里面的合辑关联
     *
     * @param int   $parent_chid 当前要处理的文档ID（父文档ID）
     * @param array $channels    合辑关系（文档与文档之间的关系，KEY为合辑项目ID，VALUE为子文档ID）
     * @param array $proportion  要生成文档与合辑关系的比例（按父文档数量算）
     *
     * @since 1.0
     */
    public function setArchiveCompilation( $parent_chid, array $channels, array $proportion )
    {
        if( empty($channels) || empty($proportion) || count($channels) != count($proportion) || empty($parent_chid) )
        {
            return false;
        }

        $parent_table_name = "#__archives{$parent_chid}";
        $parent_table_aids = $this->getTableIdsData('aid', $parent_table_name);

        // 遍历与当前文档模型有关的合辑项目
        foreach( $channels as $abrel_id => $son_chid )
        {
            // 如果比例值或来源文档模型ID为0时跳过不操作
            if( empty($proportion[$abrel_id]) || empty($son_chid) ) continue;
            $son_table_name = "#__archives{$son_chid}";
            $son_table_aids = $this->getTableIdsData('aid', $son_table_name);
            // 计算按配置的比例数量
            $parent_aids_num = floor(count($parent_table_aids) * ($proportion[$abrel_id] / 100));
            $abrel = cls_cache::Read('abrel', $abrel_id);
            // 根据比例数量获取ID值
            $parent_aids = array_rand( $parent_table_aids,
                count($parent_table_aids) > $parent_aids_num ? $parent_aids_num : count($parent_table_aids)
            );
            foreach($parent_aids as $parent_aid)
            {
                // 父模型每行数据对应200行子模型数据
                $son_aids = array_rand(
                    $son_table_aids,
                    count($son_table_aids) >= 200 ? 200 : count($son_table_aids)
                );
                foreach($son_aids as $son_aid)
                {
                    // 该处父模型归子模型
                    if(empty($abrel['tbl']))
                    {
                        $values = array($parent_aid, 1, 0);
                        // 开始处理文档与合辑的关联
                        $this->db->update(
                            $son_table_name,
                            "pid{$abrel_id},incheck{$abrel_id},inorder{$abrel_id}",
                            $values
                        )->where("aid = {$son_aid}")->exec();
                    }
                    else // 该处子模型归父模型
                    {
                        $values = array($abrel_id, $son_aid, $parent_aid, 1, 0);
                        // 开始处理文档与合辑的关联
                        $this->db->insert("#__{$abrel['tbl']}", 'arid,inid,pid,incheck,inorder', $values)->exec();
                    }
                }
                $son_table_aids = array_diff($son_table_aids, $son_aids);
            }
        }
    }

    /**
     * 设置文档表里面的会员关联
     *
     * @param array  $datas 模型ID数组，KEY是绑定的文档模型ID，VALUE是会员模型ID
     *
     * @since 1.0
     */
    public function setArchiveMembers( array $datas )
    {
        $member_datas = array();
        foreach($datas as $chid => $mchids)
        {
            $table_name = "#__archives{$chid}";

            // 取出所有会员ID与名称
            foreach($mchids as $mchid)
            {
                // 只取没有取过的会员模型
                if( empty($member_datas[$mchid]) )
                {
                    $this->db->select('mid, mname')->from("#__members")->where("mchid={$mchid} LIMIT 100")->exec();
                    while($row = $this->db->fetch())
                    {
                        $member_datas[$mchid][$row['mid']] = array('mname' => $row['mname']);
                    }
                }
            }

            $num_aids = $this->getTableIdsData('aid', $table_name);
            // 每次取10%的文档ID出来设置
            $select_num_aids = floor(count($num_aids) * 0.1);
            $num = ($select_num_aids <= 0 ? 0 : floor(count($num_aids) / $select_num_aids));
            for($i = 0; $i < $num; ++$i )
            {
                $mid_index = array_rand($member_datas[$mchid]);
                $aids = array_rand( $num_aids, $select_num_aids);
                if(!is_array($aids)) $aids = (array) $aids;
                $num_aids = array_diff($num_aids, $aids);

                // 开始处理文档与会员的关联
                $this->db->update(
                    $table_name,
                    'mid, mname',
                    array(
                        $mid_index,
                        $member_datas[$mchid][$mid_index]['mname']
                    )
                )->where('aid IN (' . implode(',', $aids) . ')')->exec();
            }
        }
    }

    /**
     * 按设置的比例去设置文档表里面的类系关联
     *
     * @param string $table_name 文档模型表名
     * @param array  $cotypes    类系数组
     *
     * @since 1.0
     */
    public function setArchiveCotypes( $table_name, array $cotypes )
    {
        $fields = $this->db->getTableColumns($table_name);
        $row_num = $this->db->getTableRowNum($table_name);
        #if( $row_num < 10000 ) cls_message::show('请先生成测试数据！', M_REFERER);
        $num_aids = $this->getTableIdsData('aid', $table_name);

        $coclass = array();
        $data_table_structure = $this->db->getTableColumns($table_name);

        // 把类系数据重置
        foreach(array_keys($cotypes) as $key)
        {
            if( !empty($fields["ccid{$key}"]) )
            {
                $this->db->update($table_name, "ccid{$key}", array(0))->exec();
            }
        }

        // 开始设置类系数据
        foreach($cotypes as $key => $cotype)
        {
            $co_max_num = floor($row_num * ($cotype / 100));
            $co_num_aids = @array_rand($num_aids, $co_max_num);
            if(empty($co_num_aids)) continue;
            $num_aids = array_diff($num_aids, $co_num_aids);

            if( empty($coclass[$cotype]) )
            {
                $coclass[$cotype] = $this->getTableIdsData('ccid', "#__coclass{$key}");
            }

            foreach($co_num_aids as $aid)
            {
                // 目前只考虑一个类系ID，如果要多个时加入array_rand的第二个参数即可。
                $ccid = array_rand($coclass[$cotype]);
                if( false !== self::checkFieldType($data_table_structure["ccid{$key}"], 'int') )
                {
                    $ccid_str = (is_array($ccid) ? implode(',', $ccid) : $ccid);
                }
                else
                {
                    $ccid_str = ',' . (is_array($ccid) ? implode(',', $ccid) : $ccid) . ',';
                }

                if( !empty($fields["ccid{$key}"]) )
                {
                    $this->db->update( $table_name, "ccid{$key}", array($ccid_str) )
                         ->where("aid = {$aid}")->exec();
                }
            }
        }
    }

    /**
     * 设置交互模型与会员模型关联
     *
     * @param array $config 关联信息，KEY为交互模型ID，VALUE为会员模型ID
     */
    public function setCommuMembers( array $config )
    {
        $commus = cls_commu::InitialInfoArray();
        $db = clone $this->db;
        foreach($config as $cid => $mchid)
        {
            if( empty(self::$mids[$cid]) )
            {
                self::$mids[$cid] = $this->getTableIdsData('mid', "#__members_$mchid", "1 LIMIT 100");
            }
            $table_name = '#__' . $commus[$cid]['tbl'];
            if(empty($table_name)) continue;
            $commus_fields = $this->db->getTableColumns($table_name);
            $this->db->select('cid')->from($table_name)->exec();
            while($row = $this->db->fetch())
            {
                $member_info = $this->getCommuMemberInfo(self::$mids[$cid]);
                $field = 'mid, mname';
                $update_data = array($member_info['mid'], $member_info['mname']);
                if(isset($commus_fields['tomid']))
                {
                    $field .= ',tomid';
                    $update_data[] = $member_info['tomid'];
                }
                if(isset($commus_fields['tomname']))
                {
                    $field .= ',tomname';
                    $update_data[] = $member_info['tomname'];
                }
                $db->update($table_name, $field, $update_data)->where(array('cid' => $row['cid']))->limit(1)->exec();
            }
        }
        unset($db);
    }

    /**
     * 获取某个表ID信息
     *
     * @param  string $id_field   ID字段名
     * @param  string $table_name 要获取的数据表名称
     * @param  string $where      获取条件
     * @return array  $rows       返回存储所有获取到的ID数组
     *
     * @since  1.0
     */
    public function getTableIdsData( $id_field, $table_name, $where ='' )
    {
        $this->db->select($id_field)->from($table_name);
        if($where)
        {
            $this->db->where($where);
        }
        $this->db->exec();
        $rows = array();
        while($row = $this->db->fetch())
        {
            $rows[$row[$id_field]] = $row[$id_field];
        }
        return $rows;
    }

    /**
     * 生成跳转URL
     *
     * @param array  $config      执行配置参数
     * @param array  $current_num 当前执行的每一项个数
     *
     * @since 1.0
     */
    public function setGenerateJumpURL( array $config, $current_num )
    {
       $this->url .= '&generate=' . implode(',', $config['generate']) .
                      '&begin_id=' . $this->id .
                      '&current_num=' . (is_array($current_num) ? implode(',', $current_num) : $current_num) .
                      $this->getGenerateURI( $config );
    }

    /**
     * 获取生成URI
     *
     * @param  array  $config 从该配置参数里获取
     * @return string $uri    获取到的URI
     *
     * @since  1.0
     */
    public function getGenerateURI( array $config )
    {
        $uri = '';
        foreach($config as $key => $value)
        {
            if( in_array($key, array('begin_id', 'current_num', 'generate')) ) continue;
            foreach($value as $k => $v)
            {
                $v && $uri .= "&$k=" . implode(',', $v);
            }
        }
        return $uri;
    }

    /**
     * 把测试数据生成到文件
     *
     * @param string $file_name 数据存储的目标文件
     * @param array  $data      要存储的数据
     *
     * @since 1.0
     */
    public function dataToFile($file_name, array $data)
    {
        global $mcharset;
        $data = implode("\t", $data) . ((false !== stripos(PHP_OS, 'WIN')) ? "\r\n" : "\n");
        $this->fp->_fopen(self::$save_path . DS . $file_name, 'a+b');
        $this->fp->_fwrite($data, mb_strlen($data, $mcharset));
    }

    /**
     * 更新数组值，给定某些字段更新数据
     *
     * @param array $array       要更新的数组
     * @param array $update_data 要更新的字段名与值，KEY是字段名称，VALUE是值
     */
    private static function updateArray(array &$array, array $update_data)
    {
        foreach($update_data as $key => $value)
        {
            isset($array[$key]) && $array[$key] = $value;
        }
    }

    /**
     * 获取表的测试数据
     *
     * @param  string $table_name 要获取的表名称
     * @return array              返回获取后的测试数据
     */
    private function getTableData($table_name)
    {
        $test_data = array();
        $fields = $this->db->getTableColumns($table_name);
        if(empty($fields) || !is_array($fields)) return array();
        foreach($fields as $field => $type)
        {
            $test_data[$field] = self::getTestData($type);
        }
        return $test_data;
    }

    /**
     * 获取测试数据
     *
     * @param  string $field_type 字段类型信息
     * @return mixed              返回与字段类型匹配的测试数据
     * @since  1.0
     */
    private static function getTestData($field_type)
    {
        $admin_md5 = 'c3284d0f94606de1fd2af172aba15bf3';
        $time = time();
        $test_string = 'testtesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttesttestesttesttest';
        $length = self::getFieldTypeLength($field_type);
        static $char = 'char';
        static $int = 'int';
        static $text = 'text';
        static $float = 'float';
        static $double = 'double';

        $checked_type = array($char, $int, $text, $float, $double);
        $checked_type = array_fill_keys($checked_type, 'false');
        foreach($checked_type as $type => $v)
        {
            $checked_type[$type] = self::checkFieldType($field_type, $type);
            if(false !== $checked_type[$type]) break;
        }
        // 生成字符型数据
        if(false !== $checked_type[$char])
        {
            if($length == 32)
            {
                $return = $admin_md5;
            }
            else
            {
                $return = substr(str_shuffle($admin_md5), 0, mt_rand(1, $length - 1));
            }
        }
        // 生成整形数据
        else if (false !== $checked_type[$int])
        {
            if($length == 1)
            {
                $return = mt_rand(0, 1);
            }
            else
            {
                $return = ($length >= 10 ? $time : substr((string)$time, 0, mt_rand(1, $length - 1)));
            }
        }
        // 生成文本型数据
        else if (false !== $checked_type[$text])
        {
            $return = substr($test_string, 0, mt_rand(1, strlen($test_string)));
        }
        // 生成浮点型数据
        else if ((false !== $checked_type[$float]) || (false !== $checked_type[$double]))
        {
            $return = 0;
        }
        // 生成空字符串
        else
        {
            $return = '';
        }
        return $return;
    }

    public function __call( $name, $arguments )
    {
        return false;
    }

    /**
     * 判断数据字段类型是否匹配
     *
     * @param  string $field_type 字段类型信息
     * @return bool               匹配返回所在位置，否则返回FALSE
     * @since  1.0
     */
    private static function checkFieldType($field_type, $type)
    {
        return stripos($field_type, $type);
    }

    /**
     * 获取字段类型长度
     *
     * @param  string $field_type 字段类型信息
     * @return int                返回字段类型长度
     * @since  1.0
     */
    private static function getFieldTypeLength($field_type)
    {
        if(empty($field_type))
        {
            return 0;
        }

        if(preg_match('/\((\d*)\)/', $field_type, $length))
        {
            return (int)$length[1];
        }

        return 255;
    }

    /**
     * 设置跳转地址
     *
     * @param string $url 要跳转的地址
     */
    public function setURL($url)
    {
        $this->url = $url;
    }

    public function __construct()
    {
        global $db;
        $this->db = $db;
        self::$save_path = M_ROOT . 'dynamic' . DS . 'test_data_cache';
        $this->fp = _08_FilesystemFile::getInstance();
        _08_FileSystemPath::checkPath(self::$save_path, true);
    }
}