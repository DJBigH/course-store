<?php

return array (
  'actions' => 
  array (
    'create' => 'コンボ作成',
    'update' => 'コンボ更新',
    'edit' => '編集',
    'delete' => '削除',
    'view_public' => '公開ページを表示',
  ),
  'form' => 
  array (
    'name' => 'コンボ名',
    'price' => '元の価格',
    'sale_price' => 'セール価格',
    'status' => '公開および購入可能にする',
    'thumbnail' => 'サムネイル',
    'description' => 'コンボの説明',
    'content_title' => 'コンボの内容',
    'pricing_title' => '価格とステータス',
    'price_help' => 'このコンボに含まれるすべてのコースの合計価格。',
    'sale_price_help' => '割引後の実際の販売価格。割引がない場合は空にしてください。',
    'courses_title' => 'コースを選択',
    'courses_help' => 'このコンボに含めるコースを選択してください（最低2つ）。',
    'quantity' => '数量限定',
    'quantity_help' => '最大購入数。無制限の場合は空にしてください。',
    'end_at' => '登録期限',
    'end_at_help' => 'この時間を過ぎると、お客様はコンボを購入できなくなります。',
    'is_coming_soon' => 'カミングスーンモード',
    'is_coming_soon_help' => 'カウントダウンを表示し、購入はまだできません。',
    'coming_soon_start_at' => '公式発売日',
  ),
  'flash' => 
  array (
    'created' => 'コースコンボが正常に作成されました。',
    'updated' => 'コースコンボが正常に更新されました。',
    'deleted' => 'コースコンボを削除しました。',
  ),
  'labels' => 
  array (
    'course_count' => ':count コース',
    'slug' => 'パス: :slug',
  ),
  'history' => 
  array (
    'bundle_created' => '新しいコースコンボを作成しました: :name',
    'bundle_updated' => 'コースコンボを更新しました: :name',
    'bundle_deleted' => 'コースコンボを削除しました: :name',
  ),
  'validation' => 
  array (
    'name_required' => 'コンボ名を入力してください。',
    'price_required' => 'コンボの元の価格を入力してください。',
    'course_ids_required' => '少なくとも2つのコースを選択してください。',
    'course_ids_min' => 'コンボには少なくとも2つのコースが必要です。',
    'sale_price_lt' => 'セール価格は元の価格より低くなければなりません。',
  ),
);
