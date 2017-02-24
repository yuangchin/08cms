<?php
/**
 * 数据库查询类
 * 写该类为了大部分查询需求方便使用，如果该类无法满足需求可直接使用$db->query之类的原生方法查询，更多说明请看{@see mysql.cls.php} 文件。
 * 
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2012 08CMS, Inc. All rights reserved.
 * 
 * @example 以下范例均测试通过
    $row = $db->select('ms.*,m.*')->from('#__msession ms, #__members m')
              ->where('ms.mid = m.mid')
              ->_and(array('ms.msid'=> 'dzIB3C'))
              ->_and(array('m.password'=>'c3284d0f94606de1fd2af172aba15bf3'))
              ->_and(array('m.checked'=>1))
              ->exec()->fetch();
    var_dump($row);
    
    $row = $db->select('m.*,s.*')->from('#__members m')
              ->innerJoin('#__members_sub s')->_on('s.mid=m.mid')
              ->where(array('mname' => 'admin'))
              ->exec()->fetch();
    var_dump($row);
    
    $row = $db->select()->from('#__members m')
              ->where('m.mid')->_in('1')
              ->limit(1)
              ->exec()->fetch();
    var_dump($row);
    
    $db->select()->from('#__members AS m')
       ->where('m.mid')
       ->_in('1, 2, 6, 7')
       ->exec();
    while($row = $db->fetch())
    {
        var_dump($row);
    }
    
    $row = $db->select()->from('#__members m')
              ->where('m.mid = 1')
              ->_and("checked=1")
              ->having('COUNT(*) > 0')
              ->exec()->fetch();
    var_dump($row);
    
    $row = $db->select()->from('#__members AS m')
              ->leftJoin('#__members_1 AS m1')->_on('m.mid=m1.mid')
              ->where('m.mid = 1')
              ->exec()->fetch();
    var_dump($row);
    
    $row = $db->select('COUNT(*)')->from('#__archives16')
              ->where(array('chid'=>3))
              ->_and(array('createdate'=>'1370270040'))
              ->exec()->fetch();
    var_dump($row);
    
    $row = $db->getTableList();
    var_dump($row);
    
    $db->getTableList(true)->like('cms_', '_%')->exec();
    while($row = $db->fetch())
    {
        var_dump($row);
    }
    
    $value = 'admin@admin.com'; $opmode = 'edit'; $mid = 2;
    $db->select('mid')->from('#__members')->where(array('email' => $value));
    if( $mid && ($opmode == 'edit') )
    {
    	$db->_and("mid != {$mid}");
    }
    $uid = $db->exec()->fetch();
    var_dump($uid);
    
    $userInfo['username'] = 'admin';
    $row = $db->select('mid, password')->from('#__members')
              ->where(array('mname' => $userInfo['username']))
              ->_and('checked = 1')
              ->exec()->fetch();
    var_dump($row);
    
    $db->insert( '#__pms', 
        array(
            'fromuser' => 'test', 
            'fromid' => 1, 
            'toid' => 12, 
            'title' => 'te"st', 
            'content' => 'test', 
            'pmdate' => time()
        )
    )->exec();
    
    $db->insert( '#__pms', 'fromuser, fromid, toid, title, content, pmdate',
        array(
            array('test', 1, 12, 'te"dddddst', 'test', time()),
            array('test', 1, 12, 'te"dddddst_', 'test', time())
        )
    )->exec();
    
    $db->insert( '#__pms', 'fromuser, fromid, toid, title, content, pmdate',
        array('test', 1, 12, 'te"ddddds__t', 'test', time())
    )->exec();
    
    $db->delete('#__pms')->where('pmid = 9')->exec();
    
    $db->update('#__pms', array('title' => 'sdd', 'viewed' => 1))->where('pmid = 14')->exec();
    $db->update('#__pms', 'title, viewed', array('sss_st"t_dddd', 0))->where('pmid = 14')->exec();
    $db->update('#__pms', 'title')->where('pmid = 14')->exec(); # 设置title字段为空值
    
    
   （循环）：
    $db->select('fc.title, fc.fcaid')->from('#__fcatalogs AS fc')->exec();
    while($row = $db->fetch()) {
        echo "<div>{$row['fcaid']}---------{$row['title']}</div>";
    }
     
    （循环嵌套查询）：
    $query = $db->select('mid')->from("#__members")->limit(3)->getQuery();
    while($row = $db->fetch($query))
    {
        $row2 = $db->select()->from('#__archives1')->where(array('mid' => $row['mid']))->limit(1)->exec()->fetch();
        var_dump($row, $row2);
    }
 */

class _08_MysqlQuery
{
    /**
     * 操作的SQL语句
     *
     * @var   string
     * @since nv50
     */
    protected $_sql = '';
    
    /**
     * 操作的数据表名称
     *
     * @var   string
     * @since nv50
     */
    protected $_tableName = '';

    /**
     * query请求后的返回值
     *
     * @var   resource
     * @since nv50
     */
    private $query = null;

    /**
     * 操作类型
     *
     * @var   string
     * @since nv50
     */
    protected $_type = '';

    /**
     * 是否使用了LIKE操作
     *
     * @var   bool
     * @since nv50
     */
    private $like = false;

