<?php

return array (
  'actions' => 
  array (
    'create' => '创建课程',
    'trash' => '查看回收站',
    'back' => '返回',
    'cancel' => '取消',
    'edit' => '编辑',
    'lessons' => '课程管理',
    'manage_quiz' => '测验管理',
    'update' => '更新课程',
    'yes' => '是',
    'no' => '否',
  ),
  'status' => 
  array (
    'active' => '激活',
    'not_active' => '未激活',
    'published' => '已发布',
    'draft' => '草稿',
  ),
  'form' => 
  array (
    'name_label' => '课程名称 (:locale)',
    'detail_label' => '详细描述 (:locale)',
    'supports_label' => '学习内容 (:locale)',
    'thumbnail' => '缩略图 URL',
    'code' => '课程代码',
    'price' => '原价',
    'price_label' => '原价 (:locale)',
    'sale_price' => '促销价',
    'sale_price_label' => '促销价 (:locale)',
    'status' => '状态',
    'position' => '排序',
    'is_document' => '附件',
    'is_learning_locked' => '锁定学习',
    'price_hint' => '最高价格 :max.',
    'sale_price_hint' => '不得超过原价。',
    'sale_price_error' => '促销价不能高于原价。',
    'is_coming_soon' => '销售状态',
    'is_coming_soon_label' => '即将推出 (Coming Soon)',
    'coming_soon_start_at' => '正式发布日期',
    'coming_soon_hint' => '用于主页的倒计时。',
    'stock_label' => '销售状态与时间',
  ),
  'warnings' => 
  array (
    'validation_summary' => '请重新检查输入内容。',
    'import_export_locked' => 'CSV 导入/导出功能仅适用于高级套餐。',
  ),
  'history' => 
  array (
    'course_created' => '创建了新课程',
    'course_updated' => '更新了课程',
    'course_duplicated' => '复制了课程',
    'course_published' => '发布了课程',
    'course_moved_to_draft' => '已移动到草稿',
    'course_visibility_updated' => '更新了可见性设置',
    'course_priority_enabled' => '启用了优先级',
    'course_priority_disabled' => '禁用了优先级',
    'course_priority_toggled' => '切换了优先级',
    'course_deleted' => '已移至回收站',
    'course_restored' => '恢复了课程',
    'course_force_deleted' => '永久删除了',
  ),
  'labels' => 
  array (
    'lessons' => ':count 课时',
    'students' => ':count 学生',
    'price' => ':amount',
    'deleted_at' => '删除于 :time',
    'priority_active' => '优先级已激活',
    'limited_actions_only' => '限制操作',
    'package_usage' => '已使用 :used / :limit 课程',
    'unlimited' => '无限',
    'free' => '免费',
  ),
);
