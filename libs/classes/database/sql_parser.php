<?php
/**
 * SQL解析操作类
 * 
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 **/
defined('M_COM') || exit('No Permisson');
_08_Loader::import(dirname(__FILE__) . ':PHP-SQL-Parser:php-sql-parser');
class _08_SQL_Parser extends PHPSQLParser
{
    private $_08_db = null;
    
    /**
     * 解析前的SQL
     **/
    private $_08_sql = '';
    
    /**
     * 上一个值
     **/
    private $_08_prevBaseExpr;
    
    /**
     * JOIN类型
     **/
    private $_08_joinType;
    
    /**
     * 上一个类型
     **/
    private $_08_prevExprType;
    
    /**
     * 调试开关
     **/
    private $_08_debug = false;
    
    /**
     * OPTION 解析数组
     **/
    private $_08_options = array();
    
    private $_08_SQL_Creator = null;
    
    /**
     * 对解析出来的SQL过滤并合并
     * 
     * @return string $sql 返回合并好的SQL
     * @since  nv50
     **/
    public function mergeSQL()
    {
        $front = cls_frontController::getInstance();
        $gets = $front->getAction();
        
//        if ($this->_08_debug && $gets !== 'regcode')
//        {
//            var_dump("\n" . $this->_08_sql . "\n" . $this->_08_SQL_Creator->created . "\n\n\n");
//            #exit;
//        }
        
//        return $this->_08_SQL_Creator->created;
        
        $sql = '';
        if (!empty($this->parsed) && is_array($this->parsed))
        {
            $sql .= $this->mergeQuery($this->parsed);
        }
        
        if ($this->_08_debug && $gets !== 'regcode')
        {
            var_dump($this->parsed);
            var_dump("\n" . $this->_08_sql . "\n\n" . $sql . "\n\n\n");
            exit;
        }
        
        return trim($sql);
    }
    
    public function mergeQuery(array $parsed)
    {
        $sql = '';
        if (!empty($parsed))
        {
            if (isset($parsed['OPTIONS']))
            {
                $this->_08_options = $parsed['OPTIONS'];
                unset($parsed['OPTIONS']);
            }
            
            if (isset($parsed['PASSWORD']))
            {
                unset($parsed['PASSWORD']);
            }
            
            if (isset($parsed['ON DUPLICATE KEY UPDATE']))
            {
                $onDuplicate = $parsed['ON DUPLICATE KEY UPDATE'];
                $parsed['ON DUPLICATE KEY UPDATE'][] = array_pop($parsed['VALUES']);
                array_pop($parsed['VALUES']);
                $parsed['VALUES'][] = $onDuplicate;
                $v = current($parsed['ON DUPLICATE KEY UPDATE'][0]["sub_tree"]);
                $v['expr_type'] = 'const';
                $v['base_expr'] = 'VALUES';
                $parsed['ON DUPLICATE KEY UPDATE'][0]["sub_tree"][] = $v;
            }
            #var_dump($parsed);exit;
            foreach ($parsed as $key => &$parse)
            {
                $key = preg_replace('/\s/', '', strtolower($key));
                $this->escapeValues($parse);
                $method = 'merge' . ucfirst($key);
                $sql .= $this->$method($parse);
            }
        }
        
        return $sql;
    }
    
    /**
     * 过滤值
     * 
     * @param array $parsed 经过解析的SQL信息
     **/
    public function escapeValues(array &$parsed)
    {
        $this->_08_prevBaseExpr = '';
        foreach ($parsed as $key => &$parse)
        {
            if (!empty($parse['sub_tree']) && is_array($parse['sub_tree']))
            {
                $this->escapeValues($parse['sub_tree']);
            }
            elseif (!empty($parse['data']) && is_array($parse['data']))
            {
                $this->escapeValues($parse['data']);
            }
            elseif (isset($parse["base_expr"]))
            {                
                $parse["base_expr"] = $this->_08_SQL_Creator->escapeBaseExpr($parse["base_expr"]);
                $this->_08_prevBaseExpr = $parse["base_expr"];
                $this->_08_SQL_Creator->setter('_08_prevBaseExpr', $this->_08_prevBaseExpr);
            }
        }
    }
    
    public function mergeSelect(array $parsed)
    {
        $select = ' SELECT';
        if (!empty($parsed) && is_array($parsed))
        {
            $select .= (' ' . $this->mergeColumns($parsed, true));
        }

        return $select; 
    }
    
