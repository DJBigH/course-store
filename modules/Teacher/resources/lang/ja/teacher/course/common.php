<?php

return array (
  'actions' => 
  array (
    'create' => 'コース作成',
    'trash' => 'ゴミ箱を表示',
    'back' => '戻る',
    'cancel' => 'キャンセル',
    'edit' => '編集',
    'lessons' => 'レッスン管理',
    'manage_quiz' => 'クイズ管理',
    'update' => 'コース更新',
    'yes' => 'はい',
    'no' => 'いいえ',
  ),
  'status' => 
  array (
    'active' => '有効',
    'not_active' => '無効',
    'published' => '公開中',
    'draft' => '下書き',
  ),
  'form' => 
  array (
    'name_label' => 'コース名 (:locale)',
    'detail_label' => '詳細説明 (:locale)',
    'supports_label' => '学習内容 (:locale)',
    'thumbnail' => 'サムネイルURL',
    'code' => 'コースコード',
    'price' => '元の価格',
    'price_label' => '元の価格 (:locale)',
    'sale_price' => 'セール価格',
    'sale_price_label' => 'セール価格 (:locale)',
    'status' => 'ステータス',
    'position' => '位置',
    'is_document' => '添付書類',
    'is_learning_locked' => '学習をロック',
    'price_hint' => '最大価格 :max.',
    'sale_price_hint' => '元の価格を超えてはいけません。',
    'sale_price_error' => 'セール価格は元の価格より高く設定できません。',
    'is_coming_soon' => '販売ステータス',
    'is_coming_soon_label' => 'カミングスーン (Coming Soon)',
    'coming_soon_start_at' => '公式発売日',
    'coming_soon_hint' => 'ホームページのカウントダウンに使用されます。',
    'stock_label' => '販売ステータスと時間',
  ),
  'warnings' => 
  array (
    'validation_summary' => '入力内容を再確認してください。',
    'import_export_locked' => 'CSV出入力機能は上位パッケージで利用可能です。',
  ),
  'history' => 
  array (
    'course_created' => '新しいコースを作成しました',
    'course_updated' => 'コースを更新しました',
    'course_duplicated' => 'コースを複製しました',
    'course_published' => 'コースを公開しました',
    'course_moved_to_draft' => '下書きに移動しました',
    'course_visibility_updated' => '表示設定を更新しました',
    'course_priority_enabled' => '優先度を有効にしました',
    'course_priority_disabled' => '優先度を無効にしました',
    'course_priority_toggled' => '優先度を切り替えました',
    'course_deleted' => 'ゴミ箱に移動しました',
    'course_restored' => 'コースを復元しました',
    'course_force_deleted' => '完全に削除しました',
  ),
  'labels' => 
  array (
    'lessons' => ':count レッスン',
    'students' => ':count 受講生',
    'price' => ':amount',
    'deleted_at' => ':time に削除されました',
    'priority_active' => '優先度有効',
    'limited_actions_only' => '制限付きアクション',
    'package_usage' => '使用済み :used / :limit コース',
    'unlimited' => '無制限',
    'free' => '無料',
  ),
);
