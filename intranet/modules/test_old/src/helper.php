<?php

if (!function_exists('show_messes')) {

    function show_messes($txt_class = null) {
        $result = '';
        if (Session::has('mess_error')) {
            $result = '<div class="alert alert-danger alert-dismissible">'
                    . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
                    . '<div class="mess_error">' . Session::get('mess_error') . '</div></div>';
            Session::forget('mess_error');
        }
        if (Session::has('mess_succ')) {
            $result = '<div class="alert alert-success alert-dismissible">'
                    . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
                    . '<div class="mess_succ">' . Session::get('mess_succ') . '</div></div>';
            Session::forget('mess_succ');
        }
        return $result;
    }

}


if (!function_exists('error_field')) {

    function error_field($field) {
        $errors = Session::get('errors');
        if (count($errors) > 0) {
            if ($errors->has($field)) {
                return '<div class="help-block alert alert-danger">' . $errors->first($field) . '</div>';
            }
        }
        return '';
    }

}