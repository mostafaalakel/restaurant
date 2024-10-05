<?php

namespace App\Http\Traits\baseTrait;

use App\Http\Traits\ApiResponseTrait;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ReservationResource;

trait ReservationTrait
{
    use ApiResponseTrait;
    public function showReservation()
    {
        $Reservations = ReservationResource::collection(Reservation::orderBy('id', 'desc')->get());
        if ($Reservations->isEmpty()) {
            return $this->notFoundResponse( 'You have no Reservations yet');
        }
        return $this->retrievedResponse($Reservations,  'the Reservation are retrieved successfully' );
    }

    public function updateReservationStatus(Request $request, $id)
    {
        $rules = ['status' => 'required'];
        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $Reservation_status = Reservation::findOrFail($id);
        $Reservation_status->update([
            'status' => 'completed'
        ]);

        return $this->updatedResponse(null, 'Reservation status updated successfully');
    }
}
