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
            return response()->json(['message' => 'Nenhum usuário encontrado com este número.', 401]);
        }
        // enviar o usuário um ont-time user codigo
        $user->notify(new LoginNeedsVerification());

        // retornar a resposta
        return response()->json(['message' => 'SMS enviado.']);

    }
}
