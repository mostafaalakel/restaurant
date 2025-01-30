<?php

namespace App\Services\User;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReservationService
{
    public function createReservation(Request $request)
    {
        $existingReservation = Reservation::where('user_id', Auth::guard('user')->id())
            ->where('status', 'processing')
            ->first();

        if ($existingReservation) {
            $existingReservation->delete();
        }

        $rules = [
            'num_people' => 'required|integer|min:1',
            'reservation_time' => 'required|date_format:Y-m-d H:i:s|after_or_equal:now',
            'special_request' => 'required|string'
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return ['status' => 'error', 'message' => 'Validation error', 'errors' => $validate->errors()];
        }

        $reservation = Reservation::create([
            'user_id' => Auth::guard('user')->id(),
            'num_people' => $request->num_people,
            'reservation_time' => $request->reservation_time,
            'special_request' => $request->special_request
        ]);

        if ($reservation) {
            return ['status' => 'success', 'message' => 'Reservation created successfully'];
        }

        return ['status' => 'error', 'message' => 'Failed to create reservation'];
    }

    public function getReservations()
    {
        $userReservations = Auth::guard('user')->user()->reservations()->get();

        if ($userReservations->isEmpty()) {
            return ['status' => 'error', 'message' => 'No reservations found'];
        }

        return ['status' => 'success', 'data' => $userReservations];
    }

    public function deleteReservation($reservationId)
    {
        $reservation = Reservation::find($reservationId);

        if (!$reservation) {
            return ['status' => 'error', 'message' => 'Reservation not found'];
        }

        $reservation->delete();
        return ['status' => 'success', 'message' => 'Reservation deleted successfully'];
    }

    public function updateReservation(Request $request, $reservationId)
    {
        $rules = [
            'num_people' => 'required|integer|min:1',
            'reservation_time' => 'required|date_format:Y-m-d H:i:s|after_or_equal:now',
            'special_request' => 'required|string'
        ];

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return ['status' => 'error', 'message' => 'Validation error', 'errors' => $validate->errors()];
        }

        $reservation = Reservation::find($reservationId);
        if (!$reservation) {
            return ['status' => 'error', 'message' => 'Reservation not found'];
        }

        $reservation->update([
            'num_people' => $request->num_people,
            'reservation_time' => $request->reservation_time,
            'special_request' => $request->special_request
        ]);

        return ['status' => 'success', 'message' => 'Reservation updated successfully'];
    }
}
