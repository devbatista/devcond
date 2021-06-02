<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\FoundAndLost;

class FoundAndLostController extends Controller
{
    public function getAll()
    {
        $retorno = ['error' => ''];

        $lost = FoundAndLost::where('status', 'LOST')->orderBy('date_created', 'DESC')->orderBy('id', 'DESC')->get();
        foreach ($lost as $key => $value) {
            $lost[$key]['date_created'] = date('d/m/Y', strtotime($value['date_created']));
            $lost[$key]['photo'] = asset('storage/' . $value['photo']);
        }
        $retorno['lost'] = $lost;

        $recovered = FoundAndLost::where('status', 'RECOVERED')->orderBy('date_created', 'DESC')->orderBy('id', 'DESC')->get();
        foreach ($recovered as $key => $value) {
            $recovered[$key]['date_created'] = date('d/m/Y', strtotime($value['date_created']));
            $recovered[$key]['photo'] = asset('storage/' . $value['photo']);
        }
        $retorno['recovered'] = $recovered;

        return $retorno;
    }

    public function insert(Request $request)
    {
        $retorno = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'description' => 'required',
            'where' => 'required',
            'photo' => 'required|file|mimes:jpg,png',
        ]);
        if(!$validator->fails()) {
            $description = $request->input('description');
            $where = $request->input('where');
            $file = $request->file('photo')->store('public');
            $file = explode('public/', $file);
            $photo = $file[1];

            $newLost = new FoundAndLost();
            $newLost->photo = $photo;
            $newLost->description = $description;
            $newLost->where = $where;
            $newLost->date_created = date('Y-m-d');
            $newLost->save();
        } else {
            $retorno['error'] = $validator->errors()->first();
            return $retorno;
        }

        return $retorno;
    }

    public function update($id, Request $request)
    {
        $retorno = ['error' => ''];

        $status = $request->input('status');
        if($status && in_array($status, ['lost', 'recovered'])) {
            $item = FoundAndLost::find($id);
            if($item) {
                $item->status = $status;
                $item->save();
            } else {
                $retorno['error'] = 'Produto inexistente';
                return $retorno;
            }
        } else {
            $retorno['error'] = 'Status inexistente';
            return $retorno;
        }

        return $retorno;
    }
}
