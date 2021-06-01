<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

                    foreach($photos as $photo) {
                        if(!empty($photo)) {
                            $photoList[] = asset('storage/'.$photo);
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
}
