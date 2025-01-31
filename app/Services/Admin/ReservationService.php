<?php

namespace App\Services\Admin;

use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservationService
{
    public function getAllReservations()
    {
        $reservations = Reservation::orderBy('id', 'desc')->get();

        if ($reservations->isEmpty()) {
            return ['status' => 'empty', 'message' => 'we have no Reservations yet'];
        }

        return ['status' => 'success', 'data' => ReservationResource::collection($reservations)];
    }

    public function updateReservationStatus(Request $request, $reservationId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ['status' => 'error', 'errors' => $validator->errors()];
        }

        $reservation = Reservation::find($reservationId);

        if (!$reservation) {
            return ['status' => 'error', 'message' => 'Reservation not found'];
        }

        $reservation->update(['status' => $request->status]);

        return ['status' => 'success', 'message' => 'Reservation status updated successfully'];
    }
}
