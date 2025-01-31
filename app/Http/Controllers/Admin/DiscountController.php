<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiscountResource;
use App\Http\Traits\ApiResponseTrait;
use App\Services\Admin\DiscountService;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    use ApiResponseTrait;

    protected $discountService;

    public function __construct(DiscountService $discountService)
    {
        $this->discountService = $discountService;
    }

    public function getAllGeneralDiscounts()
    {
        $generalDiscounts = $this->discountService->getAllGeneralDiscounts();
        return $this->retrievedResponse(DiscountResource::collection($generalDiscounts));
    }

    public function getAllCodeDiscounts()
    {
        $codeDiscounts = $this->discountService->getAllCodeDiscounts();
        return $this->retrievedResponse(DiscountResource::collection($codeDiscounts));
    }

    public function storeGeneralDiscount(Request $request)
    {
        $result = $this->discountService->storeGeneralDiscount($request);

        if ($result['status'] == 'error') {
            return $this->validationErrorResponse($result['message']);
        }

        return $this->createdResponse(null, 'generalDiscount created successfully');
    }

    public function storeCodeDiscount(Request $request)
    {
        $result = $this->discountService->storeCodeDiscount($request);

        if ($result['status'] == 'error') {
            return $this->validationErrorResponse($result['message']);
        }

        return $this->createdResponse(null, 'CodeDiscount created successfully');
    }

    public function updateGeneralDiscount(Request $request, $generalDiscountId)
    {
        $result = $this->discountService->updateDiscount($generalDiscountId, false, $request);

        if ($result['status'] == 'error') {
            return isset($result['errors'])
                ? $this->validationErrorResponse($result['errors'])
                : $this->notFoundResponse($result['message']);
        }

        return $this->updatedResponse(null, 'General discount updated successfully');
    }

    public function updateCodeDiscount(Request $request, $CodeDiscountId)
    {
        $result = $this->discountService->updateDiscount($CodeDiscountId, true, $request);

        if ($result['status'] == 'error') {
            return isset($result['errors'])
                ? $this->validationErrorResponse($result['errors'])
                : $this->notFoundResponse($result['message']);
        }

        return $this->updatedResponse(null, 'Code discount updated successfully');
    }

    public function deleteGeneralDiscount($generalDiscountId)
    {
        $result = $this->discountService->deleteDiscount($generalDiscountId, false);

        if ($result['status'] == 'error') {
            return $this->notFoundResponse($result['message']);
        }

        return $this->deletedResponse('General discount deleted successfully');
    }

    public function deleteCodeDiscount($CodeDiscountId)
    {
        $result = $this->discountService->deleteDiscount($CodeDiscountId, true);

        if ($result['status'] == 'error') {
            return $this->notFoundResponse($result['message']);
        }

        return $this->deletedResponse('Code discount deleted successfully');
    }

    public function attachGeneralDiscountToFood(Request $request, $discountId)
    {
        $result = $this->discountService->attachDiscountToFood($discountId, false, $request->food_ids);

        if ($result['status'] == 'error') {
            if (isset($result['errors'])) {
                return $this->validationErrorResponse($result['errors']);
            } else {
                return $this->notFoundResponse($result['message']);
            }
        }

        return $this->createdResponse(null, 'Foods attached to general discount successfully');
    }

    public function attachCodeDiscountToFood(Request $request, $discountId)
    {
        $result = $this->discountService->attachDiscountToFood($discountId, true, $request->food_ids);

        if ($result['status'] == 'error') {
            if (isset($result['errors'])) {
                return $this->validationErrorResponse($result['errors']);
            } else {
                return $this->notFoundResponse($result['message']);
            }
        }

        return $this->createdResponse(null, 'Foods attached to Code discount successfully');
    }

    public function detachFoodFromGeneralDiscount(Request $request, $generalDiscountId)
    {
        $result = $this->discountService->detachFoodFromDiscount($generalDiscountId, false, $request->food_ids);

        if ($result['status'] == 'error') {
            if (isset($result['errors'])) {
                return $this->validationErrorResponse($result['errors']);
            } else {
                return $this->notFoundResponse($result['message']);
            }
        }

        return $this->deletedResponse('Food successfully detached from the general discount');
    }

    public function detachFoodFromCodeDiscount(Request $request, $CodeDiscountId)
    {
        $result = $this->discountService->detachFoodFromDiscount($CodeDiscountId, true, $request->food_ids);

        if ($result['status'] == 'error') {
            if (isset($result['errors'])) {
                return $this->validationErrorResponse($result['errors']);
            } else {
                return $this->notFoundResponse($result['message']);
            }
        }

        return $this->deletedResponse('Food successfully detached from the code discount');
    }

    public function showFoodByGeneralDiscount($generalDiscountId)
    {
        $result = $this->discountService->showFoodByDiscount($generalDiscountId, false);

        return $result['status'] === 'error'
            ? $this->notFoundResponse($result['message'])
            : $this->retrievedResponse($result['data']);
    }

    public function showFoodByCodeDiscount($codeDiscountId)
    {
        $result = $this->discountService->showFoodByDiscount($codeDiscountId, true);

        return $result['status'] === 'error'
            ? $this->notFoundResponse($result['message'])
            : $this->retrievedResponse($result['data']);
    }
}
