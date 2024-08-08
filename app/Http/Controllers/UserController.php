<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class UserController extends Controller
{
    // Listar usuários
    public function index()
    {
        //recuperar os registros do banco de dados
        $users = User::get();

        //Carregar a VIEW
        return view('users.index', ['users' => $users]);
    }

    public function import(Request $request)
    {
        // Validar o arquivo
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ],[
            'file.required' => 'O campo arquivo é obrigatório.',
            'file.mimes' => 'Arquivo inválido, necessário enviar arquivo CSV.',
            'file.max' => 'Tamanho do arquivo execede :max Mb.',
        ]);

        // Criar o array com as colunas no banco de dados
        $headers = ['name', 'email', 'password'];

        // Receber o arquivo, ler os dados e converter a string em array
        $dataFile = array_map('str_getcsv', file($request->file('file')));

        // Criar a variável para receber a quantidade de registros cadastrados
        $numbewRegisteredRecords = 0;

        $emailAlreadyRegistered = false;

        // Percorrer as linhas do arquivo CSV
        foreach ($dataFile as $keyData => $row) {
            // Converter a linha em array
            $values = explode(';', $row[0]);

            foreach ($headers as $key => $header) {

                // Atribuir o valor ao elemento do array
                $arrayValues[$keyData][$header] = $values[$key];

                // Verifica se a coluna é e-mail
                if ($header == "email") {
                    // Verificar de o e-mail já está cadastrado no banco de dados
                    if (uSER::where('email', $arrayValues[$keyData]['email'])->first()) {

                        // Atribuir o e-mail na lista de e-mails já cadastrados
                        $emailAlreadyRegistered .= $arrayValues[$keyData]['email'] . ", ";
                    }
                }

                // Verificar se a coluna é senha
                if ($header == "password") {
                    // criptografar a senha
                    $arrayValues[$keyData][$header] = Hash::make($arrayValues[$keyData]['password'], ['rounds' => 12]);

                    // Atribuir a senha ao elemento do array, Gerar uma senha aleatória com 7 caracteres
                    // $arrayValues[$keyData][$header] = Hash::make(Str::random(7), ['rounds' => 12]);
                }




            }
            //Incrementar mais um registro na quantidade de registro que serão cadastrados
            $numbewRegisteredRecords++;
        }

        // Verificar se existe e-mail já cadastrado, retorna erro e não encontra no banco de dados
        if ($emailAlreadyRegistered) {
            // Redirecionar o usuário para a página anterior e enviar a mensagem de error
            return back()->with('error', 'Dados não importados. Existem e-mails já cadastrados.: <br>Quantidade: ' . $emailAlreadyRegistered);
        }

        // Cadastrar registros no banco de dados
        User::insert($arrayValues);

        //Redirecionar o usuário para a página anterior e enviar a mensagem de sucesso
        return back()->with('success', 'Dados importados com sucesso. <br>Quantidade: ' . $numbewRegisteredRecords);
    }


}
