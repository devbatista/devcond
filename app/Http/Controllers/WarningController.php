<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Warning;
use App\Models\Unit;

class WarningController extends Controller
{
    public function getMyWarnings(Request $request)
    {
        $retorno = ['array' => ''];

        $property = $request->input('property');
        if ($property) {
            $user = auth()->user();
            $unit = Unit::where('id', $property)->where('id_owner', $user['id'])->count();

            if ($unit) {
                $warnings = Warning::where('id_unit', $property)->orderBy('date_created', 'DESC')->orderBy('id', 'DESC')->get();

                foreach ($warnings as $key => $value) {
                    $warnings[$key]['date_created'] = date('d/m/Y', strtotime($value['date_created']));
                    $photoList = [];
                    $photos = explode(',', $value['photos']);

                    foreach ($photos as $photo) {
                        if (!empty($photo)) {
                            $photoList[] = asset('storage/' . $photo);
                        }
                    }

                    $warnings[$key]['photos'] = $photoList;
                }

                $retorno['list'] = $warnings;
            } else {
                $retorno['error'] = 'Esta unidade não é sua';
            }
        } else {
            $retorno['error'] = 'A propriedade é necessária!';
        }

        return $retorno;
    }

    public function addWarningFile(Request $request)
    {
        $retorno = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'photo' => 'required|file|mimes:jpg,png'
        ]);

        if (!$validator->fails()) {
            $file = $request->file('photo')->store('public');

            $retorno['photo'] = asset(Storage::url($file));
        } else {
            $retorno['error'] = $validator->errors()->first();
            return $retorno;
        }

        return $retorno;
    }

    public function setWarning(Request $request)
    {
        $retorno = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'property' => 'required',
        ]);

        if (!$validator->fails()) {
            $title = $request->input('title');
            $property = $request->input('property');
            $list = $request->input('list');

            $newWarn = new Warning();
            $newWarn->id_unit = $property;
            $newWarn->title = $title;
            $newWarn->date_created = date('Y-m-d');

            if($list && is_array($list)) {
                $photos = [];

                foreach ($list as $value) {
                    $url = explode('/', $value);
                    $photos[] = end($url);
                }
                
                $newWarn->photos = implode(',', $photos);
            }

            $newWarn->save();
        } else {
            $retorno['error'] = $validator->errors()->first();
        }

        return $retorno;
    }
}