    /**
     * 数据表前缀
     *
     * @var   string
     * @since nv50
     */
    protected $_tblprefix = 'cms_';
    
    private $dervers = 'MySQL';
    
    private $debug = false;
    
    private $db = null;

    /**
     * 查询一条数据
     *
     * @param string $fields 要查询的数据字段
     * @param bool   $use_this_table 使用$this->_tableName作表名，使用则传递TRUE，否则FALSE
     * @since nv50
     */
    public function select( $fields = '*', $use_this_table = false )
    {		
        $fields = str_replace(array("\r", "\n"), '', $fields);
        $fields = $this->quoteName( $fields );
        $this->_sql .= "SELECT {$fields} ";
        
        if ( $use_this_table && $this->_tableName )
        {
            $this->from($this->_tableName);
        }
        
        return $this;
    }

    /**
     * 插入一条数据到数据表
     *
     * @param string $table_name 要插入的数据表名称
     * @param mixed  $fields     如果该值为字符串时，只能是字段名称（即要传递第三个参数作为值）
     *                           如果为数组时，要插入的字段数据，KEY为字段名，VALUE为值（即第三个参数不用传递）
     * @param array  $values     要插入的字段数据值（当第二个参数$fields为字符串时必须要传递该参数，并且长度要与第二个参数相同）
     *
     * @since nv50
     */
    public function insert( $table_name, $fields, $values = array(), $replace = false )
    {
        if ( is_array($fields) )
        {
            # 注意array_keys与array_values顺序必须一致
            $field_name = $this->filterField( array_keys($fields) );            
            $values = $this->filterInsertValue( array_values($fields) );
        }
        else if ( is_string($fields) )
        {
            $field_name = $this->filterField( $fields );
            $values = (array) $values;
            
            /**
             * 让该方法支持这种调用（即：一次性插入多行数据，注：只有第二个参数为字符串时才支持）：
             * $db->insert( '#__pms', 'fromuser, fromid, toid, title, content, pmdate',
                    array(
                        array('test', 1, 12, 'te"dddddst', 'test', time()),
                        array('test', 1, 12, 'te"dddddst_', 'test', time())
                    )
               )->exec();
             */
            if ( isset($values[0]) && is_array($values[0]) )
            {
                $values = $this->filterInsertValue($values, false);
            }
            else
            {
            	$values = $this->filterInsertValue($values);
            }
        }
        else
        {
            return false;
        }
		
        if( !empty($field_name) && is_string($values) )
        {
            $table_name = $this->quoteName($table_name); 
            if ($replace)
            {
                $action = 'REPLACE';
            }
            else
            {
            	$action = 'INSERT';
            }
            $this->_sql = "$action INTO {$table_name} ({$field_name}) VALUES {$values} ";
        }

        return $this;
    }
    
    /**
     * 替换插入一条数据到数据表
     *
     * @param string $table_name 要插入的数据表名称
     * @param mixed  $fields     如果该值为字符串时，只能是字段名称（即要传递第三个参数作为值）
     *                           如果为数组时，要插入的字段数据，KEY为字段名，VALUE为值（即第三个参数不用传递）
     * @param array  $values     要插入的字段数据值（当第二个参数$fields为字符串时必须要传递该参数，并且长度要与第二个参数相同）
     *
     * @since nv50
     */
    public function replace( $table_name, $fields, $values = array() )
    {
        return $this->insert($table_name, $fields, $values, true);
    }
    
    /**
     * 创建一条数据到数据表中
     * 该方法能使用在 parent::getModels() 获取后的数据表对象里，在MVC架构中代替{@see self::insert()} 方法的使用
     * 
     * @param  array $insert_values 要插入的值，KEY为字段名称，VALUE为值
     * @param  bool  $replace       是否用replace into方式插入，true为是，false为用insert into
     * @return mixed                如果插入成功返回当前对象句柄，否则返回FALSE
     * 
     * @since  nv50
     */
    public function create( array $insert_values, $replace = false )
    {
        $field_name = $this->filterField( array_keys($insert_values) );            
        $values = $this->filterInsertValue( array_values($insert_values) );
        
        if( !empty($field_name) && is_string($values) )
        {
            if ( $replace )
            {
                $method = 'REPLACE';
            }
            else
            {
            	$method = 'INSERT';
            }
            $table_name = $this->quoteName($this->_tableName);
            $this->_sql = "$method INTO {$table_name} ({$field_name}) VALUES {$values} ";
            return $this->exec();
        }
        
        return false;
    }

    /**
     * 读取数据，注：该方法如果多个表查询条件一样可以很方便的查询
     * 以下范例用到的 parent 为类：_08_Application_Base，而模型名称为目录：/include/application/models/ 下的表名模型类后缀
     *
     * @example $members = parent::getModels('Members');
     *          $members2 = parent::getModels('Members2');
     *          $members3 = parent::getModels('Members3');
     *          $row = $members->where(array('mid' => 2))->read('*', false);
     *          $row2 = $members2->read('*', false);
     *          $row3 = $members3->read();  // 这样一下就能读取当mid为2的三个表所有的数据，减少多句写同一个where的麻烦
     *          
     * @param   string $fields   要读取的字段名称，多个以英文逗号分隔
     * @param   bool   $clearSQL 是否查询后清除上一个SQL信息, TRUE为清除，FALSE为不清除让下一个操作使用
     * @return  array  $row      读取后的字段数据信息
     * @since   nv50
     **/
    public function read( $fields = '*', $clearSQL = true )
    {
        $fields = $this->quoteName( $fields );
        $table_name = $this->quoteName($this->_tableName);
        $clearSQL || $sql = $this->_sql;
        $this->_sql = "SELECT {$fields} FROM {$table_name} " . $this->_sql;
        $this->limit(1)->exec($clearSQL);
        $row = $this->fetch();
        $clearSQL || $this->_sql = $sql;

        return $row;
    }

