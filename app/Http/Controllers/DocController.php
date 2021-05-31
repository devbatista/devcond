<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Doc;

class DocController extends Controller
{
    public function getAll()
    {
        $retorno = ['error' => ''];

        $docs = Doc::all();

        foreach ($docs as $key => $value) {
            $docs[$key]['file_url'] = asset('storage/'.$value['file_url']);
        }

        $retorno['list'] = $docs;

        return $retorno;
    }
}
