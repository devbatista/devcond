<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;

class UserController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function getProfile()
    {
        $retorno = ['error' => '', 'list' => []];
        $profile = User::find($this->user['id']);

        if (!$profile) {
            $retorno['error'] = 'Usuário inexistente';
            return $retorno;
        }

        $retorno['list'] = $profile;

        return $retorno;
    }

    public function editProfile(Request $request)
    {
        $retorno = ['error' => ''];

        $profile = User::find($this->user['id']);

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email', Rule::unique('users')->ignore($profile),
            'cpf' => 'required|digits:11', Rule::unique('users')->ignore($profile),
            'password_confirm' => 'same:password',
        ]);

        if ($validator->fails()) {
            $retorno['error'] = $validator->errors()->first();
            return $retorno;
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $cpf = $request->input('cpf');
        $pass = ($request->input('password')) ? $request->input('password') : false;

        $profile->name = $name;
        $profile->email = $email;
        $profile->cpf = $cpf;
        if ($pass) {
            $password = password_hash($pass, PASSWORD_DEFAULT);
            $profile->password = $password;
        }
        $profile->save();

        return $retorno;
    }
}
