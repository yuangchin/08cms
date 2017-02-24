<?php
/**
 * Array核心公共类
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2013 08CMS, Inc. All rights reserved.
 */

class cls_Array
{
     /**
     * 在 haystack 中搜索 needle，如果没有设置 strict 则使用宽松的比较。
     * 该函数可以检测二维以上的数组，如果想检测一维数组请使用原生的in_array函数
     * @link http://docs.php.net/manual/zh/function.in-array.php
     *
     * @param mixed $needle   待搜索的值或数组
     * @param array $haystack 要搜索的数组
     * @param int   $strict   如果第三个参数 strict 的值为 TRUE 或为 1 则 in_array()
     *                        函数还会检查 needle 的类型是否和 haystack 中的相同,
     *                        如果该值为2时，以strpos方式遍历判断相似的值。
     *
     * @static
     * @since 1.0
     */
    public static function _in_array( $needle, array $haystack, $strict = 0 )
    {
        # 用strpos方式遍历判断数组1中是否存在数组2相似的值
        if ( is_array($needle) && $strict === 2 )
        {
            foreach ($needle as $need) 
            {                
                if($return = self::_in_array($need, $haystack, $strict))
                {
                    return $return;
                }
            }
        }
        
        # 遍历查找 $haystack
        foreach($haystack as $value)
        {
            if( is_array($value) )
            {
                if($return = self::_in_array($needle, $value, $strict))
                {
                    return $return;
                }
            }

            if ( $strict === 1 || $strict === true )
            {
                if( $value === $needle ) return true;
            }
            else if ($strict === 2)
            {
            	if( false !== @strpos($value, $needle) ) return true;
            }
            else
            {
                if( $value == $needle ) return true;
            }
        }

        return false;
    }

    /**
     * 在一个数组中增加一个元素，如果该数组为多维则会在每一维增加
     * 
     * @param array  $array 要增加的数组对象
     * @param string $key   要增加的键
     * @param string $value 要增加的值
     * @param int    $Layer 要处理的维数
     *                      如果$Layer = 0 或者$Layer大于数组维数时则整个数组都会处理
     *                      如果该值为正时（如：$Layer = 2时只处理第2维）
     *                      如果该值为负时（如：$Layer = -2时会过滤第2维不处理）
     * 
     * @since 1.0
     */ 
	public static function _array_push( array &$array, $key = '', $value, $Layer = 0 )
    {
        static $num = 1;
        $LayerAbs = abs($Layer);

		if(
			!$Layer || 
			(($Layer < 0) && ($LayerAbs != $num)) || 
			(($Layer > 0) && ($LayerAbs == $num))
		) 
		{
			empty($key) ? ($array[] = $value) : ($array[$key] = $value);
		}
		++$num; 
        foreach($array as &$v)
        {
            if(is_array($v))
			{
				self::_array_push($v, $key, $value, $Layer);
				--$num;
			}
        }  
    }
    
    /**
     * 遍历给对象设置指定的键值，如果该键不存在则自动创建
     * 
     * @param object $object 要增加的对象
     * @param string $key    元素键名
     * @param mexid  $value  元素值
     */ 
    public static function setObjectDOM(&$object, $key, $value)
    {
        foreach($object as &$v)
        {
            $v->$key = $value;
        }
    }
	
    /**
     * 按数组中指定键的值，对数组进行排序
     * 
     * @param array  $array 	要排序的数组
     * @param string $orderkey  指定排序的键，以其值排序
     * @param bool   $keepkey 	数字键名是否需要保持不变
     */ 
    public static function _array_multisort(array &$array,$orderkey = 'vieworder',$keepkey = false){
        if(!is_array($array) || empty($array) || !function_exists('array_multisort')) return;
        foreach($array as $k => $v){
            $vorder[$k] = $array[$k][$orderkey] = empty($v[$orderkey]) ? 0 : $v[$orderkey];
            $eorder[$k] = $k;
            if($keepkey) $array[$k]['_key'] = $k;
        }
        array_multisort($vorder,SORT_ASC,$eorder,SORT_ASC,$array);
        if($keepkey){
            $na = array();
            foreach($array as $k => $v){
                $key = $v['_key'];
                unset($v['_key']);
                $na[$key] = $v;
            }
            $array = $na;
        }
    }
      
    /**
     * 对数组按字符数排序（注：数组与数组之间不排序）
     * 
     * @param array $array 要排序的数组（支持多维）
     */
    public static function _array_uasort( array &$array, $function = '__numberOfCharactersCmp' )
    {
        uasort($array, array(__CLASS__, $function));
    }
    
    /**
     * 按字符数比较
     */
    private static function __numberOfCharactersCmp( &$a, &$b )
    {
        if ( is_array($a) )
        {
            return self::_array_uasort($a);
        }
        
        if ( is_array($b) )
        {
            return self::_array_uasort($b);
        }
        
        $lenA = strlen( (string) $a );
        $lenB = strlen( (string) $b );
        if ($lenA == $lenB)
        {
            return 0;
        }
        
        return ($lenA > $lenB) ? -1 : 1;
    }
    