    /**
     * 更新一条数据
     *
     * @param string $table_name 要更新的数据表名称
     * @param mixed  $fields     如果该值为字符串时，只能是字段名称（即要传递第三个参数作为值）
     *                           如果为数组时，要插入的字段数据，KEY为字段名，VALUE为值（即第三个参数不用传递）
     * @param array  $values     要插入的字段数据值（当第二个参数$fields为字符串时要传递该参数，并且长度要与第二个参数相同，
     *                           如果要把字段清空只传递一个array('')即可）
     *
     * @since nv50
     */
    public function update( $table_name, $fields = '', $values = array() )
    {
        /**
         * 更改一种新方式调用，暂时还支持老方式
         * @example $table = parent::getModels('UserFiles_Table');  // 注：目前当前调用方法只支持MVC架构里调用，
         *                                                          // UserFiles_Table是 /include/application/models/
         *                                                          // 目录下的以 table.php 后缀的文件里的类名后缀，
         *                                                          // 即该类名对应的是：userfiles_table.php 文件里的类名后缀
         *          $table->where(array('aid' => 1))->update(array('title' => 'test'));
         */
        if ( is_array($table_name) && $this->_tableName )
        {
            $fields = $table_name;
            $table_name = $this->_tableName;
        }
        
        if ( is_array($fields) )
        {
            # 注意array_keys与array_values顺序必须一致
            $field_name = $this->filterField( array_keys($fields) );
            $sql = $this->filterUpdateValue($field_name, array_values($fields));
        }
        else if ( is_string($fields) )
        {
            if ( empty($values) )
            {
                $sql = $field_name = $this->quoteName($fields);
            }
            else
            {
                $field_name = $this->filterField( $fields );
            	$sql = $this->filterUpdateValue( $field_name, (array) $values );
            }
        }
        else
        {
            return false;
        }
        
        if( !empty($field_name) && isset($sql) )
        {
            $table_name = $this->quoteName($table_name);            
        
            if ( $this->_tableName )
            {
                $whrsql = $this->_sql;
				$this->_sql = "UPDATE {$table_name} SET {$sql} " . $this->_sql;
				if(!empty($whrsql)){ return $this->exec(); } //这行是干什么的？条件为空,导致整个表的数据,全部更新了! 判断Peace加上的
            }
            else
            {
            	$this->_sql = "UPDATE {$table_name} SET {$sql} ";
            }            
        }
        
        return $this;
    }
    
    /**
     * 删除一条数据
     * 
     * @param string $table_name 要删除数据的表名称
     * @return object 返回当前对象指针
     *
     * @since  1.0
     */
    public function delete($table_name = '')
    {
        if ( empty($table_name) || is_bool($table_name) )
        {
            $clearSQL = (false === $table_name ? false : true);
            $clearSQL || $sql = $this->_sql;
            $table_name = $this->quoteName($this->_tableName);
            $this->_sql = ("DELETE FROM {$table_name} " . $this->_sql);
            $flag = $this->exec($clearSQL);
            $clearSQL || $this->_sql = $sql;
            return $flag;
        }
        
        $table_name = $this->quoteName($table_name);
        $this->_sql = "DELETE FROM {$table_name} ";
        return $this;
    }

    /**
     * 拼接FROM语句
     *
     * @param  string $sql 要拼接的SQL
     * @return object 返回当前对象指针
     *
     * @since  1.0
     */
    public function from($table_name)
    {
        $table_name = $this->quoteName($table_name);
        $this->_sql .= "FROM {$table_name} ";
        return $this;
    }

    /**
     * 拼接WHERE语句
     *
     * @param  mixed  $values    要拼接的字段名与值（注：该处只会处理第一个数组元素），该值只要保证里面不包含SQL注入非法字符时，
     *                           可用字符串或数组方式调用但如果带有非法字符时请用数组调用，如：where a.mid = b.mid 或  where mid = 1 
     *                           可使用   ->where(array('a.mid' => 'b.mid'))         ->where(array('mid' => 1)) 
     *                           或       ->where('a.mid = b.mid')                   ->where('mid = 1')       两种调用方法 
     *                           但如果是：where mid = '1' 时，就只能用数组调用，因为1里带了两个单引号，单引号是SQL注入的非法字符
     * @param  bool   $operators 操作符默认为 '=' 即：键 = 值，如果传递 '!=' 时就是 键 != 值
     * @return object            返回当前对象指针
     *
     * @since  1.0
     */
    public function where( $values, $operators = '=' )
    {
        if ( !empty($values) )
        {
            $this->_sql .= "WHERE " . $this->filterParams($values, $operators) . ' ';
            
            if (count($values) > 1)
            {
                $values = cls_Array::limit($values, 1, count($values), true);
                foreach ( $values as $key => $value )
                {
                    $this->_and(array($key => $value));
                }
            }
            
            $this->_sql .= ' ';
        }
        
        return $this;
    }

