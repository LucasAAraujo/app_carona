<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\LoginNeedsVerification;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function submit(Request $request)
    {
        // validar o número de celular
        $request->validate([
            'phone' => 'required|numeric|min:10'
        ]);

        // achar ou criar um user model
        $user = User::firstOrCreate([
            'phone' => $request->phone
        ]);

        if(!$user) {
            return response()->json(['message' => 'Nenhum usuário encontrado com este número.'], 401);
        }
        // enviar o usuário um ont-time user codigo
        $user->notify(new LoginNeedsVerification());

        // retornar a resposta
        return response()->json(['message' => 'SMS enviado.']);
    }

    public function verify(Request $request)
    {
        // validaçao da requisição
        $request->validate([
            'phone' => 'required|numeric|min:10',
            'login_code' => 'required|numeric|between:111111,999999'
        ]);

        // achar o usuário
        $user = User::where('phone', $request->phone)
            ->where('login_code', $request->login_code)
            ->first();
        // O código providenciado é o mesmo salvo?
        // Se sim, return back com auth token
        if ($user) {
            $user->update([
                'login_code' => null
            ]);

            return $user->createToken($request->login_code)->plainTextToken;
        }
        //Se não, return back com uma menssagem
        return response()->json(['message' => 'Código de verificação inválido.'], 401);
    }
}
