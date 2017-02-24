<?php
defined('M_COM') || exit('No Permission');
$fields118 = array (
  'subject' => 
  array (
    'ename' => 'subject',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '房源标题',
    'issystem' => '1',
    'iscustom' => '0',
    'iscommon' => '1',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives71',
    'length' => '255',
    'vieworder' => '0',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '很重要,好的标题（至少包含 路段、小区名、户型 便于用户搜索）能有效提升房源点击率',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '1',
    'notnull' => '1',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '1',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'lpmc' => 
  array (
    'ename' => 'lpmc',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '小区名称',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '1',
    'issearch' => '2',
    'available' => '1',
    'tbl' => 'archives71',
    'length' => '255',
    'vieworder' => '1',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '可输入小区名称或地址进行搜索',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '1',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '1',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'thumb' => 
  array (
    'ename' => 'thumb',
    'datatype' => 'image',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '缩略图',
    'issystem' => '0',
    'iscustom' => '0',
    'iscommon' => '1',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives71',
    'length' => '0',
    'vieworder' => '10',
    'rpid' => '1',
    'wmid' => '0',
    'guide' => '留空时自动来自房源描述的图片',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'mj' => 
  array (
    'ename' => 'mj',
    'datatype' => 'float',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '建筑面积',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '1',
    'issearch' => '2',
    'available' => '1',
    'tbl' => 'archives71',
    'length' => '0',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '单位：M<sup>2</sup>',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'dj' => 
  array (
    'ename' => 'dj',
    'datatype' => 'float',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '单价',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '1',
    'issearch' => '2',
    'available' => '1',
    'tbl' => 'archives71',
    'length' => '0',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '单位：元/M<sup>2</sup>,自动由总价和面积计算出来',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'zj' => 
  array (
    'ename' => 'zj',
    'datatype' => 'float',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '总价',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '1',
    'issearch' => '2',
    'available' => '1',
    'tbl' => 'archives71',
    'length' => '0',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '万元  [留空或0表示面议]',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'dt' => 
  array (
    'ename' => 'dt',
    'datatype' => 'map',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '地图',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '1',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives71',
    'length' => '0',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'szlc' => 
  array (
    'ename' => 'szlc',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '所在楼层',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives_118',
    'length' => '10',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '1',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'zlc' => 
  array (
    'ename' => 'zlc',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '总楼层',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives_118',
    'length' => '10',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '1',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'zxcd' => 
  array (
    'ename' => 'zxcd',
    'datatype' => 'select',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '装修程度',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '1',
    'available' => '1',
    'tbl' => 'archives_118',
    'length' => '0',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '1=毛坯
2=简易装修
3=中档装修
4=高档装修
5=豪华装修',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'cx' => 
  array (
    'ename' => 'cx',
    'datatype' => 'select',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '朝向',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '1',
    'available' => '1',
    'tbl' => 'archives_118',
    'length' => '0',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '1=南
2=北
3=东
4=西
5=东南
6=东北
7=西南
8=西北
9=南北
10=东西',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'content' => 
  array (
    'ename' => 'content',
    'datatype' => 'htmltext',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '房源描述',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives_118',
    'length' => '0',
    'vieworder' => '10',
    'rpid' => '1',
    'wmid' => '29',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '1',
    'editor_height' => '300',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'address' => 
  array (
    'ename' => 'address',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '地址',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '2',
    'available' => '1',
    'tbl' => 'archives_118',
    'length' => '255',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '1',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '1',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'fl' => 
  array (
    'ename' => 'fl',
    'datatype' => 'select',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '房龄',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '2',
    'available' => '1',
    'tbl' => 'archives_118',
    'length' => '10',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '-1=不详
2015=2015 年
2014=2014 年
2013=2013 年
2012=2012 年
2011=2011 年
2010=2010 年
2009=2009 年
2008=2008 年
2007=2007 年
2006=2006 年
2005=2005 年
2004=2004 年
2003=2003 年
2002=2002 年
2001=2001 年
2000=2000 年
1999=1999 年
1998=1998 年
1997=1997 年
1996=1996 年
1995=1995 年
1994=1994 年
1993=1993 年
1992=1992 年
1991=1991 年
1990=1990 年
1989=1989 年
1988=1988 年
1987=1987 年
1986=1986 年
1985=1985 年
1984=1984 年
1983=1983 年
1982=1982 年
1981=1981 年
1980=1980 年
1979=1979 年
1978=1978 年
1977=1977 年
1976=1976 年
1975=1975 年
1974=1974 年
1973=1973 年
1972=1972 年
1971=1971 年
1970=1970 年
1969=1969 年
1968=1968 年
1967=1967 年
1966=1966 年
1965=1965 年
1964=1964 年
1963=1963 年
1962=1962 年
1961=1961 年
1960=1960 年
1959=1959 年
1958=1958 年
1957=1957 年
1956=1956 年
1955=1955 年
1954=1954 年
1953=1953 年
1952=1952 年
1951=1951 年',
    'fromcode' => '0',
    'nohtml' => '1',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'xingming' => 
  array (
    'ename' => 'xingming',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '联系人',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '2',
    'available' => '1',
    'tbl' => 'archives_118',
    'length' => '50',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '1',
    'notnull' => '1',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'lxdh' => 
  array (
    'ename' => 'lxdh',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '联系人电话',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '2',
    'available' => '1',
    'tbl' => 'archives_118',
    'length' => '50',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '例：13423045276',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '1',
    'notnull' => '1',
    'mlimit' => '',
    'regular' => '/^\\s*\\d{3,4}[-]?\\d{7,8}\\s*$/',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'fwjg' => 
  array (
    'ename' => 'fwjg',
    'datatype' => 'select',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '房屋结构',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '0',
    'issearch' => '1',
    'available' => '1',
    'tbl' => 'archives_118',
    'length' => '0',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '1=平层
2=复式
3=跃层
4=错层
5=开间',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'ts' => 
  array (
    'ename' => 'ts',
    'datatype' => 'mselect',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '特色',
    'issystem' => '0',
    'iscustom' => '1',
    'iscommon' => '1',
    'issearch' => '1',
    'available' => '1',
    'tbl' => 'archives71',
    'length' => '10',
    'vieworder' => '10',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => '',
    'vdefault' => '',
    'innertext' => '1=高回报率
2=底层沿街
3=近地铁口
4=知名商户入驻
5=繁华地段
6=独栋
7=低价急售
8=可分隔两层',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '1',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'keywords' => 
  array (
    'ename' => 'keywords',
    'datatype' => 'text',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '关键词',
    'issystem' => '0',
    'iscustom' => '0',
    'iscommon' => '1',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives71',
    'length' => '100',
    'vieworder' => '99',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => 'seo关键词',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '1',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
  'abstract' => 
  array (
    'ename' => 'abstract',
    'datatype' => 'multitext',
    'type' => 'a',
    'tpid' => '118',
    'cname' => '描述',
    'issystem' => '0',
    'iscustom' => '0',
    'iscommon' => '1',
    'issearch' => '0',
    'available' => '1',
    'tbl' => 'archives71',
    'length' => '0',
    'vieworder' => '99',
    'rpid' => '0',
    'wmid' => '0',
    'guide' => 'seo描述',
    'vdefault' => '',
    'innertext' => '',
    'fromcode' => '0',
    'nohtml' => '0',
    'notnull' => '0',
    'mlimit' => '',
    'regular' => '',
    'min' => '',
    'max' => '',
    'mode' => '0',
    'coid' => '0',
    'cnmode' => '0',
    'filter' => '0',
    'editor_height' => '0',
    'auto_page_size' => '5',
    'auto_compression_width' => '0',
  ),
) ;