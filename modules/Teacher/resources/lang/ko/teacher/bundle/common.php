<?php

return array (
  'actions' => 
  array (
    'create' => '콤보 생성',
    'update' => '콤보 업데이트',
    'edit' => '수정',
    'delete' => '삭제',
    'view_public' => '공개 페이지 보기',
  ),
  'form' => 
  array (
    'name' => '콤보 이름',
    'price' => '원래 가격',
    'sale_price' => '할인가',
    'status' => '공개 및 구매 가능',
    'thumbnail' => '썸네일',
    'description' => '콤보 설명',
    'content_title' => '콤보 내용',
    'pricing_title' => '가격 및 상태',
    'price_help' => '이 콤보에 포함된 모든 강의의 총 가격입니다.',
    'sale_price_help' => '할인 후 실제 판매 가격입니다. 할인이 없으면 비워 두십시오.',
    'courses_title' => '강의 선택',
    'courses_help' => '이 콤보에 포함할 강의를 선택하세요 (최소 2개).',
    'quantity' => '수량 제한',
    'quantity_help' => '최대 구매 수량. 제한이 없으면 비워 두십시오.',
    'end_at' => '등록 마감일',
    'end_at_help' => '이 시간 이후에는 고객이 콤보를 구매할 수 없습니다.',
    'is_coming_soon' => '커밍순 모드',
    'is_coming_soon_help' => '카운트다운을 표시하며 아직 구매할 수 없습니다.',
    'coming_soon_start_at' => '공식 출시일',
  ),
  'flash' => 
  array (
    'created' => '코스 콤보가 성공적으로 생성되었습니다.',
    'updated' => '코스 콤보가 성공적으로 업데이트되었습니다.',
    'deleted' => '코스 콤보가 삭제되었습니다.',
  ),
  'labels' => 
  array (
    'course_count' => ':count 강의',
    'slug' => '경로: :slug',
  ),
  'history' => 
  array (
    'bundle_created' => '새로운 강의 콤보 생성: :name',
    'bundle_updated' => '강의 콤보 업데이트: :name',
    'bundle_deleted' => '강의 콤보 삭제: :name',
  ),
  'validation' => 
  array (
    'name_required' => '콤보 이름을 입력해 주세요.',
    'price_required' => '콤보 원래 가격을 입력해 주세요.',
    'course_ids_required' => '최소 2개의 강의를 선택해 주세요.',
    'course_ids_min' => '콤보에는 최소 2개의 강의가 포함되어야 합니다.',
    'sale_price_lt' => '할인가는 원래 가격보다 낮아야 합니다.',
  ),
);
