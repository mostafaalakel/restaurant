<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Services\Admin\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ApiResponseTrait;

    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function addEmployee(Request $request)
    {
        $result = $this->employeeService->addEmployee($request->all());

        return $result['status'] === 'error'
            ? $this->validationErrorResponse($result['errors'])
            : $this->createdResponse(null, $result['message']);
    }

    public function showEmployees()
    {
        $result = $this->employeeService->showEmployees();

        return $result['status'] === 'error'
            ? $this->notFoundResponse($result['message'])
            : $this->retrievedResponse($result['data'], $result['message']);
    }

    public function updateEmployee(Request $request, $employeeId)
    {
        $result = $this->employeeService->updateEmployee($employeeId, $request->all());

        return $result['status'] === 'error'
            ? (isset($result['errors'])
                ? $this->validationErrorResponse($result['errors'])
                : $this->notFoundResponse($result['message']))
            : $this->updatedResponse(null, $result['message']);
    }

    public function deleteEmployee($employeeId)
    {
        $result = $this->employeeService->deleteEmployee($employeeId);

        return $result['status'] === 'error'
            ? $this->notFoundResponse($result['message'])
            : $this->deletedResponse($result['message']);
    }
}
