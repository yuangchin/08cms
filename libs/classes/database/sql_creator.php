<?php
/**
 * 对解析出来的SQL组装操作类
 * 
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2014 08CMS, Inc. All rights reserved.
 **/
defined('M_COM') || exit('No Permisson');
_08_Loader::import(dirname(__FILE__) . ':PHP-SQL-Parser:php-sql-creator');
class _08_SQL_Creator extends PHPSQLCreator
{
    private $_08_db = null;
    
    private $_08_prevBaseExpr;

    protected function processOrderByAlias($parsed)
    {
        if (isset($parsed['base_expr']))
        {
            $parsed['base_expr'] = $this->escapeBaseExpr($parsed['base_expr']);
        }
        
        return parent::processOrderByAlias($parsed);
    }

    protected function processOperator($parsed)
    {
        if (isset($parsed['base_expr']))
        {
            $parsed['base_expr'] = $this->escapeBaseExpr($parsed['base_expr']);
        }
        
        return parent::processOperator($parsed);
    }

    protected function processColRef($parsed)
    {
        if (isset($parsed['base_expr']))
        {
            $parsed['base_expr'] = $this->escapeBaseExpr($parsed['base_expr']);
        }
        
        return parent::processColRef($parsed);
    }

    protected function processReserved($parsed) {
        if (isset($parsed['base_expr']))
        {
            $parsed['base_expr'] = $this->escapeBaseExpr($parsed['base_expr']);
        }
        
        return parent::processReserved($parsed);
    }

    protected function processConstant($parsed)
    {
        if (isset($parsed['base_expr']))
        {
            $parsed['base_expr'] = $this->escapeBaseExpr($parsed['base_expr']);
        }
        
        return parent::processConstant($parsed);
    }
    
    /**
     * 过滤基本规则
     * 
     * @param  string $base_expr 要过滤的基本规则
     * @param  string $split     基本规则包含符，目前只处理单引号与双引号包含的
     * @return string $base_expr 返回过滤后的基本规则
     **/
    public function escapeBaseExpr($base_expr, $split = "'")
    {
        $base_expr = (string) $base_expr;
        if (0 === strpos($base_expr, $split))
        {
            # 获取去除单引号的字符
            $base_expr = substr($base_expr, 1, -1);                        
            if (strtoupper($this->_08_prevBaseExpr) === 'LIKE')
            {
                $prevPercent = $endPercent = '';
                if (substr($base_expr, 0, 1) === '%')
                {
                    $base_expr = substr($base_expr, 1);
                    $prevPercent = '%';
                }
                
                if (substr($base_expr, -1) === '%')
                {
                    $base_expr = substr($base_expr, 0, strlen($base_expr) - 1);
                    $endPercent = '%';
                }
                $base_expr = $this->_08_db->escape($base_expr, true);
                
                $base_expr = ($split . $prevPercent . $base_expr . $endPercent . $split);
            }
            else
            {
            	$base_expr = ($split . $this->_08_db->escape($base_expr) . $split);
            }
        }
        elseif (0 === strpos($base_expr, '"'))
        {
            $base_expr = $this->escapeBaseExpr($base_expr, '"');
        }
        else
        {
        	$base_expr = $this->_08_db->escape($base_expr);
        }
        
        return $base_expr;
    }
    
    public function __construct($parsed = false) 
    {
        $this->_08_db = _08_factory::getDBO();
       # parent::__construct($parsed);
    }
    
    public function setter($name, $value)
    {
        if (property_exists($this, $name))
        {
            $this->$name = $value;            
        }
    }
}