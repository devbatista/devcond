<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Area;
use App\Models\AreaDisabledDay;
use App\Models\Reservation;
use App\Models\Unit;

class ReservationController extends Controller
{
    public function getReservations()
    {
        $retorno = ['error' => '', 'list' => []];
        $daysHelper = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];

        $areas = Area::where('allowed', 1)->get();

        foreach ($areas as $area) {
            $dayList = explode(',', $area['days']);

            $dayGroups = [];

            // Adicionando o primeiro dia
            $lastDay = intval(current($dayList));
            $dayGroups[] = $daysHelper[$lastDay];
            array_shift($dayList);

            // Adicionando os dias relevantes
            foreach ($dayList as $day) {
                if (intval($day) != $lastDay + 1) {
                    $dayGroups[] = $daysHelper[$lastDay];
                    $dayGroups[] = $daysHelper[$day];
                }
                $lastDay = intval($day);
            }

            // Adicionando o último dia
            $dayGroups[] = $daysHelper[end($dayList)];

            // Juntando as datas (Dia1-Dia2)
            $dates = '';
            $close = 0;
            foreach ($dayGroups as $group) {
                if (!$close) {
                    $dates .= $group;
                } else {
                    $dates .= '-' . $group . ',';
                }
                $close = 1 - $close;
            }

            $dates = explode(',', $dates);
            array_pop($dates);

            // Adicionando o TIME
            $start = date('H:i', strtotime($area['start_time']));
            $end = date('H:i', strtotime($area['end_time']));

            foreach ($dates as $key => $value) {
                $dates[$key] .= ' ' . $start . ' às ' . $end;
            }

            $retorno['list'][] = [
                'id' => $area['id'],
                'cover' => asset('storage/' . $area['cover']),
                'title' => $area['title'],
                'dates' => $dates,
            ];
        }

