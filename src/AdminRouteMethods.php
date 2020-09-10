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
            $this->get('user', 'AccountController@index');
            $this->patch('account/update', 'AccountController@update')->name('account.update');
            $this->patch('account/password', 'AccountController@password')->name('account.password');
        };
    }

    /**
     * Impersonation routes
     */
    public function impersonate()
    {
        return function () {
            $this->post('users/{user}/impersonate', 'UserController@impersonate');
            $this->post('users/stopImpersonate', 'UserController@stopImpersonate');
        };
    }

    /**
     * Image upload from Wysiwyg
     */
    public function upload()
    {
        return function () {
            $this->post('upload', '\Okami101\LaravelAdmin\Http\Controllers\UploadController@index');
        };
    }
}
