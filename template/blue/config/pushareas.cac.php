<?php
defined('M_COM') || exit('No Permission');
$pushareas = array (
  'push_111' => 
  array (
    'paid' => 'push_111',
    'cname' => '图文推荐',
    'ptid' => 11,
    'sourcetype' => 'archives',
    'sourceid' => '109',
    'smallids' => '',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{icon}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 6,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_112' => 
  array (
    'paid' => 'push_112',
    'cname' => '常见问题',
    'ptid' => '11',
    'sourcetype' => 'archives',
    'sourceid' => '109',
    'smallids' => '',
    'smallson' => '0',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => 'return cls_catalog::p_ccid({caid},0,1);',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_113' => 
  array (
    'paid' => 'push_113',
    'cname' => '入门流程',
    'ptid' => 11,
    'sourcetype' => 'archives',
    'sourceid' => '109',
    'smallids' => '',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{icon}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 5,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_118' => 
  array (
    'paid' => 'push_118',
    'cname' => '视频幻灯_1',
    'ptid' => 10,
    'sourcetype' => 'archives',
    'sourceid' => '12',
    'smallids' => '',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '',
      ),
      'classid2' => 
      array (
        'from' => '',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 5,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_130' => 
  array (
    'paid' => 'push_130',
    'cname' => '楼盘',
    'ptid' => 14,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => '',
    'smallson' => 0,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
        'nodemode' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '',
      ),
      'classid2' => 
      array (
        'from' => '',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_131' => 
  array (
    'paid' => 'push_131',
    'cname' => '二手房',
    'ptid' => 14,
    'sourcetype' => 'archives',
    'sourceid' => '3',
    'smallids' => ',3,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
        'nodemode' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '',
      ),
      'classid2' => 
      array (
        'from' => '',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_132' => 
  array (
    'paid' => 'push_132',
    'cname' => '出租',
    'ptid' => 14,
    'sourcetype' => 'archives',
    'sourceid' => '2',
    'smallids' => ',4,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
        'nodemode' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '',
      ),
      'classid2' => 
      array (
        'from' => '',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_133' => 
  array (
    'paid' => 'push_133',
    'cname' => '幻灯片推荐',
    'ptid' => 15,
    'sourcetype' => 'archives',
    'sourceid' => '112',
    'smallids' => ',600,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_136' => 
  array (
    'paid' => 'push_136',
    'cname' => '内容页推荐',
    'ptid' => '15',
    'sourcetype' => 'archives',
    'sourceid' => '112',
    'smallids' => ',600,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_28' => 
  array (
    'paid' => 'push_28',
    'cname' => '头条区块',
    'ptid' => 3,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',1,',
    'smallson' => 1,
    'sourcesql' => '({pre}ccid1<>0)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 20,
    'mspace' => '0',
    'orderspace' => 2,
    'copyspace' => 2,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_29' => 
  array (
    'paid' => 'push_29',
    'cname' => '新房团购',
    'ptid' => 3,
    'sourcetype' => 'archives',
    'sourceid' => '5',
    'smallids' => ',5,',
    'smallson' => 1,
    'sourcesql' => '({pre}ccid1<>0 AND ({pre}enddate>\'{timestamp}\' or {pre}enddate=0))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 5,
    'mspace' => '0',
    'orderspace' => 2,
    'copyspace' => 2,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_30' => 
  array (
    'paid' => 'push_30',
    'cname' => '精品楼盘',
    'ptid' => 3,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 0,
    'sourcesql' => '{pre}ccid1<>0 AND {pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 12,
    'mspace' => '0',
    'orderspace' => 2,
    'copyspace' => 2,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_31' => 
  array (
    'paid' => 'push_31',
    'cname' => '优惠活动',
    'ptid' => '3',
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',555,',
    'smallson' => '1',
    'sourcesql' => '({pre}ccid1<>0)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '2',
    'copyspace' => '2',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_32' => 
  array (
    'paid' => 'push_32',
    'cname' => '工程进度',
    'ptid' => '3',
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',557,',
    'smallson' => '1',
    'sourcesql' => '({pre}ccid1<>0)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '2',
    'copyspace' => '2',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_33' => 
  array (
    'paid' => 'push_33',
    'cname' => '楼市快递',
    'ptid' => '3',
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',14,15,16,514,',
    'smallson' => '1',
    'sourcesql' => '({pre}ccid1<>0)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '20',
    'mspace' => '0',
    'orderspace' => '2',
    'copyspace' => '2',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_34' => 
  array (
    'paid' => 'push_34',
    'cname' => '新房问答',
    'ptid' => 3,
    'sourcetype' => 'archives',
    'sourceid' => '106',
    'smallids' => ',517,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_35' => 
  array (
    'paid' => 'push_35',
    'cname' => '看房日记',
    'ptid' => '3',
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',556,',
    'smallson' => '1',
    'sourcesql' => '{pre}ccid1<>0',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '2',
    'copyspace' => '2',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_36' => 
  array (
    'paid' => 'push_36',
    'cname' => '二手资讯',
    'ptid' => '3',
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',21,',
    'smallson' => '1',
    'sourcesql' => '{pre}ccid1<>0',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '2',
    'copyspace' => '2',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_37' => 
  array (
    'paid' => 'push_37',
    'cname' => '中介二手房',
    'ptid' => 3,
    'sourcetype' => 'archives',
    'sourceid' => '3',
    'smallids' => ',3,',
    'smallson' => 1,
    'sourcesql' => '({pre}ccid1<>0 and {pre}mchid=2)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 2,
    'copyspace' => 2,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_38' => 
  array (
    'paid' => 'push_38',
    'cname' => '个人二手房',
    'ptid' => 3,
    'sourcetype' => 'archives',
    'sourceid' => '3',
    'smallids' => ',3,',
    'smallson' => 1,
    'sourcesql' => '({pre}ccid1<>0 and {pre}mchid=1)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 2,
    'copyspace' => 2,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_39' => 
  array (
    'paid' => 'push_39',
    'cname' => '政策法规',
    'ptid' => '3',
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',17,',
    'smallson' => '1',
    'sourcesql' => '({pre}ccid1<>0)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '2',
    'copyspace' => '2',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_40' => 
  array (
    'paid' => 'push_40',
    'cname' => '推荐经纪人☆',
    'ptid' => 3,
    'sourcetype' => 'members',
    'sourceid' => '2',
    'smallids' => '',
    'smallson' => '0',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{xingming}',
      ),
      'url' => 
      array (
        'from' => '{mspacehome}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{image}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{szqy}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 2,
    'copyspace' => 2,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => 'push_load_mem.php',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_41' => 
  array (
    'paid' => 'push_41',
    'cname' => '租房资讯',
    'ptid' => '3',
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',20,',
    'smallson' => '1',
    'sourcesql' => '{pre}ccid1<>0',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '2',
    'copyspace' => '2',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_42' => 
  array (
    'paid' => 'push_42',
    'cname' => '中介出租',
    'ptid' => 3,
    'sourcetype' => 'archives',
    'sourceid' => '2',
    'smallids' => ',4,',
    'smallson' => 1,
    'sourcesql' => '({pre}ccid1<>0 and {pre}mchid=2)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 2,
    'copyspace' => 2,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_43' => 
  array (
    'paid' => 'push_43',
    'cname' => '个人出租',
    'ptid' => 3,
    'sourcetype' => 'archives',
    'sourceid' => '2',
    'smallids' => ',4,',
    'smallson' => 0,
    'sourcesql' => '({pre}ccid1<>0 and {pre}mchid=1)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 2,
    'copyspace' => 2,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_44' => 
  array (
    'paid' => 'push_44',
    'cname' => '人物专访',
    'ptid' => '3',
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',18,',
    'smallson' => '1',
    'sourcesql' => '{pre}ccid1<>0',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '2',
    'copyspace' => '2',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_45' => 
  array (
    'paid' => 'push_45',
    'cname' => '经纪公司☆',
    'ptid' => 3,
    'sourcetype' => 'members',
    'sourceid' => '3',
    'smallids' => '',
    'smallson' => '0',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{cmane}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{mspacehome}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{image}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{szqy}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 2,
    'copyspace' => 2,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => 'push_load_mem.php',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_82' => 
  array (
    'paid' => 'push_82',
    'cname' => '经纪人_1_2☆',
    'ptid' => 7,
    'sourcetype' => 'members',
    'sourceid' => '2',
    'smallids' => '',
    'smallson' => '0',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{xingming}',
      ),
      'url' => 
      array (
        'from' => '{mspacehome}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{image}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => 'push_load_mems.php',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_85' => 
  array (
    'paid' => 'push_85',
    'cname' => '头条区块_1',
    'ptid' => 6,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',504,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_86' => 
  array (
    'paid' => 'push_86',
    'cname' => '公司动态_1',
    'ptid' => '6',
    'sourcetype' => 'archives',
    'sourceid' => '104',
    'smallids' => ',512,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{marcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '1',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_87' => 
  array (
    'paid' => 'push_87',
    'cname' => '家装团购_1',
    'ptid' => '6',
    'sourcetype' => 'archives',
    'sourceid' => '105',
    'smallids' => ',515,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '1',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_88' => 
  array (
    'paid' => 'push_88',
    'cname' => '装修公司_1☆',
    'ptid' => '6',
    'sourcetype' => 'members',
    'sourceid' => '11',
    'smallids' => '',
    'smallson' => '0',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{companynm}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{mspacehome}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{pic}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '1',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => 'push_load_mems.php',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_89' => 
  array (
    'paid' => 'push_89',
    'cname' => '热卖商品_1',
    'ptid' => 6,
    'sourcetype' => 'archives',
    'sourceid' => '103',
    'smallids' => ',513,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{marcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid31}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_90' => 
  array (
    'paid' => 'push_90',
    'cname' => '品牌商家_1☆',
    'ptid' => '6',
    'sourcetype' => 'members',
    'sourceid' => '12',
    'smallids' => '',
    'smallson' => '0',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{companynm}',
      ),
      'url' => 
      array (
        'from' => '{mspacehome}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{pic}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '1',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => 'push_load_mems.php',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_91' => 
  array (
    'paid' => 'push_91',
    'cname' => '设计师_1',
    'ptid' => 6,
    'sourcetype' => 'archives',
    'sourceid' => '101',
    'smallids' => ',510,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{marcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid32}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_92' => 
  array (
    'paid' => 'push_92',
    'cname' => '装修案例_1',
    'ptid' => '6',
    'sourcetype' => 'archives',
    'sourceid' => '102',
    'smallids' => ',511,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{marcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid32}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '1',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_93' => 
  array (
    'paid' => 'push_93',
    'cname' => '装修课堂_1',
    'ptid' => '6',
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',507,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_94' => 
  array (
    'paid' => 'push_94',
    'cname' => '装修日记_1',
    'ptid' => '6',
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',508,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_95' => 
  array (
    'paid' => 'push_95',
    'cname' => '视频头条_1',
    'ptid' => 10,
    'sourcetype' => 'archives',
    'sourceid' => '12',
    'smallids' => ',30,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_96' => 
  array (
    'paid' => 'push_96',
    'cname' => '推荐视频_1',
    'ptid' => '10',
    'sourcetype' => 'archives',
    'sourceid' => '12',
    'smallids' => ',30,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => 'return cls_catalog::p_ccid({caid},0,1);',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_98' => 
  array (
    'paid' => 'push_98',
    'cname' => '精彩回答_1',
    'ptid' => '8',
    'sourcetype' => 'archives',
    'sourceid' => '106',
    'smallids' => ',516,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid35}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_gdxw' => 
  array (
    'paid' => 'push_gdxw',
    'cname' => '滚动新闻_1',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',502,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => 0,
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 20,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_hdp' => 
  array (
    'paid' => 'push_hdp',
    'cname' => '幻灯片',
    'ptid' => 12,
    'sourcetype' => 'archives',
    'sourceid' => '14',
    'smallids' => '',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => 0,
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 6,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_lfzxgs' => 
  array (
    'paid' => 'push_lfzxgs',
    'cname' => '量房装修公司_3',
    'ptid' => 6,
    'sourcetype' => 'members',
    'sourceid' => '11',
    'smallids' => '',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{companynm}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{mspacehome}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{pic}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => 0,
    'autopush' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => 'push_load_mems.php',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_syhdp' => 
  array (
    'paid' => 'push_syhdp',
    'cname' => '幻灯片',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',1,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => 0,
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '0',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 20,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_xqf' => 
  array (
    'paid' => 'push_xqf',
    'cname' => '学区房学校推荐',
    'ptid' => 7,
    'sourcetype' => 'archives',
    'sourceid' => '8',
    'smallids' => ',26,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => 0,
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_119' => 
  array (
    'paid' => 'push_119',
    'cname' => '焦点推荐_3',
    'ptid' => '10',
    'sourcetype' => 'archives',
    'sourceid' => '12',
    'smallids' => '',
    'smallson' => '0',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '8',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_121' => 
  array (
    'paid' => 'push_121',
    'cname' => '幻灯片',
    'ptid' => 13,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',1,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
        'nodemode' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 4,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_61' => 
  array (
    'paid' => 'push_61',
    'cname' => '头条区块_1',
    'ptid' => 5,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',1,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_tjlp' => 
  array (
    'paid' => 'push_tjlp',
    'cname' => '推荐楼盘_写字楼_2',
    'ptid' => 16,
    'sourcetype' => 'archives',
    'sourceid' => '115',
    'smallids' => ',612,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '1',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_zxtt' => 
  array (
    'paid' => 'push_zxtt',
    'cname' => '资讯头条',
    'ptid' => 13,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',1,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
        'nodemode' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '1',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '0',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 99,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_114' => 
  array (
    'paid' => 'push_114',
    'cname' => '幻灯片',
    'ptid' => 5,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',1,',
    'smallson' => 1,
    'sourcesql' => 'thumb<>\'\'',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '2',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_116' => 
  array (
    'paid' => 'push_116',
    'cname' => '推荐图文',
    'ptid' => 12,
    'sourcetype' => 'archives',
    'sourceid' => '14',
    'smallids' => '',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '2',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 6,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_zxlp' => 
  array (
    'paid' => 'push_zxlp',
    'cname' => '最新楼盘_写字楼_2',
    'ptid' => 16,
    'sourcetype' => 'archives',
    'sourceid' => '115',
    'smallids' => ',612,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '2',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_123' => 
  array (
    'paid' => 'push_123',
    'cname' => '楼盘推荐',
    'ptid' => 13,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 1,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
        'nodemode' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '3',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 3,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_125' => 
  array (
    'paid' => 'push_125',
    'cname' => '打折优惠',
    'ptid' => 13,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 1,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
        'nodemode' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '3',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 3,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_jjkp' => 
  array (
    'paid' => 'push_jjkp',
    'cname' => '即将开盘_写字楼_2',
    'ptid' => 16,
    'sourcetype' => 'archives',
    'sourceid' => '115',
    'smallids' => ',612,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '3',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_lphdp' => 
  array (
    'paid' => 'push_lphdp',
    'cname' => '幻灯片_写字楼楼盘_3',
    'ptid' => 16,
    'sourcetype' => 'archives',
    'sourceid' => '7',
    'smallids' => ',7,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'pid3' => 
      array (
        'from' => '{pid3}',
        'refresh' => '1',
      ),
      'pid36' => 
      array (
        'from' => '{pid36}',
        'refresh' => '1',
      ),
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '3',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => 'pushs_huxing.php',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_lptk' => 
  array (
    'paid' => 'push_lptk',
    'cname' => '楼盘图库_写字楼_3',
    'ptid' => 16,
    'sourcetype' => 'archives',
    'sourceid' => '7',
    'smallids' => ',7,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'pid3' => 
      array (
        'from' => '{pid3}',
        'refresh' => '1',
      ),
      'pid36' => 
      array (
        'from' => '{pid36}',
        'refresh' => '1',
      ),
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '3',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => 'pushs_huxing.php',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_shoptjlp' => 
  array (
    'paid' => 'push_shoptjlp',
    'cname' => '推荐楼盘_商铺_2',
    'ptid' => 16,
    'sourcetype' => 'archives',
    'sourceid' => '116',
    'smallids' => ',616,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '4',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_126' => 
  array (
    'paid' => 'push_126',
    'cname' => '推荐房源-二手房',
    'ptid' => 13,
    'sourcetype' => 'archives',
    'sourceid' => '3',
    'smallids' => ',3,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'imgnum' => 
      array (
        'from' => '{imgnum}',
        'refresh' => '1',
      ),
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
        'nodemode' => '1',
      ),
      'zj' => 
      array (
        'from' => '{zj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '5',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 3,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '0',
  ),
  'push_gdlp' => 
  array (
    'paid' => 'push_gdlp',
    'cname' => '滚动楼盘',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 0,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'ccid1' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'dj' => 
      array (
        'from' => '{dj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '5',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_jishou' => 
  array (
    'paid' => 'push_jishou',
    'cname' => '急售',
    'ptid' => 13,
    'sourcetype' => 'archives',
    'sourceid' => '3',
    'smallids' => ',3,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'imgnum' => 
      array (
        'from' => '{imgnum}',
        'refresh' => '1',
      ),
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
        'nodemode' => '1',
      ),
      'zj' => 
      array (
        'from' => '{zj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '5',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '0',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 3,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_shopzxlp' => 
  array (
    'paid' => 'push_shopzxlp',
    'cname' => '最新开盘_商铺_2',
    'ptid' => 16,
    'sourcetype' => 'archives',
    'sourceid' => '116',
    'smallids' => ',616,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '5',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_xfhdp' => 
  array (
    'paid' => 'push_xfhdp',
    'cname' => '首页幻灯片_1',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 1,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{ccid31}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '5',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_127' => 
  array (
    'paid' => 'push_127',
    'cname' => '推荐房源-租房',
    'ptid' => '13',
    'sourcetype' => 'archives',
    'sourceid' => '2',
    'smallids' => ',4,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'imgnum' => 
      array (
        'from' => '{imgnum}',
        'refresh' => '1',
      ),
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
        'nodemode' => '1',
      ),
      'zj' => 
      array (
        'from' => '{zj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => '6',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '4',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_hdpy' => 
  array (
    'paid' => 'push_hdpy',
    'cname' => '幻灯片右_1',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 1,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{ccid18}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '6',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_jizu' => 
  array (
    'paid' => 'push_jizu',
    'cname' => '急租',
    'ptid' => 13,
    'sourcetype' => 'archives',
    'sourceid' => '2',
    'smallids' => ',4,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'imgnum' => 
      array (
        'from' => '{imgnum}',
        'refresh' => '1',
      ),
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
        'nodemode' => '1',
      ),
      'zj' => 
      array (
        'from' => '{zj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '6',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 4,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_shopjjlp' => 
  array (
    'paid' => 'push_shopjjlp',
    'cname' => '即将开盘_商铺_2',
    'ptid' => 16,
    'sourcetype' => 'archives',
    'sourceid' => '116',
    'smallids' => ',616,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '6',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_shoplphdp' => 
  array (
    'paid' => 'push_shoplphdp',
    'cname' => '幻灯片_商铺楼盘_3',
    'ptid' => 16,
    'sourcetype' => 'archives',
    'sourceid' => '7',
    'smallids' => ',7,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'pid3' => 
      array (
        'from' => '{pid3}',
        'refresh' => '1',
      ),
      'pid36' => 
      array (
        'from' => '{pid36}',
        'refresh' => '1',
      ),
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '6',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => 0,
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => 'pushs_huxing.php',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_shoplptk' => 
  array (
    'paid' => 'push_shoplptk',
    'cname' => '楼盘图库_商铺_3',
    'ptid' => 16,
    'sourcetype' => 'archives',
    'sourceid' => '7',
    'smallids' => ',7,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'pid3' => 
      array (
        'from' => '{pid3}',
        'refresh' => '1',
      ),
      'pid36' => 
      array (
        'from' => '{pid36}',
        'refresh' => '1',
      ),
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '6',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '0',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => 'pushs_huxing.php',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_54' => 
  array (
    'paid' => 'push_54',
    'cname' => '头条区块_1',
    'ptid' => '4',
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',502,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => '7',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '20',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_jjr' => 
  array (
    'paid' => 'push_jjr',
    'cname' => '经纪人_写字楼出售_2',
    'ptid' => 16,
    'sourcetype' => 'members',
    'sourceid' => '2',
    'smallids' => '',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{xingming}',
      ),
      'url' => 
      array (
        'from' => '{mspacehome}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{image}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '7',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => 'push_load_mems.php',
  ),
  'push_4' => 
  array (
    'paid' => 'push_4',
    'cname' => '精选专题',
    'ptid' => '2',
    'sourcetype' => 'archives',
    'sourceid' => '14',
    'smallids' => ',37,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '1',
    'vieworder' => '10',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_xftj' => 
  array (
    'paid' => 'push_xftj',
    'cname' => '特价楼盘_1',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '107',
    'smallids' => ',559,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '10',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_thzq' => 
  array (
    'paid' => 'push_thzq',
    'cname' => '特惠专区',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 0,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'bdsm' => 
      array (
        'from' => '{bdsm}',
        'refresh' => '1',
      ),
      'ccid1' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'dj' => 
      array (
        'from' => '{dj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '13',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_mxlp' => 
  array (
    'paid' => 'push_mxlp',
    'cname' => '明星楼盘',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 0,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'bdsm' => 
      array (
        'from' => '{bdsm}',
        'refresh' => '1',
      ),
      'ccid1' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'dj' => 
      array (
        'from' => '{dj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '14',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_55' => 
  array (
    'paid' => 'push_55',
    'cname' => '团购楼盘_1',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '5',
    'smallids' => ',5,',
    'smallson' => 1,
    'sourcesql' => '({pre}enddate > \'{timestamp}\'  or {pre}enddate=0)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '15',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_rxlp' => 
  array (
    'paid' => 'push_rxlp',
    'cname' => '热销楼盘',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 0,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'bdsm' => 
      array (
        'from' => '{bdsm}',
        'refresh' => '1',
      ),
      'ccid1' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'dj' => 
      array (
        'from' => '{dj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '15',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_120' => 
  array (
    'paid' => 'push_120',
    'cname' => '看房报名',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '110',
    'smallids' => ',560,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '20',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 2,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_5' => 
  array (
    'paid' => 'push_5',
    'cname' => '新房团购',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '5',
    'smallids' => ',5,',
    'smallson' => 0,
    'sourcesql' => '({pre}enddate > \'{timestamp}\' or {pre}enddate=0)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'yhsm' => 
      array (
        'from' => '{yhsm}',
        'refresh' => '1',
      ),
      'ccid1' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'tgj' => 
      array (
        'from' => '{tgj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '20',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_kfttj' => 
  array (
    'paid' => 'push_kfttj',
    'cname' => '看房团',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '110',
    'smallids' => ',560,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'kfsj' => 
      array (
        'from' => '{kfsj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '20',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 2,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_rmlp' => 
  array (
    'paid' => 'push_rmlp',
    'cname' => '热门楼盘_1',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 0,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'bdsm' => 
      array (
        'from' => '{bdsm}',
        'refresh' => '1',
      ),
      'ccid1' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'dj' => 
      array (
        'from' => '{dj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '20',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_tjftj' => 
  array (
    'paid' => 'push_tjftj',
    'cname' => '特价房',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '107',
    'smallids' => ',559,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '21',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_zyj' => 
  array (
    'paid' => 'push_zyj',
    'cname' => '赚佣金',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '113',
    'smallids' => ',605,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '22',
    'autopush' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_zxkp' => 
  array (
    'paid' => 'push_zxkp',
    'cname' => '最新开盘',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 0,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'bdsm' => 
      array (
        'from' => '{bdsm}',
        'refresh' => '1',
      ),
      'ccid1' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'dj' => 
      array (
        'from' => '{dj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '23',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_rqlp' => 
  array (
    'paid' => 'push_rqlp',
    'cname' => '人气楼盘',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 0,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'bdsm' => 
      array (
        'from' => '{bdsm}',
        'refresh' => '1',
      ),
      'ccid1' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'dj' => 
      array (
        'from' => '{dj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '24',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_7' => 
  array (
    'paid' => 'push_7',
    'cname' => '新房资讯',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',502,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '25',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_xfsp' => 
  array (
    'paid' => 'push_xfsp',
    'cname' => '商铺楼盘_1',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '116',
    'smallids' => ',616,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '25',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 4,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_spcs' => 
  array (
    'paid' => 'push_spcs',
    'cname' => '商铺楼盘',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '116',
    'smallids' => ',616,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '28',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 4,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_xfxzl' => 
  array (
    'paid' => 'push_xfxzl',
    'cname' => '写字楼楼盘_1',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '115',
    'smallids' => ',612,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '30',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 4,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_xzlcs' => 
  array (
    'paid' => 'push_xzlcs',
    'cname' => '写字楼楼盘',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '115',
    'smallids' => ',612,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '30',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 4,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_sydt' => 
  array (
    'paid' => 'push_sydt',
    'cname' => '商业动态',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',607,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '32',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '0',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_18' => 
  array (
    'paid' => 'push_18',
    'cname' => '二手房',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '3',
    'smallids' => ',3,',
    'smallson' => 0,
    'sourcesql' => '(enddate>{timestamp} or enddate=0)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'zj' => 
      array (
        'from' => '{zj}',
        'refresh' => '1',
      ),
      'lpmc' => 
      array (
        'from' => '{lpmc}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '35',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_20' => 
  array (
    'paid' => 'push_20',
    'cname' => '出租',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '2',
    'smallids' => ',4,',
    'smallson' => 0,
    'sourcesql' => '(enddate>{timestamp} or enddate=0)',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'lpmc' => 
      array (
        'from' => '{lpmc}',
        'refresh' => '1',
      ),
      'zj' => 
      array (
        'from' => '{zj}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '38',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 5,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_129' => 
  array (
    'paid' => 'push_129',
    'cname' => '热门小区',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 1,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'2\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '40',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_58' => 
  array (
    'paid' => 'push_58',
    'cname' => '推荐楼盘_2',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 1,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{ccid1}',
        'refresh' => '1',
      ),
      'classid2' => 
      array (
        'from' => '{ccid12}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '40',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_56' => 
  array (
    'paid' => 'push_56',
    'cname' => '最新开盘_2',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 1,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{ccid31}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '41',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_57' => 
  array (
    'paid' => 'push_57',
    'cname' => '即将开盘_2',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '4',
    'smallids' => ',2,',
    'smallson' => 1,
    'sourcesql' => '{pre}aid IN (SELECT aid FROM {tblprefix}archives_4 WHERE (leixing=\'0\' OR leixing=\'1\'))',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{ccid18}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '42',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_tjjjr' => 
  array (
    'paid' => 'push_tjjjr',
    'cname' => '推荐经纪人',
    'ptid' => 2,
    'sourcetype' => 'members',
    'sourceid' => '2',
    'smallids' => '',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{xingming}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{image}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{mspacehome}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '42',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => 'push_load_mems.php',
  ),
  'push_102' => 
  array (
    'paid' => 'push_102',
    'cname' => '今日团购_2',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '5',
    'smallids' => ',5,',
    'smallson' => 1,
    'sourcesql' => '({pre}enddate > \'{timestamp}\' or {pre}enddate=0)',
    'sourcefields' => 
    array (
      'pid3' => 
      array (
        'from' => '{pid3}',
        'refresh' => '1',
      ),
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '43',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => 'pushs_huxing.php',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_17' => 
  array (
    'paid' => 'push_17',
    'cname' => '经纪公司☆',
    'ptid' => '2',
    'sourcetype' => 'members',
    'sourceid' => '3',
    'smallids' => '',
    'smallson' => '0',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{cmane}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{mspacehome}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{image}',
        'refresh' => '1',
      ),
      'szqy' => 
      array (
        'from' => '{szqy}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '1',
    'vieworder' => '43',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => 'push_load_mems.php',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_137' => 
  array (
    'paid' => 'push_137',
    'cname' => '图说地产',
    'ptid' => '2',
    'sourcetype' => 'archives',
    'sourceid' => '112',
    'smallids' => ',600,',
    'smallson' => '1',
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => '0',
    'vieworder' => '50',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => '10',
    'mspace' => '0',
    'orderspace' => '0',
    'copyspace' => '0',
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => 1,
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_107' => 
  array (
    'paid' => 'push_107',
    'cname' => '幻灯片_3',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '7',
    'smallids' => ',7,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'pid3' => 
      array (
        'from' => '{pid3}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '51',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => 'pushs_huxing.php',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
  'push_24' => 
  array (
    'paid' => 'push_24',
    'cname' => '楼盘',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',1,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '51',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '0',
  ),
  'push_108' => 
  array (
    'paid' => 'push_108',
    'cname' => '户型图_3',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '11',
    'smallids' => ',11,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'pid3' => 
      array (
        'from' => '{pid3}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '52',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => 'pushs_huxing.php',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '1',
  ),
  'push_25' => 
  array (
    'paid' => 'push_25',
    'cname' => '二手',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',1,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '52',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '0',
  ),
  'push_109' => 
  array (
    'paid' => 'push_109',
    'cname' => '楼盘图库_3',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '7',
    'smallids' => ',7,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'pid3' => 
      array (
        'from' => '{pid3}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '53',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => 'pushs_huxing.php',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '1',
    'enddate_from' => '',
    'forbid_useradd' => '0',
  ),
  'push_26' => 
  array (
    'paid' => 'push_26',
    'cname' => '商业',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',1,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '53',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '0',
  ),
  'push_27' => 
  array (
    'paid' => 'push_27',
    'cname' => '资讯',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',1,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '54',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '0',
  ),
  'push_cjwt' => 
  array (
    'paid' => 'push_cjwt',
    'cname' => '常见问题_3',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '106',
    'smallids' => ',517,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '54',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '0',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 5,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
  ),
  'push_11' => 
  array (
    'paid' => 'push_11',
    'cname' => '工具',
    'ptid' => 2,
    'sourcetype' => 'archives',
    'sourceid' => '1',
    'smallids' => ',1,',
    'smallson' => 1,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'classid1' => 
      array (
        'from' => '{caid}',
        'refresh' => '1',
      ),
      'abstract' => 
      array (
        'from' => '{abstract}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 0,
    'vieworder' => '55',
    'available' => '1',
    'apmid' => '0',
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => '0',
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'autopush' => '0',
    'enddate_from' => '',
    'forbid_useradd' => '0',
  ),
  'push_kfs' => 
  array (
    'paid' => 'push_kfs',
    'cname' => '品牌开发商',
    'ptid' => 4,
    'sourcetype' => 'archives',
    'sourceid' => '13',
    'smallids' => ',36,',
    'smallson' => 0,
    'sourcesql' => '',
    'sourcefields' => 
    array (
      'subject' => 
      array (
        'from' => '{subject}',
        'refresh' => '1',
      ),
      'content' => 
      array (
        'from' => '{content}',
        'refresh' => '1',
      ),
      'address' => 
      array (
        'from' => '{address}',
        'refresh' => '1',
      ),
      'tel' => 
      array (
        'from' => '{tel}',
        'refresh' => '1',
      ),
      'thumb' => 
      array (
        'from' => '{thumb}',
        'refresh' => '1',
      ),
      'url' => 
      array (
        'from' => '{arcurl}',
        'refresh' => '1',
      ),
    ),
    'sourceadv' => 1,
    'vieworder' => '60',
    'autopush' => '1',
    'available' => 1,
    'apmid' => 0,
    'autocheck' => '1',
    'maxorderno' => 10,
    'mspace' => 0,
    'orderspace' => 0,
    'copyspace' => 0,
    'script_admin' => '',
    'script_detail' => '',
    'script_load' => '',
    'enddate_from' => '',
    'forbid_useradd' => 0,
  ),
) ;