    /**
     * 拼接OR操作
     * 如：$db->select('*')->from('#__members')->where('mname')->like('a')->_and('mname')->like('d')->exec()->fetch();
     *
     * @param  mixed  $values    要拼接的字段名与值（注：该处只会处理第一个数组元素），如果该值为字符串时，
     *                           只能是数据表或字段名称组成的字符串，如果数组的值为字段时增加一个反引号 ` 即可
     * @param  bool   $operators 操作符默认为 '=' 即：键 = 值，如果传递 '!=' 时就是 键 != 值
     * @since nv50
     */
    public function _or( $values, $operators = '=' )
    {
        if ( !empty($values) )
        {
            $this->_sql .= "OR " . $this->filterParams($values, $operators) . ' ';
        }
            
        return $this;
    }

    /**
     * 拼接AND操作
     * 如：$db->select()->from('#__members')->where(array('mid' => '1'))->_and(array('checked' => '1'))->exec()->fetch()
     *
     * @param  mixed  $values    要拼接的字段名与值（注：该处只会处理第一个数组元素），如果该值为字符串时，
     *                           只能是数据表或字段名称组成的字符串，如果数组的值为字段时增加一个反引号 ` 即可
     * @param  bool   $operators 操作符默认为 '=' 即：键 = 值，如果传递 '!=' 时就是 键 != 值
     * @since  1.0
     */
    public function _and( $values, $operators = '=' )
    {
        if ( !empty($values) )
        {
            $this->_sql .= "AND " . $this->filterParams($values, $operators) . ' ';
        }
        
        return $this;
    }
    
    /**
     * 过滤安全参数
     * 
     * @param  mixed  $params    要过滤的参数
     * @param  bool   $operators 操作符默认为 '=' 即：键 = 值，如果传递 '!=' 时就是 键 != 值
     * @return string $sql       过滤后的参数组成的SQL
     * 
     * @since  1.0
     */
    public function filterParams( $params, $operators = '=' )
    {
        if ( is_array($params) )
        {
            $field = key($params);
            $value = current($params);
            
            $field = $this->_addBacktick($field);
            $value = $this->filterValue($value);
            
            $sql = "{$field} {$operators} {$value}";
        }
        else
        {
        	$sql = $this->quoteName( (string) $params );
        }
        
        return $sql;
    }

    /**
     * 拼接ORDER BY 排序语句
     *
     * @param  string $sql 要拼接的SQL
     * @return object      返回当前对象指针
     *
     * @since  1.0
     */
    public function order($sql)
    {
        $sql = $this->quoteName($sql);
        $this->_sql .= "ORDER BY {$sql} ";
        return $this;
    }

    /**
     * 拼接HAVING 语句
     *
     * @param  string $sql 要拼接的SQL
     * @return object      返回当前对象指针
     *
     * @since  1.0
     */
    public function having($sql)
    {
        $sql = $this->quoteName($sql);
        $this->_sql .= "HAVING {$sql} ";
        return $this;
    }

    /**
     * 拼接GROUP BY 语句
     *
     * @param  string $field 要拼接的字段名称
     * @return object        返回当前对象指针
     *
     * @since  1.0
     */
    public function group( $field )
    {
        $field = $this->quoteName($field);
        $this->_sql .= "GROUP BY {$field} ";
        return $this;
    }

    /**
     * 拼接LIMIT 语句
     *
     * @param  int    $limit  开始偏移的位置
     * @param  int    $offset 偏移量
     * @return object         返回当前对象指针
     *
     * @since  1.0
     */
    public function limit( $limit, $offset = 0 )
    {
        $limit = (int) $limit;
        $offset = (int) $offset;
        
        if ( $limit >= 0 )
        {
            $this->_sql .= "LIMIT {$limit} ";
        }
        
        if ( $offset > 0 )
        {
            $this->_sql .= ", {$offset} ";
            //$this->_sql .= "OFFSET {$offset} ";  # 如果想把偏移位置放后面调用可使用该语句
        }
        
        return $this;
    }
    
    /**
     * 给字段添加反引号
     * 
     * @param  string $field    字段名
     * @return string           已经添加反引号的字段名
     * 
     * @since  1.0
     */
    protected function _addBacktick( $field )
    {
        $field = (string) $field;
        
        if ( (false !== stripos($field, '.`')) || (@$field{0} === '`') )
        {
            $field = $this->quoteName($field);
        }
        else
        {
            # 拆分别名与字段名
            $field_split = explode('.', $field);
            
            if ( isset($field_split[1]) )
            {
                $field = $this->quoteName( $field_split[0] . '.`' . $field_split[1] . '`' );
            }
            else
            {
            	$field = ('`' . $this->quoteName($field_split[0]) . '`');
            }
        }
        
        return $field;
    }

