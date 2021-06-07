<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Unit;
use App\Models\UnitPeople;
use App\Models\UnitVehicle;
use App\Models\UnitPet;
use Illuminate\Auth\Events\Validated;

class UnitController extends Controller
{
    public function getInfo($id)
    {
        $retorno = ['error' => ''];

        $unit = Unit::find($id);
        $user = auth()->user();

        if ($unit) {
            if ($unit['id_owner'] === $user['id']) {
                $people = UnitPeople::where('id_unit', $id)->get();
                $vehicles = UnitVehicle::where('id_unit', $id)->get();
                $pets = UnitPet::where('id_unit', $id)->get();

                foreach ($people as $key => $value) {
                    $people[$key]['birthday'] = date("d/m/Y", strtotime($value['birthday']));
                }

                $retorno['people'] = $people;
                $retorno['vehicles'] = $vehicles;
                $retorno['pets'] = $pets;
            } else {
                $retorno['error'] = 'Essa propriedade não é sua';
                return $retorno;
            }
        } else {
            $retorno['error'] = 'Propriedade inexiste';
            return $retorno;
        }

        return $retorno;
    }

    public function addPerson($id, Request $request)
    {
        $retorno = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'birthday' => 'required|date'
        ]);

        if (!$validator->fails()) {
            $name = $request->input('name');
            $birthday = $request->input('birthday');

            $newPerson = new UnitPeople();
            $newPerson->id_unit = $id;
            $newPerson->name = $name;
            $newPerson->birthday = $birthday;
            $newPerson->save();
        } else {
            $retorno['error'] = $validator->errors()->first();
            return $retorno;
        }

        return $retorno;
    }

    public function addVehicle($id, Request $request)
    {
        $retorno = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'color' => 'required',
            'plate' => 'required'
        ]);

        if (!$validator->fails()) {
            $title = $request->input('title');
            $color = $request->input('color');
            $plate = $request->input('plate');

            $newVehicle = new UnitVehicle();
            $newVehicle->id_unit = $id;
            $newVehicle->title = $title;
            $newVehicle->color = $color;
            $newVehicle->plate = $plate;
            $newVehicle->save();
        } else {
            $retorno['error'] = $validator->errors()->first();
            return $retorno;
        }

        return $retorno;
    }

    public function addPet($id, Request $request)
    {
        $retorno = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'race' => 'required',
        ]);

        if (!$validator->fails()) {
            $name = $request->input('name');
            $race = $request->input('race');

            $newPet = new UnitPet();
            $newPet->id_unit = $id;
            $newPet->name = $name;
            $newPet->race = $race;
            $newPet->save();
        } else {
            $retorno['error'] = $validator->errors()->first();
            return $retorno;
        }

        return $retorno;
    }

    public function removePerson($id, Request $request)
    {
        $retorno = ['error' => ''];

        $user = auth()->user();
        $unit = Unit::where('id', $id)->where('id_owner', $user['id'])->first();
        $person = UnitPeople::where('id', $request->input('id'))->first();
        $idPerson = $request->input('id');
        $removePerson = UnitPeople::where('id', $idPerson)->where('id_unit', $id)->first();

        if (!$idPerson) {
            $retorno['error'] = 'Dados não enviados';
            return $retorno;
        }

        if (!$person) {
            $retorno['error'] = 'Pessoa inexistente';
            return $retorno;
        }

        if (!$unit) {
            $retorno['error'] = 'Essa propriedade não é desse dono ou não existe';
            return $retorno;
        }
        
        if(!$removePerson) {
            $retorno['error'] = 'A pessoa selecionada não é moradora dessa propriedade';
            return $retorno;
        }

        UnitPeople::where('id', $idPerson)->where('id_unit', $id)->delete();

        return $retorno;
    }

    public function removeVehicle($id, Request $request)
    {
        $retorno = ['error' => ''];

        $user = auth()->user();
        $unit = Unit::where('id', $id)->where('id_owner', $user['id'])->first();
        $vehicle = UnitVehicle::where('id', $request->input('id'))->first();
        $idVehicle = $request->input('id');
        $removeVehicle = UnitVehicle::where('id', $idVehicle)->where('id_unit', $id)->first();

        if (!$idVehicle) {
            $retorno['error'] = 'Dados não enviados';
            return $retorno;
        }

        if (!$vehicle) {
            $retorno['error'] = 'Veículo inexistente';
            return $retorno;
        }

        if (!$unit) {
            $retorno['error'] = 'Esse veículo não é desse proprietário ou não existe';
            return $retorno;
        }
        
        if(!$removeVehicle) {
            $retorno['error'] = 'Veículo selecionado não é de alguém dessa propriedade';
            return $retorno;
        }

        UnitVehicle::where('id', $idVehicle)->where('id_unit', $id)->delete();

        return $retorno;
    }

    public function removePet($id, Request $request)
    {
        $retorno = ['error' => ''];

        $user = auth()->user();
        $unit = Unit::where('id', $id)->where('id_owner', $user['id'])->first();
        $vehicle = UnitPet::where('id', $request->input('id'))->first();
        $idPet = $request->input('id');
        $removePet = UnitPet::where('id', $idPet)->where('id_unit', $id)->first();

        if (!$idPet) {
            $retorno['error'] = 'Dados não enviados';
            return $retorno;
        }

        if (!$pet) {
            $retorno['error'] = 'Pet inexistente';
            return $retorno;
        }

        if (!$unit) {
            $retorno['error'] = 'Esse pet não é desse proprietário ou não existe';
            return $retorno;
        }
        
        if(!$removePet) {
            $retorno['error'] = 'Pet selecionado não é de alguém dessa propriedade';
            return $retorno;
        }

        UnitPet::where('id', $idPet)->where('id_unit', $id)->delete();

        return $retorno;
    }
}