    public function mergeFrom(array $parsed)
    {
        $from = ' FROM';
        $from .= $this->mergeTable($parsed);
        $from = preg_replace('/FROM\s+([A-Z]*?)\s+JOIN/i', 'FROM', $from);

        return $from; 
    }
    
    public function mergeTable(array $parsed)
    {
        $tables = '';
        if (empty($parsed))
        {
            return $tables;
        }

        foreach ($parsed as $table)
        {
            @$table['expr_type'] = strtolower($table['expr_type']);
            if ( (empty($table['table']) && (!in_array($table['expr_type'], array('subquery', 'ref_clause'))) ) || empty($table['base_expr']) )
            {
                continue;
            }
            
            if (isset($table['join_type']))
            {
                $table['join_type'] = strtoupper($table['join_type']);
                if (!empty($this->_08_joinType) || ($table['ref_type'] === 'ON'))
                {
                    if (($this->_08_joinType === 'JOIN') || ($table['ref_type'] === 'ON'))
                    {
                        if ($table['join_type'] === 'CROSS')
                        {
                            $tables .= ',';
                        }
                        elseif($table['join_type'] === 'JOIN')
                        {
                            $tables .= ' INNER JOIN';
                        }
                        else
                        {
                            $tables .= " {$table['join_type']} JOIN";
                        }
                        
                        $this->_08_joinType = '';
                    }
                }                    
                else
                {
                	$this->_08_joinType = $table['join_type'];
                }
            }
            
            if (isset($table['expr_type']))
            {
                if ($table['expr_type'] === 'subquery' && !empty($table['sub_tree']))
                {
                    if ((strtoupper($this->_08_prevBaseExpr) !== 'IN') && !preg_match('/(JOIN|,)$/i', trim($tables)))
                    {
                        $tables .= ',';
                    }
                    $tables .= (' (' . trim($this->mergeQuery($table['sub_tree'])) . ')');            
                    if (isset($table['alias']["base_expr"]))
                    {
                        $tables .= (' ' . $table['alias']["base_expr"]);
                    }
                    if (!empty($table['ref_clause']))
                    {
                        $tables .= (" {$table['ref_type']} " . $this->mergeColumns($table['ref_clause']));
                    }
                    $this->_08_joinType = '';
                }
                elseif($table['ref_clause'])
                {
                    $alias = ' ';
                    
                    if (isset($table['alias']["base_expr"]))
                    {
                        $alias .= $table['alias']["base_expr"];
                    }
                    
                    if (count($table['ref_clause']) > 3)
                    {
                        $tables .= (" {$table['table']} $alias {$table['ref_type']} (" . $this->mergeColumns($table['ref_clause']) . ')');
                    }
                    else
                    {
                    	$tables .= (" {$table['table']} $alias {$table['ref_type']} " . $this->mergeColumns($table['ref_clause']));
                    }
                }
                else
                {
                    $tables .= " {$table['base_expr']}";
                }
            }
        }

        return $tables; 
    }
    
    public function mergeWhere(array $parsed)
    {
        $where = ' WHERE ';
        $where .= $this->mergeColumns($parsed);

        return $where; 
    }
    
    public function mergeGroup(array $parsed)
    {
        $where = ' GROUP BY ';
        $where .= $this->mergeColumns($parsed, true);

        return $where; 
    }
    
    public function mergeInsert(array $parsed)
    {
        $insert = ' INSERT INTO';
        if (!empty($parsed['table']) && !empty($parsed['base_expr']))
        {
            $insert .= " {$parsed['base_expr']}";
        }
        
        if (!empty($parsed['columns']) && is_array($parsed['columns']))
        {
            $insert .= (' (' . $this->mergeColumns($parsed['columns'], true) . ')');
        }

        return $insert; 
    }
    
    public function mergeReplace(array $parsed)
    {
        $insert = ' REPLACE INTO';
        if (!empty($parsed['table']) && !empty($parsed['base_expr']))
        {
            $insert .= " {$parsed['base_expr']}";
        }
        
        if (!empty($parsed['columns']) && is_array($parsed['columns']))
        {
            $insert .= (' (' . $this->mergeColumns($parsed['columns'], true) . ')');
        }

        return $insert; 
    }
    