    /**
     * 拼接LIKE语句
     *
     * @param  string $text  搜索条件
     * @param  string $split 查询格式，如果第一个字符为非%，则为 keyword% 方法查询，
     *                                 如果第二个字符为非%，则以 %keyword 方法查询默认为 %keyword% 查询，
     *                                 否则当传递空字符时使用 field = 'keyword' 方法查询
     * @return object 返回当前对象指针
     *
     * @since  1.0
     */
    public function like($text, $split = '%%', $multi = false)
    {
        $this->like = true;
        $text = $this->escape($text, true);
        if($split{0} == '%')
        {
            $text = '%' . $text;
        }
        if($split{1} == '%')
        {
            $text .= '%';
        }
		$multi && $text = str_replace(array(' ','*'),'%',$text);
        $this->_sql .= "LIKE '{$text}' ";

        return $this;
    }

    /**
     * 拼接 LEFT JOIN 操作
     *
     * @param string $table_name 要拼接的数据表
     *
     * @since nv50
     */
    public function leftJoin($table_name)
    {
        $this->join('left', $table_name);
        return $this;
    }

    /**
     * 拼接 RIGHT JOIN 操作
     *
     * @param string $table_name 要拼接的数据表
     *
     * @since nv50
     */
    public function rightJoin($table_name)
    {
        $this->join('right', $table_name);
        return $this;
    }

    /**
     * 拼接 INNER JOIN 操作
     *
     * @param string $table_name 要拼接的数据表
     *
     * @since nv50
     */
    public function innerJoin($table_name)
    {
        $this->join('inner', $table_name);
        return $this;
    }

    /**
     * 拼接JOIN语句
     *
     * @param  string $type        拼接类型，有：left,right,inner
     * @param  string $table_name  要联结的数据表名
     * @return object              返回当前对象指针
     * @since  1.0
     */
    public function join($type, $table_name)
    {
        $type = strtoupper($type);
        $table_name = $this->quoteName($table_name);
        $this->_sql .= $type . " JOIN {$table_name} ";
        return $this;
    }
    
    /**
     * 拼接ON操作
     * 该方法只为联结查询拼接而写，不使用联结查询时可不拼接此方法
     * 如：$db->select()->from('#__members AS m')->innerJoin('#__members_1 AS m1')->_on('m.mid=m1.mid')->where('m.mid=1')->exec()->fetch();
     *
     * @param string $sql 要拼接的SQL信息
     * @since nv50
     */
    public function _on($sql)
    {
        if ( !empty($sql) )
        {
            $sql = $this->quoteName($sql);
            $this->_sql .= "ON {$sql} ";
        }
        
        return $this;
    }
    
    /**
     * 拼接IN操作，如果想用子查询请先用另一条SQL语句查询出值再传递到该参数里（即强制减少子查询的使用）
     * 
     * 正确调用方法1：$db->select()->from('#__members AS m')->where('m.mid')->_in(array(1, 2, 3))->exec();
                      while($row = $db->fetch()){.....}
                        
     * 正确调用方法2：$db->select()->from('#__members AS m')->where('m.mid')->_in('1, 2, 3')->exec();
                      while($row = $db->fetch()){.....}
                        
     * 错误调用方法如：$db->select()->from('#__members AS m')->where('m.mid')->_in("'1','2','3'")->exec();
     *
     * @param mixed $values 要拼接的值（注：该值可为数组或字符串，但如果该值有可能会引起SQL注入的特殊字符时请用数组传递，否则查询不被通过）
     * @since nv50
     */
    public function _in( $values )
    {
        if ( empty($values) )
        {
            $values = $this->filterValue( $values );
            $this->_sql .= "= {$values} ";
        }
        else
        {
        	if ( is_array($values) )
            {
                $values = implode(', ', array_map(array($this, 'filterValue'), $values));
            }
            else
            {            	
                $values = $this->escape( $values );
            }
            $this->_sql .= "IN ({$values}) ";
        }
        
        return $this;            
    }
    
    /**
     * 拼接NOT语句
     * 
     * @param mixed  $values 要拼接的值（注：该值可为数组或字符串，但如果该值有可能会引起SQL注入的特殊字符时请用数组传递，否则查询不被通过）
     * @param string $method 拼接条件方法，如：  like、_in
     */
    public function not( $values, $method )
    {
        $this->_sql .= 'NOT ';
        return $this->$method($values);
    }

    /**
     * 发送一条SQL查询
     *
     * @param  string $clearSQL 是否查询后清除上一个SQL信息, TRUE为清除，FALSE为不清除让下一个操作使用
     * @param  string $type     查询类型，具体请查看：{@see cls_mysql::query()}
     * @return object 查询成功返回当前指针，否则返回FALSE
     * @since  1.0
     */
    public function exec( $clearSQL = true,  $type = '' )
    {
        if(empty($this->_sql)) return false;
        if ( $this->debug )
        {
        	var_dump($this->_sql);
            echo '<br />';
        }
        $this->query = $this->db->query(trim($this->_sql), $type, true);
        $clearSQL && $this->clear();
        
        return ($this->query ? $this : false);
    }
    
