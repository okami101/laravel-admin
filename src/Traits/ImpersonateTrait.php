<?php

namespace Vtec\Crud\Traits;

trait ImpersonateTrait
{
    public function setImpersonating($id)
    {
        if ($id === 1) {
            return abort(403);
        }
        session()->put('impersonate', $id);
    }

    public function stopImpersonating()
    {
        session()->forget('impersonate');
    }

    public function isImpersonating()
    {
        return session()->has('impersonate');
    }
}
