<?php

namespace Vtec\Crud\Http\Controllers;

use Illuminate\Http\Request;

class UploadController
{
    /**
     * Image upload from Wysiwyg.
     *
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request)
    {
        if ($file = $request->file('file')) {
            $path = $file->storePublicly('uploads', ['disk' => 'public']);

            return [
                'location' => url("/storage/$path"),
            ];
        }

        return abort(400);
    }
}