        return $retorno;
    }

    public function getDisabledDates($id)
    {
        $retorno = ['error' => '', 'list' => []];
        $area = Area::find($id);

        if (!$area) {
            $retorno['error'] = 'Área inexistente';
            return $retorno;
        }

        // Dias desativados por determinação da administração
        $disabledDays = AreaDisabledDay::where('id_area', $id)->get();
        foreach ($disabledDays as $disabledDay) {
            $retorno['list'][] = $disabledDay['day'];
        }

        // Dias que não estão permitidos
        $allowedDays = explode(',', $area['days']);
        $offDays = [];
        for ($i = 0; $i < 7; $i++) {
            if (!in_array($i, $allowedDays)) {
                $offDays[] = $i;
            }
        }

        // Listar os dias não disponíveis +3 meses pra frente
        $start = time();
        $end = strtotime('+3 months');
        $current = $start;
        $keep = true;

        for ($current = $start; $current < $end; $current = strtotime('+1 day', $current)) {
            $wd = date('w', $current);
            if (in_array($wd, $offDays)) {
                $retorno['list'][] = date('Y-m-d', $current);
            }
        }

        return $retorno;
    }

    public function setReservation($id, Request $request)
    {
        $retorno = ['error' => ''];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i:s',
            'property' => 'required',
        ]);

        if ($validator->fails()) {
            $retorno['error'] = $validator->errors()->first();
            return $retorno;
        }

        $date = $request->input('date');
        $time = $request->input('time');
        $property = $request->input('property');

        $unit = Unit::find($property);
        $area = Area::find($id);

        if (!($unit && $area)) {
            $retorno['error'] = 'Dados incorretos';
            return $retorno;
        }

        $can = true;
        $weekday = date('w', strtotime($date));

        // Verifica se a reserva está dentro da disponibilidade
        $allowedDays = explode(',', $area['days']);
        if (!in_array($weekday, $allowedDays)) {
            $can = false;
        } else {
            $start = strtotime($area['start_time']);
            $end = strtotime('-1 hour', strtotime($area['end_time']));
            $revtime = strtotime($time);

            if ($revtime < $start || $revtime > $end) {
                $can = false;
            };
        }

        // Verifica se está dentro das datas desativadas
        $existingDisabledDays = AreaDisabledDay::where('id_area', $id)->where('day', $date)->count();
        if ($existingDisabledDays) {
            $can = false;
        }

        // Verifica se não existe reserva no mesmo período solicitado
        $existingReservations = Reservation::where('id_area', $id)->where('reservation_date', $date . ' ' . $time)->count();
        if ($existingReservations) {
            $can = false;
        }

        if (!$can) {
            $retorno['error'] = 'Reserva não permitida nesse período';
            return $retorno;
        }

        $newReservation = new Reservation();
        $newReservation->id_unit = $property;
        $newReservation->id_area = $id;
        $newReservation->reservation_date = $date . ' ' . $time;
        $newReservation->save();

        return $retorno;
    }

    public function getTimes($id, Request $request)
    {
        $retorno = ['error' => '', 'list' => []];

        $validator = Validator::make($request->all(), [
            'date' => 'required|date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            $retorno['error'] = $validator->errors()->first();
            return $retorno;
        }

        $date = $request->input('date');
        $area = Area::find($id);

        if (!$area) {
            $retorno['error'] = 'Área inexistente';
            return $retorno;
        }

        $can = true;

        // Verifica se o dia está desativado
        $existingDisabledDay = AreaDisabledDay::where('id_area', $id)->where('day', $date)->count();
        if ($existingDisabledDay > 0) {
            $can = false;
        }

        // Verifica se o dia está permitido
        $allowedDays = explode(',', $area['days']);
        $weekday = date('w', strtotime($date));
        if (!in_array($weekday, $allowedDays)) {
            $can = false;
        }

        if ($can) {
            $times = [];
            $start = strtotime($area['start_time']);
            $end = strtotime($area['end_time']);

            for ($lastTime = $start; $lastTime < $end; $lastTime = strtotime('+1 hour', $lastTime)) {
                $times[] = $lastTime;
            }

            $timeList = [];
            foreach ($times as $time) {
                $timeList[] = [
                    'id' => date('H:i:s', $time),
                    'title' => date('H:i', $time) . ' - ' . date('H:i', strtotime('+1 hour', $time)),
                ];
            }

            // Removendo as reservas
            $reservations = Reservation::where('id_area', $id)->whereBetween('reservation_date', [$date . ' 00:00:00', $date . ' 23:59:59'])->get();

            $toRemove = [];
            foreach ($reservations as $reservation) {
                $time = date('H:i:s', strtotime($reservation['reservation_date']));
                $toRemove[] = $time;
            }

            foreach ($timeList as $timeItem) {
                if (!in_array($timeItem['id'], $toRemove)) {
                    $retorno['list'][] = $timeItem;
                }
            }
        }

        return $retorno;
    }

    public function myReservations(Request $request)
    {
        $retorno = ['error' => '', 'list' => []];

        $property = $request->input('property');
        if (!$property) {
            $retorno['error'] = 'É obrigatório determinar a propriedade';
            return $retorno;
        }

        $user = auth()->user();
        $unit = Unit::where('id', $property)->where('id_owner', $user['id'])->first();
        if (!$unit) {
            $retorno['error'] = 'Propriedade inexistente ou não é sua';
            return $retorno;
        }

        $reservations = Reservation::where('id_unit', $property)->orderBy('reservation_date', 'DESC')->get();
        foreach ($reservations as $reservation) {
            $area = Area::find($reservation['id_area']);

            $dateRev = date('d/m/Y H:i', strtotime($reservation['reservation_date']));
            $afterTime = date('H:i', strtotime('+1 hour', strtotime($reservation['reservation_date'])));
            $dateRev .= ' à ' . $afterTime;

            $retorno['list'][] = [
                'id' => $reservation['id'],
                'id_area' => $reservation['id_area'],
                'title' => $area['title'],
                'cover' => asset('storage/'.$area['cover']),
                'date_reserved' => $dateRev,
            ];
        }

        return $retorno;
    }

    public function delMyReservation($id)
    {
        $retorno = ['error' => ''];

        $user = auth()->user();
        $reservation = Reservation::find($id);

        if(!$reservation) {
            $retorno['error'] = 'Reserva inexistente';
            return $retorno;
        }

        $unit = Unit::where('id', $reservation['id_unit'])->where('id_owner', $user['id'])->count();
        if(!$unit) {
            $retorno['error'] = 'Esta reserva não é sua';
            return $retorno;
        }

        Reservation::find($id)->delete();

        return $retorno;
    }
}
