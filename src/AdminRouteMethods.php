<?php

namespace Okami101\LaravelAdmin;

use Illuminate\Support\Facades\Route;

/**
 * Class AdminRouteMethods
 *
 * @mixin Route
 */
class AdminRouteMethods
{
    /**
     * Profile routes
     */
    public function account()
    {
        return function () {
            $this->get('user', 'App\Http\Controllers\AccountController@index');
        };
    }

    /**
     * Impersonation routes
     */
    public function impersonate()
    {
        return function () {
            $this->post('users/{user}/impersonate', 'App\Http\Controllers\UserController@impersonate');
            $this->post('users/stopImpersonate', 'App\Http\Controllers\UserController@stopImpersonate');
        };
    }

    /**
     * Image upload from Wysiwyg
     */
    public function upload()
    {
        return function () {
            $this->post('upload', 'Okami101\LaravelAdmin\Http\Controllers\UploadController@index');
        };
    }
}
