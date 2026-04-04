<?php

if (!function_exists('getCategoriesCheckBox')) {
    function getCategoriesCheckBox($categories, $old = [], $parentId = 0, $char = '')
    {
        $id = request()->route()->category;
        if ($categories) {
            foreach ($categories as $key => $category) {
                if ($category->parent_id == $parentId && $id != $category->id) {
                    $checked = !empty($old) && in_array($category->id, $old) ? 'checked' : null;
                    echo '<label class="d-block ps-2"><input type="checkbox" class="me-1" name="categories[]" value="' . $category->id . '"' . $checked . '/>' . $char . $category->name . '</label>';
                    unset($categories[$key]);
                    getCategoriesCheckBox($categories, $old, $category->id, $char . ' |- ');
                }
            }
        }
    }
}
