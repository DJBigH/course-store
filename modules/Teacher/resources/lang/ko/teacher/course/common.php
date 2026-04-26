<?php

return array (
  'actions' => 
  array (
    'create' => '강의 생성',
    'trash' => '휴지통 보기',
    'back' => '뒤로',
    'cancel' => '취소',
    'edit' => '편집',
    'lessons' => '레슨 관리',
    'manage_quiz' => '퀴즈 관리',
    'update' => '강의 업데이트',
    'yes' => '예',
    'no' => '아니요',
  ),
  'status' => 
  array (
    'active' => '활성',
    'not_active' => '비활성',
    'published' => '게시됨',
    'draft' => '초안',
  ),
  'form' => 
  array (
    'name_label' => '강의명 (:locale)',
    'detail_label' => '상세 설명 (:locale)',
    'supports_label' => '학습 내용 (:locale)',
    'thumbnail' => '썸네일 URL',
    'code' => '강의 코드',
    'price' => '원래 가격',
    'price_label' => '원래 가격 (:locale)',
    'sale_price' => '할인가',
    'sale_price_label' => '할인가 (:locale)',
    'status' => '상태',
    'position' => '순서',
    'is_document' => '첨부 파일',
    'is_learning_locked' => '학습 잠금',
    'price_hint' => '최대 가격 :max.',
    'sale_price_hint' => '원래 가격을 초과할 수 없습니다.',
    'sale_price_error' => '할인가는 원래 가격보다 높을 수 없습니다.',
    'is_coming_soon' => '판매 상태',
    'is_coming_soon_label' => '커밍순 (Coming Soon)',
    'coming_soon_start_at' => '공식 출시일',
    'coming_soon_hint' => '홈페이지의 카운트다운에 사용됩니다.',
    'stock_label' => '판매 상태 및 시간',
  ),
  'warnings' => 
  array (
    'validation_summary' => '입력 내용을 다시 확인해 주세요.',
    'import_export_locked' => 'CSV 가져오기/내보내기 기능은 프리미엄 패키지에서 제공됩니다.',
  ),
  'history' => 
  array (
    'course_created' => '새 강의를 생성했습니다',
    'course_updated' => '강의를 업데이트했습니다',
    'course_duplicated' => '강의를 복제했습니다',
    'course_published' => '강의를 게시했습니다',
    'course_moved_to_draft' => '초안으로 이동했습니다',
    'course_visibility_updated' => '공개 설정을 업데이트했습니다',
    'course_priority_enabled' => '우선순위를 활성화했습니다',
    'course_priority_disabled' => '우선순위를 비활성화했습니다',
    'course_priority_toggled' => '우선순위를 전환했습니다',
    'course_deleted' => '휴지통으로 이동했습니다',
    'course_restored' => '강의를 복원했습니다',
    'course_force_deleted' => '영구 삭제했습니다',
  ),
  'labels' => 
  array (
    'lessons' => ':count 레슨',
    'students' => ':count 수강생',
    'price' => ':amount',
    'deleted_at' => ':time에 삭제됨',
    'priority_active' => '우선순위 활성',
    'limited_actions_only' => '제한된 작업',
    'package_usage' => ':used / :limit 강의 사용 중',
    'unlimited' => '무제한',
    'free' => '무료',
  ),
);
