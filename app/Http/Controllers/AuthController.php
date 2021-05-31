<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Unit;

class AuthController extends Controller
{
    public function unauthorized()
    {
        return response()->json([
            'error' => 'Não autorizado'
        ], 401);
    }

    public function register(Request $request)
    {
        $retorno = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'cpf' => 'required|digits:11|unique:users,cpf',
            'password' => 'required',
            'password_confirm' => 'required|same:password',
        ]);

        if (!$validator->fails()) {
            $name = $request->input('name');
            $email = $request->input('email');
            $cpf = $request->input('cpf');
            $pass = $request->input('password');

            $password = password_hash($pass, PASSWORD_DEFAULT);

            $newUser = new User();
            $newUser->name = $name;
            $newUser->email = $email;
            $newUser->cpf = $cpf;
            $newUser->password = $password;
            $newUser->save();

            $token = auth()->attempt([
                'cpf' => $cpf,
                'password' => $pass
            ]);

            if (!$token) {
                $retorno['error'] = 'Ocorreu um erro, entre em contato com o administrador do sistema';
                return $retorno;
            }

            $retorno['token'] = $token;

            $user = auth()->user();
            $retorno['user'] = $user;

            $properties = Unit::select(['id', 'name'])->where('id_owner', $user['id'])->get();
            $retorno['user']['properties'] = $properties;
        } else {
            $retorno['error'] = $validator->errors()->first();
            return $retorno;
        }

        return $retorno;
    }

    public function login(Request $request)
    {
        $retorno = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'cpf' => 'required|digits:11',
            'password' => 'required',
        ]);

        if (!$validator->fails()) {
            $cpf = $request->input('cpf');
            $password = $request->input('password');

            $token = auth()->attempt([
                'cpf' => $cpf,
                'password' => $password,
            ]);

            if (!$token) {
                $retorno['error'] = 'CPF e/ou senha incorretos!';
                return $retorno;
            }

            $retorno['token'] = $token;

            $user = auth()->user();
            $retorno['user'] = $user;

            $properties = Unit::select(['id', 'name'])->where('id_owner', $user['id'])->get();
            $retorno['user']['properties'] = $properties;
        } else {
            $retorno['error'] = $validator->errors()->first();
            return $retorno;
        }

        return $retorno;
    }

    public function validateToken()
    {
        $retorno = ['error' => ''];

        $user = auth()->user();
        $retorno['user'] = $user;

        $properties = Unit::select(['id', 'name'])->where('id_owner', $user['id'])->get();
        $retorno['user']['properties'] = $properties;

        return $retorno;
    }

    public function logout()
    {
        $retorno = ['error' => ''];
        auth()->logout();
        return $retorno;
    }
}
