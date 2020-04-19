<?php

namespace Vtec\Crud;

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
}
