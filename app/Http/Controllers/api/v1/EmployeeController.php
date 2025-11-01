<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Employee::query();

            // Add search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('personal_email', 'like', "%{$search}%")
                      ->orWhere('employee_no', 'like', "%{$search}%");
                });
            }

            // Add filtering
            if ($request->has('gender')) {
                $query->where('gender', $request->gender);
            }

            if ($request->has('department')) {
                // Assuming we'll add department later, placeholder
                // $query->where('department', $request->department);
            }

            $employees = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'data' => $employees,
                'message' => 'Employees retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Employee index error', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'An error occurred while retrieving employees.'
            ], 500);
        }
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $employee = Employee::create($validated);

            Log::info('Employee created', [
                'employee_id' => $employee->id,
                'created_by' => $request->user()->id,
            ]);

            return response()->json([
                'data' => $employee,
                'message' => 'Employee created successfully.'
            ], 201);

        } catch (\Exception $e) {
            Log::error('Employee creation error', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'An error occurred while creating the employee.'
            ], 500);
        }
    }

    /**
     * Display the specified employee.
     */
    public function show(Request $request, Employee $employee): JsonResponse
    {
        try {
            return response()->json([
                'data' => $employee,
                'message' => 'Employee retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Employee show error', [
                'error' => $e->getMessage(),
                'employee_id' => $employee->id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'An error occurred while retrieving the employee.'
            ], 500);
        }
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        try {
            $validated = $request->validated();

            $employee->update($validated);

            Log::info('Employee updated', [
                'employee_id' => $employee->id,
                'updated_by' => $request->user()->id,
            ]);

            return response()->json([
                'data' => $employee,
                'message' => 'Employee updated successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Employee update error', [
                'error' => $e->getMessage(),
                'employee_id' => $employee->id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'An error occurred while updating the employee.'
            ], 500);
        }
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(Request $request, Employee $employee): JsonResponse
    {
        try {
            // Check if employee has associated user
            if ($employee->user) {
                return response()->json([
                    'message' => 'Cannot delete employee with associated user account.'
                ], 422);
            }

            $employee->delete();

            Log::info('Employee deleted', [
                'employee_id' => $employee->id,
                'deleted_by' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Employee deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Employee deletion error', [
                'error' => $e->getMessage(),
                'employee_id' => $employee->id,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'An error occurred while deleting the employee.'
            ], 500);
        }
    }
}