    public function mergeUpdate(array $parsed)
    {
        $update = ' UPDATE' . $this->mergeTable($parsed);
        return $update;
    }
    
    public function mergeSet(array $parsed)
    {
        $set = ' SET';
        $sub_tree = '';
        
        foreach ($parsed as $parse)
        {
            if (!empty($parse['sub_tree']) && is_array($parse['sub_tree']))
            {                
                $sub_tree .= (', ' . $this->mergeColumns($parse['sub_tree']));
            }
        }
        
        return $set . ($sub_tree ? substr($sub_tree, 1) : '');
    }
    
    public function mergeColumns(array $parsed, $insert = false)
    {
        $fields = $base_expr_split = '';
        foreach ($parsed as $field)
        {
            if (!isset($field['expr_type']) || !isset($field['base_expr']))
            {
                continue;
            }

            if ($fields !== '')
            {
                if ($insert && ($this->_08_prevBaseExpr !== 'DISTINCT'))
                {
                    $base_expr_split = ', ';
                }
                else
                {
                	$base_expr_split = ' ';
                }
            } 

            $field['expr_type'] = strtolower($field['expr_type']);
            
            if ($field['expr_type'] === 'subquery' && !empty($field['sub_tree']))
            {
                $this->_08_joinType = '';
                if (!in_array(strtoupper($this->_08_prevBaseExpr), array('IN', 'EXISTS', 'JOIN')))
                {
                    $fields .= ',';
                }
                
                $fields .= (' (' . trim($this->mergeQuery($field['sub_tree'])) . ')'); 
                         
                if (isset($table['alias']["base_expr"]))
                {
                    $fields .= (' ' . $table['alias']["base_expr"]);
                }
            }
            else
            {
                if (!empty($this->_08_options) && is_array($this->_08_options))
                {
                    $option = current($this->_08_options);
                    $fields .= "$option ";
                    $keys = array_keys($this->_08_options, $option);
                    unset($this->_08_options[$keys[0]]);
                }
                    
                if (in_array($field['expr_type'], array('bracket_expression', 'in-list', 'expression'), true))
                {
                    $fields .= ' ';
                }
                elseif (($field['expr_type'] === 'function') && !in_array(strtoupper($field['base_expr']), 
                    array('MID', 'PASSWORD', 'FIND_IN_SET', 'ROUND', 'MONTH', 'ABS', 'CONCAT', 
                          'ADDDATE', 'REPLACE', 'WEEKDAY', 'DAY', 'HOUR', 'MINUTE')))
                {
                    # mid与MYSQL关键字冲突，因估计本系统很少用MID关键字，所以暂时不考虑。
                    $fields .= " {$field['base_expr']}()";
                }
                else
                {                    
                    if (($field['base_expr'] === '|') && ($this->_08_prevBaseExpr === '|'))
                    {
                        $fields .= $field['base_expr'];
                    }
                    else
                    {
                        if(($field['expr_type'] === 'aggregate_function') && in_array(strtoupper($field['base_expr']), array('GROUP_CONCAT')))
                        {
                            if ($field['base_expr'] === $this->_08_prevBaseExpr)
                            {
                                $fields .= ', ';
                            }
                            $insert = false;
                        }
                        
                    	$fields .= ($base_expr_split . $field['base_expr']);
                    }
                }
                
                if (!empty($field['sub_tree']) && is_array($field['sub_tree']))
                {
                    if (in_array($field['expr_type'], array('function', 'in-list'), true))
                    {
                        $fields .= ('(' . $this->mergeColumns($field['sub_tree'], true) . ')');
                    }
                    elseif($field['expr_type'] === 'expression')
                    {
                        $fields .= $this->mergeColumns($field['sub_tree']);
                    }
                    else
                    {
                    	$fields .= ('(' . $this->mergeColumns($field['sub_tree'], $insert) . ')');
                    }
                }
                
                if (isset($field['alias']))
                {
                    $fields .= (' ' . $field['alias']['base_expr']);
                }
                
                if (!empty($field['direction']))
                {
                    $fields .= (' ' . $field['direction']);
                }
            }
            $this->_08_prevBaseExpr = $field["base_expr"];
            $this->_08_prevExprType = $field["expr_type"];
        }
        
        return $fields;
    }
    