    /**
     * 获取当前query句柄
     * 
     * @param  string   $clearSQL 是否查询后清除上一个SQL信息, TRUE为清除，FALSE为不清除让下一个操作使用
     * @param  string   $type     查询类型，具体请查看：{@see cls_mysql::query()}
     * 
     * @return resource           返回mysql_query查询后的资源标识符，如果查询失败则返回FALSE
     */
    public function getQuery( $clearSQL = true,  $type = '' )
    {
        if ( $this->exec($clearSQL, $type) )
        {
            return $this->query;
        }
        
        return FALSE;
    }

    /**
     * 查询信息
     * 调用方法示例一（循环）：
     * $db->select('fc.title, fc.fcaid')->from('#__fcatalogs AS fc')->exec();
     * while($row = $db->fetch()) {
     *     echo "<div>{$row['fcaid']}---------{$row['title']}</div>";
     * }
     *
     * 调用方法示例二（循环嵌套查询）：
     * $query = $db->select('mid')->from("#__members")->limit(3)->getQuery();
       while($row = $db->fetch($query))
       {
           $row2 = $db->select()->from('#__archives1')->where(array('mid' => $row['mid']))->limit(1)->exec()->fetch();
           var_dump($row, $row2);
       }
     *
     * 调用方法示例三（查询单条）：
     * $row = $db->select('fc.title, fc.pid')->from('#__fcatalogs AS fc')->exec()->fetch();
     *
     * @limk http://docs.php.net/manual/zh/function.mysql-fetch-array.php
     *
     * @param  resource $query       循环体变量名称，如果该为空时只查询一条，否则循环遍历
     * @param  int      $result_type 查询类型，可以接受以下值：
     *                               MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH。
     *                               默认值是 MYSQL_BOTH
     * @return                       返回根据从结果集取得的行生成的数组，如果没有则返回 FALSE
     * @since  1.0
     */
    public function fetch( $query = '', $result_type = '' )
    {
        if( !is_resource($query) )
        {
            $query = $this->query;
        }
        
        if ( strtoupper($this->dervers) == 'MYSQLI' )
        {
            $result_type = MYSQLI_ASSOC;
        }
        else
        {
        	$result_type = MYSQL_ASSOC;
        }
        
        return $this->db->fetch_array( $query, $result_type );
    }

    /**
     * 修改表结构
     *
     * @example       $db->alterTable('#__members', array('Field' => 'DROP COLUMN test'));
                      $db->alterTable('#__members', array('Field' => 'ADD COLUMN test', 'Type' =>'varchar(10)', 'Null' => false));
     * @param  string $table_name 要修改的表名称
     * @param  string $condition  修改条件
     *
     * @return bool   修改成功返回TRUE，否则返回FALSE
     * @since  1.0
     */
    public function alterTable($table_name, $condition)
    {
        $table_name = $this->quoteName($table_name);
        $sql = $this->_getColumnSQL($condition);
        $this->_sql = "ALTER TABLE `{$table_name}` {$sql}";
        return (bool)$this->exec();
    }
    
    /**
     * 获取列信息
     * 
     * @param  array  $field 字段信息数组，key有：Field、Type、Null、Defalut、Extra
     * @return string        返回构造好的SQL 
     */
    protected function _getColumnSQL(array $field)
	{
		$blobs = array('text', 'smalltext', 'mediumtext', 'largetext');

		$fieldName = (string) $field['Field'];
		$fieldType = isset($field['Type']) ? $field['Type'] : '';
		$fieldNull = isset($field['Null']) ? (bool)$field['Null'] : null;
		$fieldDefault = isset($field['Default']) ? (string) $field['Default'] : null;
		$fieldExtra = isset($field['Extra']) ? (string) $field['Extra'] : '';

		$sql = $this->quoteName($fieldName) . ' ' . $fieldType;

		if ( $fieldNull !== null )
        {
            if ($fieldNull)
    		{
    			if ($fieldDefault === null)
    			{
    				$sql .= ' DEFAULT NULL';
    			}
    			else
    			{
    				$sql .= ' DEFAULT ' . $this->filterValue($fieldDefault);
    			}
    		}
    		else
    		{
    			if (in_array($fieldType, $blobs) || $fieldDefault === null)
    			{
    				$sql .= ' NOT NULL';
    			}
    			else
    			{
    				$sql .= ' NOT NULL DEFAULT ' . $this->filterValue($fieldDefault);
    			}
		    }
        }

		if ($fieldExtra)
		{
			$sql .= ' ' . strtoupper($fieldExtra);
		}

		return $sql;
	}

    /**
     * 过滤值，以插入方式过滤
     *
     * @param  array  $values      要过滤的值
     * @param  string $parentheses 是否在值外加入小括号（注：一次性插入多行数据时，请传递false）
     * @return string              过滤后的值
     * @since  1.1
     */
    public function filterInsertValue( array $values, $parentheses = true )
    {
        $arrays = array();
        foreach ($values as $value)
        {
            if ( is_array($value) )
            {
                $arrays[] = $this->filterInsertValue($value, true);
            }
            else
            {
            	$arrays[] = $this->filterValue($value);
            }
        }
        
        return (($parentheses ? '(' : '') . implode(', ', $arrays) . ($parentheses ? ')' : ''));
    }

