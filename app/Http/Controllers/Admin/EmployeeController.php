<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    use ApiResponseTrait;

    public function addEmployee(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date|before_or_equal:today',
            'position' => 'required|max:255',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        Employee::create($request->all());
        return $this->createdResponse(null, 'Employee created successfully');
    }

    public function showEmployees()
    {
        $employees = Employee::all();
        return $this->retrievedResponse($employees, 'Employee retrieved successfully');
    }

    public function updateEmployee(Request $request, $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'salary' => 'sometimes|required|numeric|min:0',
            'hire_date' => 'sometimes|required|date|before_or_equal:today',
            'position' => 'sometimes|required|string|max:255'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $employee->update($request->all());
        return $this->updatedResponse(null,'Employee updated successfully');
    }

    public function deleteEmployee($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $employee->delete();
        return $this->deletedResponse('Employee deleted successfully');
    }
}
