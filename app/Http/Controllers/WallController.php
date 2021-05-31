<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wall;
use App\Models\User;
use App\Models\WallLike;

class WallController extends Controller
{
    public function getAll()
    {
        $retorno = ['error' => '', 'list' => []];

        $user = auth()->user();
        
        $walls = Wall::all();

        foreach($walls as $key => $value) {
            $walls[$key]['likes'] = 0;
            $walls[$key]['liked'] = false;

            $likes = WallLike::where('id_wall', $value['id'])->count();
            $walls[$key]['likes'] = $likes;

            $meLikes = WallLike::where('id_wall', $value['id'])->where('id_user', $user['id'])->count();

            if($meLikes > 0) {
                $walls[$key]['liked'] = true;
            }
        }
        
        $retorno['list'] = $walls;

        return $retorno;
    }

    public function like($id)
    {
        $retorno = ['error' => ''];

        $user = auth()->user();

        $meLike = WallLike::where('id_wall', $id)->where('id_user', $user['id'])->count();

        if($meLike) {
            WallLike::where('id_wall', $id)->where('id_user', $user['id'])->delete();
            $retorno['liked'] = false;
        } else {
            $newLike = new WallLike();
            $newLike->id_wall = $id;
            $newLike->id_user = $user['id'];
            $newLike->save();
            $retorno['liked'] = true;
        }

        $retorno['likes'] = WallLike::where('id_wall', $id)->count();

        return $retorno;
    }
}