    /**
     * 滤过字段名称
     *
     * @param  mixed  $field_name 要过滤的字段名称
     * @return string $new_fields 过滤后的字段名称
     *
     * @since  1.0
     */
    public function filterField( $field_name )
    {
        if(is_string($field_name)) {
            $field_array = explode(',', $field_name);
        } else {
            $field_array = (array) $field_name;
        }
        
        $new_fields = array();
        foreach($field_array as $name)
        {
            $name = trim($name);
            $new_fields[] = $this->_addBacktick($name);
        }
        
        return implode(', ', $new_fields);
    }

    /**
     * 过滤值，以更新方式过滤
     *
     * @param  mixed  $field     要过滤的字段
     * @param  mixed  $value     要过滤的值
     * @param  string $delimiter 分隔符
     * 
     * @return string 过滤后的值
     * @since  1.0
     */
    public function filterUpdateValue( $field, array $value, $delimiter = ', ' )
    {
        if( empty($field) ) return '';
        if( !is_array($field) )
        {
            $field_array = explode(',', (string) $field);
        }
        else
        {
        	$field_array = $field;
        }
        
        $new_value = array();
        for($i = 0; $i < count($field_array); ++$i)
        {
            $new_value[] = "{$field_array[$i]} = " . (empty($value[$i]) ? "'' " : $this->filterValue($value[$i]));
        }

        return implode($delimiter, $new_value);
    }

    /**
     * 过滤值
     *
     * @param  string $value 要过滤的值，如果该值是一个数组，并且带有type与value时会解析成相关类型，目前支持type为field即为一个字段类型，值不会带引号
     *                       如：array( 'field_name' => array('type'=> 'field', 'value' => "field_name + 1") )
     * @return string        返回已经过滤的值
     *
     * @since  nv50
     **/
    public function filterValue($value)
    {
        if ( is_array($value) )
        {
            if ( isset($value['type']) && (strtolower($value['type']) == 'field') )
            {
                return $this->_db->escape($value['value']);
            }
        }
        
        return ("'" . $this->escape($value) . "'");
    }

    /**
     * 获取数据表行数
     * 注：该方法以后会弃用，如果是在MVC中调用请使用 _08_MysqlQuery::count 代替
     * @example $this->_db->where(array('aid' => $aid))->_and(array('checked' => 1))->getTableRowNum("#__$ntbl");
     *
     * @param  string $table_name 要获取的数据表名称
     * @return array  $row        返回行数
     *
     * @since  1.0
     */
    public function getTableRowNum( $table_name )
    {
        $sql = "SELECT COUNT(*) AS num FROM " . $this->quoteName($table_name);
        if ( empty($this->_sql) )
        {
            $this->_sql = $sql;
        }
        else
        {
        	$this->_sql = ($sql . ' ' . $this->_sql);
        }
        
        $row = $this->exec()->fetch();
        return $row['num'];
    }

    /**
     * 获取数据表行数
     * 注：该方法将替换_08_MysqlQuery::getTableRowNum，如果是在MVC中调用请使用该方法代替_08_MysqlQuery::getTableRowNum
     *
     * @param  string $table_name 要获取的数据表名称
     * @return array  $row        返回行数
     *
     * @since  1.0
     */
    public function count( $clearSQL = true )
    {
        $row = $this->read('COUNT(*) AS num', $clearSQL);
        return $row['num'];
    }

    /**
     * 获取某个表字段信息
     *
     * @param  string $table_name 要获取的数据表名称
     * @param  bool   $type_only  是否只获取类型，是为TRUE，否为FLASE
     * @return array  $result     返回字段信息数组
     *
     * @since  1.0
     */
    public function getTableColumns( $table_name, $type_only = true )
	{
		$result = array();
		$this->_sql = 'SHOW FULL COLUMNS FROM ' . $this->quoteName($table_name);
		$fields = $this->loadObjectList();
		if ($type_only) {
			foreach ($fields as $field)
			{
				$result[$field->Field] = $field->Type;
				#$result[$field->Field] = preg_replace("/[(0-9)]/", '', $field->Type);
			}
		} else {
			foreach ($fields as $field)
			{
				$result[$field->Field] = $field;
			}
		}

		return $result;
	}

