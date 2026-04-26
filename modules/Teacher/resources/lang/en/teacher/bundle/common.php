<?php

return array (
  'actions' => 
  array (
    'create' => 'Create bundle',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'back' => 'Back to Bundles',
    'update' => 'Update bundle',
    'view_public' => 'View Public',
  ),
  'form' => 
  array (
    'name' => 'Bundle Name',
    'description' => 'Bundle Description',
    'courses' => 'Courses in Bundle',
    'price' => 'Original Price',
    'sale_price' => 'Sale Price',
    'status' => 'Publicly visible and available for purchase',
    'thumbnail' => 'Thumbnail',
    'content_title' => 'Bundle Content',
    'pricing_title' => 'Pricing & Status',
    'price_help' => 'The original list price of the entire bundle.',
    'sale_price_help' => 'Actual selling price after discount. Leave blank for no discount.',
    'courses_title' => 'Select Courses',
    'courses_help' => 'Select courses to include in this bundle (Min 2).',
    'quantity' => 'Quantity Limit',
    'quantity_help' => 'Maximum number of purchases. Leave blank for unlimited.',
    'end_at' => 'Registration Deadline',
    'end_at_help' => 'Customers cannot purchase after this time.',
    'is_coming_soon' => 'Coming Soon Mode',
    'is_coming_soon_help' => 'Displays a countdown and prevents immediate purchase.',
    'coming_soon_start_at' => 'Official Launch Date',
  ),
  'labels' => 
  array (
    'course_count' => ':count courses',
    'slug' => 'Path: :slug',
  ),
  'flash' => 
  array (
    'deleted' => 'Course bundle deleted.',
  ),
  'history' => 
  array (
    'bundle_created' => 'Created new course bundle: :name',
    'bundle_updated' => 'Updated course bundle: :name',
    'bundle_deleted' => 'Deleted course bundle: :name',
  ),
  'validation' => 
  array (
    'name_required' => 'Please enter the bundle name.',
    'price_required' => 'Please enter the original price.',
    'course_ids_required' => 'Please select at least 2 courses.',
    'course_ids_min' => 'The bundle must contain at least 2 courses.',
    'sale_price_lt' => 'The sale price must be less than the original price.',
  ),
);
