<?php

return array (
  'actions' => 
  array (
    'create' => '创建组合',
    'update' => '更新组合',
    'edit' => '编辑',
    'delete' => '删除',
    'view_public' => '查看公开页面',
  ),
  'form' => 
  array (
    'name' => '组合名称',
    'price' => '原价',
    'sale_price' => '促销价',
    'status' => '公开可见并允许购买',
    'thumbnail' => '缩略图',
    'description' => '组合描述',
    'content_title' => '组合内容',
    'pricing_title' => '价格与状态',
    'price_help' => '此组合中所有课程的总价。',
    'sale_price_help' => '折后的实际销售价格。如果不打折，请留空。',
    'courses_title' => '选择课程',
    'courses_help' => '选择要包含在此组合中的课程（最少2门）。',
    'quantity' => '数量限制',
    'quantity_help' => '最大购买数量。如果不限制，请留空。',
    'end_at' => '注册截止日期',
    'end_at_help' => '超过此时间后，客户将无法购买此组合。',
    'is_coming_soon' => '即将推出模式',
    'is_coming_soon_help' => '显示倒计时，且暂不允许购买。',
    'coming_soon_start_at' => '正式发布日期',
  ),
  'flash' => 
  array (
    'deleted' => '课程组合已删除。',
  ),
  'labels' => 
  array (
    'course_count' => ':count 门课程',
    'slug' => '路径: :slug',
  ),
  'history' => 
  array (
    'bundle_created' => '创建了新的课程组合: :name',
    'bundle_updated' => '更新了课程组合: :name',
    'bundle_deleted' => '删除了课程组合: :name',
  ),
  'validation' => 
  array (
    'name_required' => '请输入组合名称。',
    'price_required' => '请输入组合原价。',
    'course_ids_required' => '请选择至少2门课程。',
    'course_ids_min' => '组合必须包含至少2门课程。',
    'sale_price_lt' => '促销价必须低于原价。',
  ),
);
