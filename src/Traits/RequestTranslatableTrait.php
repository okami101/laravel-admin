<?php

namespace Vtec\Crud\Traits;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Trait RequestTranslatableTrait
 *
 * @mixin Model
 */
trait RequestTranslatableTrait
{
    use HasTranslations;

    protected function getLocale(): string
    {
        return request()->get('locale') ?: config('app.locale');
    }
}
