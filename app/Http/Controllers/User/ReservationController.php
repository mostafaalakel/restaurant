<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Http\Resources\ReservationResource;
use App\Services\User\ReservationService;

class ReservationController extends Controller
{
    use ApiResponseTrait;

    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function createReservation(Request $request)
    {
        $result = $this->reservationService->createReservation($request);

        if ($result['status'] == 'error') {
            return isset($result['errors'])
                ? $this->validationErrorResponse($result['errors'])
                : $this->apiResponse('error', $result['message']);
        }

        return $this->createdResponse([], $result['message']);
    }

    public function showReservation()
    {
        $result = $this->reservationService->getReservations();

        if ($result['status'] == 'error') {
            return $this->retrievedResponse(null, $result['message']);
        }

        $userReservationsResource = ReservationResource::collection($result['data']);
        return $this->retrievedResponse($userReservationsResource, 'Reservations retrieved successfully');
    }

    public function deleteReservation($reservationId)
    {
        $result = $this->reservationService->deleteReservation($reservationId);

        if ($result['status'] == 'error') {
            return $this->notFoundResponse($result['message']);
        }

        return $this->apiResponse('success', $result['message']);
    }

    public function updateReservation(Request $request, $reservationId)
    {
        $result = $this->reservationService->updateReservation($request, $reservationId);

        if ($result['status'] == 'error') {
            return isset($result['errors'])
                ? $this->validationErrorResponse($result['errors'])
                : $this->notFoundResponse($result['message']);
        }

        return $this->apiResponse('success', $result['message']);
    }
}
