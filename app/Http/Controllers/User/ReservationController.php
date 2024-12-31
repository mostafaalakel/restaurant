<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Models\Reservation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ReservationResource;

class ReservationController extends Controller
{
  use ApiResponseTrait;
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
      return $this->validationErrorResponse($validate->errors());
    }

    $reservation = Reservation::create([
      'user_id' => Auth::guard('user')->id(),
      'num_people' => $request->num_people,
      'reservation_time' => $request->reservation_time,
      'special_request' => $request->special_request
    ]);

    if ($reservation) {
      return $this->createdResponse([], 'Reservation created successfully');
    } else {
      return $this->apiResponse(500, 'Failed to create reservation');
    }
  }

  public function showReservation()
  {
    $userReservation = Auth::guard('user')->user()->reservations()->get();

    if (!$userReservation->isEmpty()) {
      $userReservationResource = ReservationResource::collection($userReservation);
      return $this->retrievedResponse($userReservationResource, 'Reservation retrieved successfully');
    } else {
      return $this->apiResponse(404, 'No reservations found');
    }
  }

  public function deleteReservation($reservationId)
  {
    $reservation = Reservation::findOrFail($reservationId);
    $reservation->delete();

    return $this->apiResponse(200, 'Reservation deleted successfully');
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
      return $this->validationErrorResponse($validate->errors());
    }

    $reservation = Reservation::findOrFail($reservationId);
    $reservation->update([
      'num_people' => $request->num_people,
      'reservation_time' => $request->reservation_time,
      'special_request' => $request->special_request
    ]);

    return $this->apiResponse(200, 'Reservation updated successfully');
  }
}
