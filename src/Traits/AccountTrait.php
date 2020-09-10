<?php

namespace Okami101\LaravelAdmin\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Class AccountTrait
 */
trait AccountTrait
{
    /**
     * Update account infos
     *
     * @param \Illuminate\Http\Request $request
     *
     * @throws \Illuminate\Validation\ValidationException
     *
     * @return Model
     */
    public function updateLoggedUser(Request $request)
    {
        $validated = $this->validate($request, [
            'name' => 'required|max:191',
            'email' => 'required|email|unique:users,email,'.auth()->id(),
        ]);

        /** @var Model $user */
        $user = auth()->user();
        $user->update($validated);

        return $user;
    }

    /**
     * Change password
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     *
     */
    public function changePassword(Request $request)
    {
        /** @var Model $user */
        $user = auth()->user();

        $this->validate($request, [
            'old_password' => 'required|current_password',
            'new_password' => 'required|confirmed|min:8|strong_password',
        ]);

        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        return response()->noContent();
    }
}
