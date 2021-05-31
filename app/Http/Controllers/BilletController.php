<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Billet;
use App\Models\Unit;

class BilletController extends Controller
{
    public function getAll(Request $request)
    {
        $retorno = ['error' => ''];

        $property = $request->input('property');

        if ($property) {
            $user = auth()->user();
            $unit = Unit::where('id', $property)->where('id_owner', $user['id'])->count();

            if ($unit) {

                $billets = Billet::where('id_unit', $property)->get();

                foreach ($billets as $key => $value) {
                    $billets[$key]['file_url'] = asset('storage/' . $value['file_url']);
                }

                $retorno['list'] = $billets;
            } else {
                $retorno['error'] = 'Esta unidade não é sua';
            }
        } else {
            $retorno['error'] = 'A propriedade é obrigaória';
        }

        return $retorno;
    }
}
