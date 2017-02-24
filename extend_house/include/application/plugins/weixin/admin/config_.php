<?php
/**
 * 总站菜单分类配置文件
 *
 * @author    Wilson
 * @copyright Copyright (C) 2008 - 2014, 08CMS Inc. All rights reserved.
 * @version   1.0
 */
 
return array(
         array(
             'name' => '楼盘', 
             'sub_button' => array(
                 array('type' => 'click', 'name' => '按区域', 'key' => 'PROPERTY_REGION'),
                 array('type' => 'click', 'name' => '按物业类型', 'key' => 'PROPERTY_PROPERTY'),
                 array('type' => 'click', 'name' => '按价格', 'key' => 'PROPERTY_PRICE'),
                 array('type' => 'click', 'name' => '按周边', 'key' => 'PROPERTY_PERIPHERY')
             )
         ),
       
         array(
             'name' => '二手房', 
             'sub_button' => array(
                 array('type' => 'click', 'name' => '按区域', 'key' => 'SECOND_HAND_HOUSING_REGION'),
                 array('type' => 'click', 'name' => '按价格', 'key' => 'SECOND_HAND_HOUSING_PRICE'),
                 array('type' => 'click', 'name' => '按面积', 'key' => 'SECOND_HAND_HOUSING_AREA'),
                 array('type' => 'click', 'name' => '按周边', 'key' => 'SECOND_HAND_HOUSING_PERIPHERY')
             )
         ),
         
         array(
             'name' => '出租房', 
             'sub_button' => array(
                 array('type' => 'click', 'name' => '按区域', 'key' => 'RENTING_REGION'),
                 array('type' => 'click', 'name' => '按价格', 'key' => 'RENTING_PRICE'),
                 array('type' => 'click', 'name' => '按面积', 'key' => 'RENTING_AREA'),
                 array('type' => 'click', 'name' => '按周边', 'key' => 'RENTING_PERIPHERY')
             )
         )
      );