    public function mergeDelete(array $parsed)
    {
        $delete = ' DELETE';
        if (empty($parsed['TABLES']) || !is_array($parsed['TABLES']))
        {
            return '';
        }
        $_table = '';
        foreach ($parsed['TABLES'] as $table)
        {
            if ($_table)
            {
                $_table .= ', ';
            }
            
            $delete .= (' ' . $_table . $this->_08_db->escape($table));
        }
        
        return $delete;
    }
    
    public function mergeCreate(array $parsed)
    {
        $create = '';
        foreach ($parsed as $parse)
        {
           #$create .= (' ' . $this->_08_SQL_Creator->escapeBaseExpr($parse));
             $create .= (' ' . $parse);
        }
        
        return $create;
    }
    
    public function mergeShow(array $parsed)
    {
        $show = '';
        foreach ($parsed as $parse)
        {
           $show .= (' ' . $this->_08_SQL_Creator->escapeBaseExpr($parse));
           #  $show .= (' ' . $parse);
        }
        
        return $show;
    }
    
    public function mergeAlter(array $parsed)
    {
        return $this->mergeShow($parsed);
    }
    
    public function mergeDescribe(array $parsed)
    {
        return $this->mergeShow($parsed);
    }
    
    public function mergeDrop(array $parsed)
    {
        return $this->mergeShow($parsed);
    }
    
    public function mergeForce(array $parsed)
    {
        return $this->mergeShow($parsed);
    }
    
    public function mergeOrder(array $parsed)
    {
        $order = ' ORDER BY ' . $this->mergeColumns($parsed, true);
        return $order;
    }
    
    public function mergePassword(array $parsed)
    {
         return ' password' . $this->mergeShow($parsed);
    }
    
    public function mergeOptimize(array $parsed)
    {
        return $this->mergeShow($parsed);
    }
    
    public function mergeHaving(array $parsed)
    {
        return ' HAVING ' . $this->mergeColumns($parsed);
    }
    
    public function mergeRepair(array $parsed)
    {
        return $this->mergeShow($parsed);
    }
    
    public function mergeTruncate(array $parsed)
    {
        return $this->mergeShow($parsed);
    }
    
    public function mergeRename(array $parsed)
    {
        return $this->mergeShow($parsed);
    }
    
    public function mergeLock(array $parsed)
    {
        return $this->mergeShow($parsed);
    }
    
    public function mergeUnlock(array $parsed)
    {
        return $this->mergeShow($parsed);
    }
    
    public function mergeOnduplicatekeyupdate(array $parsed)
    {
        $onduplicate = ' ON DUPLICATE KEY UPDATE' . $this->mergeColumns($parsed);
        return $onduplicate;
    }
    
    public function mergeLimit(array $parsed)
    {
        if (!isset($parsed['offset']) || !isset($parsed['rowcount']))
        {
            return '';
        }
        
        $parsed['offset'] = (int) $parsed['offset'];
        $parsed['rowcount'] = (int) $parsed['rowcount'];
        if ($parsed['offset'] === 0)
        {
            $order = " LIMIT {$parsed['rowcount']}";
        }
        else
        {
        	$order = " LIMIT {$parsed['offset']}, {$parsed['rowcount']}";
        }
        
        return $order;
    }
    
    public function mergeValues(array $parsed)
    {
        $values = ' VALUES (';
        $record = false;
        foreach ($parsed as $key => $value)
        {            
            @$value['expr_type'] = strtolower($value['expr_type']);
            
            if (!empty($value['data']))
            {
                $values .= $this->mergeColumns($value['data'], true);
            }
            
            if ($value['expr_type'] === 'record')
            {                
                $values .= '), (';
                $record = true;
            }
        } 
        
        if ($record)
        {
            return substr($values, 0, -3);
        }
        else
        {
        	return $values . ')';
        }
    }
    
    /**
     * 设置调试开关，如果想调试某条SQL语句，只要在exec()前调用下这方法即可。
     **/
    public function setDebug()
    {
        $this->_08_debug = true;
        return $this;
    }
    
    public static function getInstance($sql = false, $calcPositions = false)
    {
        return new self($sql, $calcPositions);
    }
    
    public function __construct($sql = false, $calcPositions = false)
    {
        $this->_08_debug = false;
        $this->_08_joinType = '';
        $this->_08_db = _08_factory::getDBO();
        $this->_08_sql = $sql;
        parent::__construct($sql, $calcPositions);        
        $this->_08_SQL_Creator = new _08_SQL_Creator($this->parsed);
    }
}