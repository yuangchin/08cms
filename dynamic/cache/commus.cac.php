<?php
defined('M_COM') || exit('No Permission');
$commus = array (
  1 => 
  array (
    'cuid' => '1',
    'cname' => '评论',
    'remark' => '资讯,专题,看房团,图库,楼盘,写字楼楼盘,商铺楼盘',
    'available' => '1',
    'tbl' => 'commu_zixun',
    'chids' => 
    array (
      1 => '4',
      2 => '112',
      3 => '115',
      4 => '116',
      5 => '110',
      6 => '1',
      7 => '14',
    ),
    'pmid' => '0',
    'autocheck' => '1',
    'repeattime' => '0',
    'acurrency' => '5',
    'ccurrency' => '10',
  ),
  2 => 
  array (
    'cuid' => '2',
    'cname' => '楼盘评分',
    'remark' => '楼盘的评分',
    'available' => '1',
    'tbl' => 'commu_dp',
    'chids' => 
    array (
      1 => '4',
      2 => '115',
      3 => '116',
    ),
    'pmid' => '23',
    'autocheck' => '1',
    'repeattime' => '5',
    'acurrency' => '5',
    'ccurrency' => '10',
  ),
  3 => 
  array (
    'cuid' => '3',
    'cname' => '楼盘订阅',
    'remark' => '楼盘意向',
    'available' => '1',
    'tbl' => 'commu_yx',
    'chids' => 
    array (
      1 => '4',
      2 => '115',
      3 => '116',
    ),
    'pmid' => '0',
    'issms' => '1',
    'smscon' => '您好！您订阅的楼盘是:{$subject}；地址:{$address}；楼盘联系电话:{$tel}',
    'issmshout' => '1',
    'repeattime' => '3',
    'acurrency' => '5',
    'ccurrency' => '10',
  ),
  4 => 
  array (
    'cuid' => '4',
    'cname' => '房源举报',
    'remark' => '',
    'available' => '1',
    'tbl' => 'commu_jubao',
    'chids' => 
    array (
      1 => '3',
      2 => '2',
    ),
    'pmid' => '0',
    'repeattime' => '',
    'acurrency' => '5',
    'ccurrency' => '10',
  ),
  5 => 
  array (
    'cuid' => '5',
    'cname' => '空间留言',
    'remark' => '经纪人',
    'available' => '1',
    'tbl' => 'commu_liuyan',
    'chids' => 
    array (
      1 => '2',
    ),
    'pmid' => '0',
    'autocheck' => '1',
    'repeattime' => '',
  ),
  6 => 
  array (
    'cuid' => '6',
    'cname' => '文档收藏',
    'remark' => '',
    'available' => '1',
    'tbl' => 'commu_sc',
    'chids' => 
    array (
      1 => '115',
      2 => '116',
      3 => '117',
      4 => '118',
      5 => '119',
      6 => '120',
      7 => '3',
      8 => '2',
      9 => '9',
      10 => '10',
      11 => '13',
      12 => '103',
      13 => '106',
    ),
    'pmid' => '0',
  ),
  7 => 
  array (
    'cuid' => '7',
    'cname' => '楼盘收藏',
    'remark' => '',
    'available' => '1',
    'tbl' => 'commu_gz',
    'chids' => 
    array (
      0 => '4',
    ),
    'pmid' => '0',
  ),
  8 => 
  array (
    'cuid' => '8',
    'cname' => '新房团购',
    'remark' => '',
    'available' => '1',
    'tbl' => 'commu_df',
    'chids' => 
    array (
      1 => '5',
    ),
    'pmid' => '0',
    'repeattime' => '0',
    'acurrency' => '5',
    'ccurrency' => '10',
  ),
  9 => 
  array (
    'cuid' => '9',
    'cname' => '需求收藏_暂不用',
    'remark' => '',
    'available' => '0',
    'tbl' => 'commu_xgz',
    'chids' => 
    array (
      0 => '9',
      1 => '10',
    ),
    'pmid' => '0',
  ),
  10 => 
  array (
    'cuid' => '10',
    'cname' => '公司资金',
    'remark' => '',
    'available' => '1',
    'tbl' => 'commu_zj',
  ),
  11 => 
  array (
    'cuid' => '11',
    'cname' => '店铺收藏',
    'remark' => '',
    'available' => '1',
    'tbl' => 'commu_dsc',
    'chids' => 
    array (
      1 => '2',
      2 => '3',
      3 => '11',
      4 => '12',
    ),
    'pmid' => '0',
  ),
  31 => 
  array (
    'cuid' => '31',
    'cname' => '业主评论',
    'remark' => '装修公司',
    'available' => '1',
    'tbl' => 'commu_yezhupl',
    'chids' => 
    array (
      0 => '11',
    ),
    'pmid' => '0',
    'autocheck' => '1',
    'repeattime' => '5',
  ),
  36 => 
  array (
    'cuid' => '36',
    'cname' => '委托房源',
    'remark' => '委托房源(手机短信通知功能)',
    'available' => '1',
    'tbl' => 'commu_weituo',
  ),
  37 => 
  array (
    'cuid' => '37',
    'cname' => '问答答案',
    'remark' => '问答交互',
    'available' => '1',
    'tbl' => 'commu_answers',
    'chids' => 
    array (
      0 => '106',
    ),
    'pmid' => '0',
    'autocheck' => '1',
    'repeatanswer' => '1',
    'acurrency' => '5',
    'ccurrency' => '10',
  ),
  38 => 
  array (
    'cuid' => '38',
    'cname' => '问答举报',
    'remark' => '举报',
    'available' => '1',
    'tbl' => 'commu_jbask',
    'chids' => 
    array (
      0 => '106',
    ),
    'pmid' => '0',
    'autocheck' => '1',
    'repeattime' => '',
    'acurrency' => '1',
    'ccurrency' => '3',
  ),
  39 => 
  array (
    'cuid' => '39',
    'cname' => '问答收藏_暂不用',
    'remark' => '问答收藏',
    'available' => '0',
    'tbl' => 'commu_scask',
    'chids' => 
    array (
      0 => '106',
    ),
    'pmid' => '0',
  ),
  40 => 
  array (
    'cuid' => '40',
    'cname' => '网站提问',
    'remark' => '网站提问',
    'available' => '1',
    'tbl' => 'commu_webtw',
    'pmid' => '0',
    'autocheck' => '0',
    'repeattime' => '0',
  ),
  41 => 
  array (
    'cuid' => '41',
    'cname' => '资讯读后感',
    'remark' => '资讯读后感',
    'available' => '1',
    'tbl' => 'commu_zxdp',
    'chids' => 
    array (
      1 => '1',
    ),
    'repeattime' => '',
  ),
  42 => 
  array (
    'cuid' => '42',
    'cname' => '专家团申请',
    'remark' => '只放状态,具体资料存在会员通用字段',
    'available' => '1',
    'tbl' => 'commu_expert',
  ),
  43 => 
  array (
    'cuid' => '43',
    'cname' => '房源预约刷新',
    'remark' => '预约刷新房源',
    'available' => '1',
    'tbl' => 'commu_yuyue',
  ),
  44 => 
  array (
    'cuid' => '44',
    'cname' => '楼盘印象',
    'remark' => '存储楼盘印象数据',
    'available' => '1',
    'tbl' => 'commu_impression',
    'pmid' => '0',
    'autocheck' => '1',
    'repeattime' => '0',
    'totalnum' => '3',
    'yxnum' => '15',
    'chids' => 
    array (
    ),
  ),
  45 => 
  array (
    'cuid' => '45',
    'cname' => '看房活动报名',
    'remark' => '看房团',
    'available' => '1',
    'tbl' => 'commu_kanfang',
    'chids' => 
    array (
      0 => '',
      1 => '110',
    ),
    'pmid' => '0',
    'autocheck' => '1',
    'repeattime' => '0',
    'acurrency' => '5',
    'ccurrency' => '10',
  ),
  46 => 
  array (
    'cuid' => '46',
    'cname' => '房源意向',
    'remark' => '二手房,出租的意向(手机短信通知功能)',
    'available' => '1',
    'tbl' => 'commu_fyyx',
    'chids' => 
    array (
      1 => '117',
      2 => '118',
      3 => '119',
      4 => '120',
      5 => '3',
      6 => '2',
    ),
    'issms' => '1',
    'smsfee' => 'sadm',
    'smscon' => '您好！联系人{$uname}（电话{$utel}）对你的房源（{$subject}）有意向需求，请尽快回复！',
    'pmid' => '0',
    'repeattime' => '1',
  ),
  47 => 
  array (
    'cuid' => '47',
    'cname' => '价格趋势',
    'remark' => '楼盘、二手房、出租的价格趋势(时间：month)',
    'available' => '1',
    'tbl' => 'commu_pricetrend',
  ),
  48 => 
  array (
    'cuid' => '48',
    'cname' => '楼盘评论暂不用',
    'remark' => '楼盘、写字楼、商铺评论及回复',
    'available' => '0',
    'tbl' => 'commu_lppl',
    'chids' => 
    array (
      1 => '4',
      2 => '115',
      3 => '116',
    ),
    'pmid' => '0',
    'autocheck' => '1',
    'repeattime' => '',
  ),
  49 => 
  array (
    'cuid' => '49',
    'cname' => '分销推荐信息',
    'remark' => '存放推荐客户看房信息',
    'available' => '1',
    'tbl' => 'commu_customer',
  ),
  50 => 
  array (
    'cuid' => '50',
    'cname' => '佣金提款信息',
    'remark' => '存放从分销中获得佣金的提款信息',
    'available' => '1',
    'tbl' => 'commu_fxtikuan',
  ),
  101 => 
  array (
    'cuid' => '101',
    'cname' => '直播信息',
    'remark' => '发言人的信息',
    'available' => '1',
    'tbl' => 'commu_live',
  ),
  201 => 
  array (
    'cuid' => '201',
    'cname' => '测评师评论',
    'remark' => '测评师评论',
    'available' => '1',
    'tbl' => 'commu_cpspl',
    'chids' => 
    array (
      1 => '4',
    ),
    'pmid' => '0',
    'autocheck' => '1',
    'repeattime' => '30',
    'acurrency' => '5',
    'ccurrency' => '10',
  ),
  202 => 
  array (
    'cuid' => '202',
    'cname' => '浏览统计(交互)表',
    'remark' => '新房页面访问统计表',
    'available' => '1',
    'tbl' => 'commu_count',
  ),
  203 => 
  array (
    'cuid' => '203',
    'cname' => '分享(交互)表',
    'remark' => '分享(交互)表',
    'available' => '1',
    'tbl' => 'commu_fenxiang',
  ),
  204 => 
  array (
    'cuid' => '204',
    'cname' => '用户分享记录',
    'remark' => '记录用户机经纪人对分享楼盘的分享操作',
    'available' => '1',
    'tbl' => 'commu_share_record',
  ),
  205 => 
  array (
    'cuid' => '205',
    'cname' => '用户浏览记录',
    'remark' => '用户浏览记录',
    'available' => '1',
    'tbl' => 'commu_view_record',
  ),
  206 => 
  array (
    'cuid' => '206',
    'cname' => '400电话拨打记录',
    'remark' => '400电话拨打记录',
    'available' => '1',
    'tbl' => 'web400calllog',
  ),
) ;