<?php

if (!function_exists('error_field')) {

    function error_field($field) {
        $errors = Session::get('errors');
        if (count($errors) > 0) {
            if ($errors->has($field)) {
                return '<label id="'. $field .'-error" class="error" for="'. $field .'">'. $errors->first($field) .'</label>';
            }
        }
        return '';
    }

}

if (!function_exists('test_Renames')) {
    
    function test_Renames($name) {
        return [
            'index' => $name.".index",
            'create' => $name.".create",
            'store' => $name.".store",
            'show' => $name.".show",
            'edit' => $name.".edit",
            'update' => $name.".update",
            'destroy' => $name.".destroy"
        ];
    }
    
}

if (!function_exists('test_Shuffle')) {
    
    function test_Shuffle($array) {
        $keys = array_keys($array);
        $new_array = [];
        shuffle($array);
        foreach ($array as $key => $item) {
            $new_array[$keys[$key]] = $item;
        }
        return $new_array;
    }
    
}