    /**
     * 加载一个对象列表
     *
     * @param  string $key   指定数据下标
     * @param  string $class 使用返回的行对象的类名
     * @return array  $array 返回对象列表数组
     *
     * @since nv50
     */
    public function loadObjectList($key = '', $class = 'stdClass')
	{
		$array = array();
        $this->exec();
		while ($row = $this->db->fetch_object($this->query, $class))
		{
			if (!empty($key))
			{
				$array[$row->$key] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}
		$this->db->free_result($this->query);

		return $array;
	}

    /**
     * 重命名一个数据表
     *
     * @param  string $old_table 旧的表名
     * @param  string $new_table 新的表名
     * @return bool              修改成功返回TRUE，否则返回FALSE
     *
     * @since nv50
     */
    public function renameTable($old_table, $new_table)
	{
	    $this->_sql = "RENAME TABLE {$old_table} TO {$new_table}";
		return (bool)$this->exec();
	}

    /**
     * 删除一个数据表
     *
     * @param  string $table_name 要删除的表名称
     * @param  bool   $ifExists   选择指定的表必须存在
     * @return bool               删除成功返回TRUE，否则返回FALSE
     *
     * @since nv50
     */
    public function dropTable($table_name, $if_exists = true)
	{
	    $this->_sql = 'DROP TABLE ' . ($if_exists ? 'IF EXISTS ' : '') . $this->quoteName($table_name);
		return (bool)$this->exec();
	}

    /**
     * 获取数据表列表
     *
     * @since nv50
     */
	public function getTableList( $return = false)
	{
        $this->_sql = 'SHOW TABLES ';
        
        if ( $return )
        {
            return $this;
        }
        
		return $this->exec()->fetch($query);
	}

    /**
     * 锁定一个表
     *
     * @param  string $table_name 要锁定的数据表名
     * @return bool               锁定成功返回TRUE，否则返回FALSE
     *
     * @since  1.0
     */
	public function lockTable($table_name)
	{
	    $this->_sql = 'LOCK TABLES ' . $this->quoteName($table_name) . ' WRITE';
		return (bool)$this->exec();
	}

    /**
     * 解锁数据库中的表。
     * 
     * @return bool 解锁成功返回TRUE，否则返回FALSE
     * @since  1.0
     */
	public function unlockTables()
	{
		$this->_sql = 'UNLOCK TABLES';
		return (bool)$this->exec();
	}

    /**
     * 替换SQL前缀
     *
     * @param  string $sql    要替换的SQL语句
     * @param  string $prefix 要替换的前缀
     * @return string         替换后的正常SQL语句
     *
     * @since  1.0
     */
    public function replacePrefix($sql, $prefix = '#__')
    {
        return str_replace($prefix, $this->_tblprefix, $sql);
    }

    /**
     * 完全清空一个表
     *
     * 初始化表结构如：自增ID会还原为1等等，该函数与DELETE FROM区别是该方法对清空整张表来说
     * 比DELETE FROM快，但没DELETE FROM灵活，DELETE FROM不会改变现结构状态，而TRUNCATE TABLE
     * 则会，详情请参考MYSQL的TRUNCATE TABLE语句。
     * @param  string $table_name 数据表名称
     * @return bool               清空成功返回TRUE，否则返回FALSE
     *
     * @see   http://dev.mysql.com/doc/refman/5.1/zh/sql-syntax.html#truncate
     * @since nv50
     */
    public function truncateTable($table_name)
    {
        $this->_sql = 'TRUNCATE TABLE ' . $this->escape($table_name);
		return (bool)$this->exec();
    }

    /**
     * 过滤数据表名称
     *
     * @param  string $table_name 要过滤的数据表名
     * @return string             过滤后的数据表名
     *
     * @since  1.0
     */
    public function quoteName( $table_name )
    {
        return $this->replacePrefix($this->escape($table_name));
    }
    
    /**
     * 设置当前属性接口
     */
    public function setter( $name, $value )
    {
        if ( isset($this->$name) )
        {
            $this->$name = $value;
        }        
    }
    
    /**
     * 获取当前属性接口
     */
    public function getter( $name )
    {
        return $this->$name;
    }

    /**
     * 防止外部增加属性
     *
     * @param  string $name  属性名称
     * @param  string $value 属性值
     *
     * @since  1.0
     */
	public function __set( $name, $value )
	{
	    $this->$name = NULL;
	}

    /**
     * 清除属性值
     *
     * @since nv50
     */
    public function clear( $varname = '' )
    {
        if ( $varname )
        {
            if ( property_exists($this, $varname) )
            {
                $this->$varname = '';
            }
            else if ( property_exists($this->db, $varname) )
            {
            	$this->db->$varname = '';
            }
        }
        else
        {
        	$this->_sql = '';
            $this->_type = '';
            $this->db->like = false;
            $this->debug = false;
        }
        
        return $this;
    }

    /**
     * 设置调试开关
     **/
    public function setDebug()
    {
        $this->debug = true;
        return $this;
    }
    
    /**
     * 清除查询对象
     * 
     * @since      nv50
     * @deprecated 准备废弃，用self::clear('query')代替
     */
    public function clearQuery()
    {
        if ( is_resource($this->query) )
        {
            $this->query = NULL;
        }
    }

    public function __construct( array $config )
    {
        extract($config); 
        isset($flag) || $flag = true;
        isset($config['tblprefix']) && $this->_tblprefix = $config['tblprefix'];
        
        isset($drivers) && $this->dervers = $drivers;
        $driversClass = 'cls_' . strtolower($this->dervers);
        $this->db = new $driversClass();
        $this->db->connect( $dbhost, $dbuser, $dbpw, $dbname, $pconnect, $flag, $dbcharset );
    }
    
    public function __clone()
    {
        $this->__construct( _08_factory::getDBOConfig() );
    }
    
    public function __call($name, $argv)
    {
        if ( method_exists($this->db, $name) )
        {
            if ( empty($argv) )
            {
                return call_user_func(array($this->db, $name));
            }
            else
            {
            	return  call_user_func_array(array($this->db, $name), $argv);
            }
        }
    }
    
    public function __get($name)
    {
        if ( property_exists($this->db, $name) )
        {
            return $this->db->$name;
        }
        else
        {
        	return null;
        }
    }
}