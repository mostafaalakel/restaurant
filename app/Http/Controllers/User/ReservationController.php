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
  // public function createReservation(Request $request)
  // {
  //   $rules = [
  //     'num_people' => 'required',
  //     'reservation_time' => 'required|date_format:Y-m-d H:i:s|after_or_equal:now',
  //     'special_request' => 'required'
  //   ];
  //   $validate = Validator::make($request->all(), $rules);

  //   if ($validate->fails()) {
  //     return response()->json(['status' => 'validation_error', 'errors' => $validate->errors()]);
  //   }

  //   $createReservation = Reservation::create([
  //     'user_id' => Auth::id(),
  //     'num_people' => $request->num_people,
  //     'reservation_time' => $request->reservation_time,
  //     'special_request' => $request->special_request
  //   ]);

  //   if ($createReservation) {
  //     return $this->apiResponse(201, 'Reservation done Successfully');
  //   } else {
  //     return $this->apiResponse(404, 'Reservation failed');
  //   }
  // }

  // public function showReservation($user_id)
  // {
  //   $userReservation = User::find($user_id)->reservations;
  //   if (!$userReservation->isEmpty()) {
  //     $userReservation = ReservationResource::collection($userReservation);
  //     return $this->apiResponse(200, 'your Reservation got it Successfully', $userReservation);
  //   } else {
  //     return $this->apiResponse(404, 'you don`t have any Reservation');
  //   }
  // }

  // public function deleteReservation($id)
  // {
  //   $reservation = Reservation::findOrFail($id);
  //   $reservation->delete();
  //   return $this->apiResponse('200', message: 'reservation deleted successfully');
  // }

  // public function updateReservation(Request $request, $id)
  // {

  //   $rules = [
  //     'num_people' => 'required',
  //     'reservation_time' => 'required|date_format:Y-m-d H:i:s|after_or_equal:now',
  //     'special_request' => 'required'
  //   ];
  //   $validate = Validator::make($request->all(), $rules);

  //   if ($validate->fails()) {
  //     return response()->json(['status' => 'validation_error', 'errors' => $validate->errors()]);
  //   }

  //   $reservation = Reservation::findOrFail($id);
  //   $reservation->update([
  //     'user_id' => Auth::id(),
  //     'num_people' => $request->num_people,
  //     'reservation_time' => $request->reservation_time,
  //     'special_request' => $request->special_request
  //   ]);

  //   return $this->apiResponse('200', 'reservation updated successfully');
  // }

  public function createReservation(Request $request)
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

    $reservation = Reservation::create([
      'user_id' => Auth::id(),
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

  public function showReservation($user_id)
  {
    $userReservation = User::find($user_id)->reservations;

    if (!$userReservation->isEmpty()) {
      $userReservationResource = ReservationResource::collection($userReservation);
      return $this->retrievedResponse($userReservationResource, 'Reservation retrieved successfully');
    } else {
      return $this->apiResponse(404, 'No reservations found for this user');
    }
  }

  public function deleteReservation($id)
  {
    $reservation = Reservation::findOrFail($id);
    $reservation->delete();

    return $this->apiResponse(200, 'Reservation deleted successfully');
  }

  public function updateReservation(Request $request, $id)
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

    $reservation = Reservation::findOrFail($id);
    $reservation->update([
      'num_people' => $request->num_people,
      'reservation_time' => $request->reservation_time,
      'special_request' => $request->special_request
    ]);

    return $this->apiResponse(200, 'Reservation updated successfully');
  }
}
