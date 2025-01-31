<?php

namespace App\Services\Admin;

use App\Models\Employee;
use Illuminate\Support\Facades\Validator;

class EmployeeService
{
    public function addEmployee($data)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date|before_or_equal:today',
            'position' => 'required|max:255',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return ['status' => 'error', 'errors' => $validator->errors()];
        }

        Employee::create($data);
        return ['status' => 'success', 'message' => 'Employee created successfully'];
    }

    public function showEmployees()
    {
        $employees = Employee::all();
        return ['status' => 'success', 'data' => $employees, 'message' => 'Employees retrieved successfully'];
    }

    public function updateEmployee($employeeId, $data)
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return ['status' => 'error', 'message' => 'Employee not found'];
        }

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'salary' => 'sometimes|required|numeric|min:0',
            'hire_date' => 'sometimes|required|date|before_or_equal:today',
            'position' => 'sometimes|required|string|max:255'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return ['status' => 'error', 'errors' => $validator->errors()];
        }

        $employee->update($data);
        return ['status' => 'success', 'message' => 'Employee updated successfully'];
    }

    public function deleteEmployee($employeeId)
    {
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return ['status' => 'error', 'message' => 'Employee not found'];
        }

        $employee->delete();
        return ['status' => 'success', 'message' => 'Employee deleted successfully'];
    }
}
