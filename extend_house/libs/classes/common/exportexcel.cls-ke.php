<?php
/**
 * 数据导出到excel类
 * 链接上传递chid/cuid/aid  
   eg:?entry=$entry$extend_str&chid=$chid&cuid=$cuid&aid=$aid
 *
 * 根据传参，可显示文档模型字段以及交互字段 或  模型字段  或 交互字段(字段中不含datatype为image、images、map、htmltext的字段)
 *
 * 处理数据，导出excel文件 
 */
defined('M_COM') || exit('No Permission');
class cls_exportexcel extends cls_exportexcels{	
    /**
     *构造完整的sql
     *@param  string  $filter   列表的搜索条件，即filterstr
     *@param  limit   $limit    sql搜索数据库条数限制
     *@param  data    $fmdata  	表单传递的字段数据
     *@return string  			完成的查询语句
     */
    protected function contruct_full_sql($where_str,$limit,$fmdata){
        global $tblprefix,$chid,$cuid,$aid,$mid;
        $chid = empty($chid) ? 0 : max(1,intval($chid));
        $cuid = empty($cuid) ? 0 : max(1,intval($cuid));
        $aid = empty($aid) ? 0 : max(1,intval($aid));
        $mid = empty($mid) ? 0 : max(1,intval($mid));
        $_select_str = '';
        $_from_str = '';
        $_where_str = '';
        $table_header_arr = array();
        $table_content_arr = array();
    
        $_tbl_name_arr = array_keys($fmdata);
        $_tblpre_pre = '';//用于循环数组时，记录前一个表的别名
        $_tblpre_arr = array();//用于存放循环数组时出现过的别名
        for($i =0;$i<count($_tbl_name_arr);$i++){
            //$_tblpre_cur    用于循环数组时，记录当前表的别名
            $_tblpre_cur = strstr($_tbl_name_arr[$i],'archives')?(strstr($_tbl_name_arr[$i],'_')? "c" : "a"):"cu";
            if($i == 0){
                $_tblpre_pre = $_tblpre_cur;
                $_from_str .= "{$tblprefix}".$_tbl_name_arr[$i]." as $_tblpre_cur";
            }else{
                $_from_str .= " INNER JOIN {$tblprefix}".$_tbl_name_arr[$i]." as $_tblpre_cur ON ".$_tblpre_cur.".aids LIKE CONCAT('%',".$_tblpre_pre.".aid,'%')";
            }
        }
    
        foreach($fmdata as $k => $v){
            $_tblpre = '';
            if(strstr($k,'archives')){
                $_tblpre = strstr($k,'_')? "c" : "a";
            }else{
                $_tblpre = "cu";
            }
            $_tblpre_arr[] = $_tblpre;
            foreach($v as $key => $val){
                $table_header_arr[] = $val;
                $_select_str .= ",".$_tblpre.".$key ";
            }
        }
    
        //排序
        $orderby_str = '';
        if(in_array('a',$_tblpre_arr)){
            $orderby_str = " a.aid DESC ";
        }elseif(in_array('c',$_tblpre_arr)){
            $orderby_str = " c.aid DESC ";
        }
        if(in_array('cu',$_tblpre_arr)){
            $orderby_str = " cu.cid DESC ";
        }
    
        foreach(array('aid','chid') as $k){
            if($$k){
                if(in_array('a',$_tblpre_arr) && in_array($k,array('aid','chid'))){
                    $_where_str .=  " AND a.$k='".$$k."' ";
                }
                if(in_array('cu',$_tblpre_arr) && in_array($k,array('aid'))){
                    $_where_str .=  " AND cu.aids='".$$k."' ";
                }
            }
        }
        //处理搜索条件，组成sql
        $_where_str .= $this->deal_width_filterstr($where_str,$_tblpre_arr);
    
        //会员中心
//         ($this->mc && !empty($mid)) && $_where_str .= " AND a.mid='".$mid."' ";
//         print_r("SELECT ".substr($_select_str,1)." FROM $_from_str ORDER BY ".$_tblpre_pre.".aid");
        return "SELECT ".substr($_select_str,1)." FROM $_from_str";
    }
}