    /**
     * 把一个多维数组转成一维
     * 
     * @param  array $array        要转换的多维数组
     * @param  bool  $retentionKey 是否保留键值，TRUE为保留，FALSE为不保留
     * @return array               返回已经转换的一维数组
     * 
     * @since  nv50
     */
    public static function _array_multi_to_one ( array $arrays, $retentionKey = false )
    {
        static $_one_array = array();
        foreach($arrays as $key => $array)
        {
            if ( is_array($array) )
            {
                self::_array_multi_to_one($array);
            }
            else
            {
                if ( $retentionKey )
                {
                    $_one_array[$key] = $array;
                }
                else
                {
                	$_one_array[] = $array;
                }
            	
            }
        }
        
        return $_one_array;
    }
      
    /**
    * 对一个数组反引用
    * 类似于： array_map('stripslashes', $array);
    *  
    * @param array $array  要反引用的数组
    * 
    * @since nv50
    */ 
    public static function array_stripslashes(&$array)
    {
        if(is_array($array))
        {
            foreach($array as &$value)
            {
                self::array_stripslashes($value);
            }
        }
        else 
        {
            $array = stripslashes($array);
        }
    }
	
    /**
     * 获取数组的维数
     * 
     * @param  array $array 要获取的数组
     * @return int          返回数组的维数
     * @since  nv50
     */
    public static function array_dimension(array $array)
    {
        $dimension = 0;
        if ( empty($array) )
        {
            return $dimension;
        }
        
        foreach ($array as $value) 
        {
            if ( is_array($value) )
            {
                # 判断子数组维数
                $sonArrayDimension = self::array_dimension($value);
                if ( $sonArrayDimension > $dimension )
                {
                    $dimension = $sonArrayDimension;
                }
            }
        }
        
        return $dimension + 1;
    }
    
    /**
     * 获取数组偏移元素值，与SQL的Limit类似，注：获取后Key会以0开始的数字键重置
     *
     * @param  array $array 要获取的数组
     * @param  int   $limit 要获取的开始位置
     * @param  int   offset 要取的个数
     * @return array $IteratorParams 返回获取后的元素值，如果偏移量不存在时返回null
     *
     * @since  nv50
     */
    public static function limit( array $array, $limit, $offset = 0, $reservedKEY = false)
    {
        if ( empty($offset) )
        {
        	$offset = $limit;
            $limit = 0;
        }

        try
        {
            $arrayIterator = new ArrayIterator($array);
            $paramsIterator = new LimitIterator($arrayIterator, (int) $limit, (int) $offset);
            $IteratorParams = array();
            foreach ( $paramsIterator as $key => $param )
            {
                if ($reservedKEY)
                {
                    $IteratorParams[$key] = $param;
                }
                else
                {
                	$IteratorParams[] = $param;
                }            	
            }
        }
        catch (OutOfBoundsException $error)
        {
            $IteratorParams = array();
        }

        return $IteratorParams;
    }
	
	/**
     * 根据$Key读取数组中的值			
     * @param  array  		$array 				源数组
     * @param  string  		$Key 				键名，支持'xx.kk.dd'得到$array['xx']['kk']['dd']
     */
	public static function Get($array = array(),$Key = ''){
		if(!is_array($array)) return NULL;
		if(!($KeyArray = self::ParseKey($Key))){
			return NULL;
		}
		foreach($KeyArray as $k){
			$array = isset($array[$k]) ? $array[$k] : NULL;
		}
		return $array;
    }
	
	/**
     * 根据$Key设置数组中的值
     * @param  string  		$Key 				键名，支持'xx.kk.dd'为$array['xx']['kk']['dd']赋值
     * @param  string  		$value 值			需要设置的值
     * @param  array  		$array 				源数组
    */
	public static function Set(&$array,$Key = '',$Value = 0){
		if(!is_array($array)) return;
		if(!($KeyArray = self::ParseKey($Key))) return;
		$level = count($KeyArray) - 1;
		foreach($KeyArray as $k => $v){
			if($k < $level){
				$array[$v] = isset($array[$v]) && is_array($array[$v]) ? $array[$v] : array();
				$array = &$array[$v];
			}else{
				$array[$v] = $Value;
			}
		}
    }
	
	/**
     * 将组合键名(以'.'连接)分解为数组
     * @param  string  $key			键名字串
     * @param  string  $AllowDot	是否允许包含连接符'.'
     */
    public static function ParseKey($Key = ''){
        $Key = preg_replace('/[^\w\.]/', '', (string)$Key);
		if($Key === '') return false;
		return explode('.',$Key);
    }
	
    
    /**
     * 将回调函数作用到给定数组的单元上
     * 功能类似于：array_map 详情请查看：{@link http://www.php.net/manual/zh/function.array-map.php}
     * 区别在于{@see self::map}支持单元上也有数组
     * 
     * @param  mixed $params   传递给回调函数的参数
     * @return mixed           返回回调函数处理后的返回值
     * 
     * @since  nv50
     */
    public static function map()
    {
        $params = func_get_args();
        if ( is_array($params[1]) )
        {
            $_params = $params;
            foreach ( $params[1] as &$param ) 
            {
                $_params[1] = $param;
                $param = call_user_func_array( array('self', 'map'), $_params );
            }
        
            $params = $params[1];
        }
        else
        {
            $function = $params[0];
            unset($params[0]);
        	$params = call_user_func_array( $function, $params );
        }
        
        return $params;
    }
}