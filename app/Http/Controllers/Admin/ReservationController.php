<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\Admin\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    use ApiResponseTrait;

    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function showReservation()
    {
        $result = $this->reservationService->getAllReservations();

        if ($result['status'] === 'empty') {
            return $this->retrievedResponse(null, $result['message']);
        }

        return $this->retrievedResponse($result['data'], 'The reservations are retrieved successfully');
    }

    public function updateReservationStatus(Request $request, $reservationId)
    {
        $result = $this->reservationService->updateReservationStatus($request, $reservationId);

        if ($result['status'] === 'error') {
            return isset($result['errors'])
                ? $this->validationErrorResponse($result['errors'])
                : $this->notFoundResponse($result['message']);
        }

        return $this->updatedResponse(null, $result['message']);
    }
}
