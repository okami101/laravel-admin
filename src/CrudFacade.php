<?php

namespace Vtec\Crud;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Vtec\Crud\CrudClass
 */
class CrudFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'crud';
    }
}
