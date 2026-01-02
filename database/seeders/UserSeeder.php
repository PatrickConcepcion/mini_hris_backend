<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User with Employee
        $adminEmployee = Employee::updateOrCreate(
            ['personal_email' => 'admin@example.com'],
            [
                'first_name' => 'John',
                'middle_name' => 'Admin',
                'last_name' => 'Doe',
                'employee_no' => 'ADM001',
                'date_of_birth' => '1985-01-15',
                'date_hired' => '2020-01-01',
                'phone_number' => '+1-555-0101',
                'gender' => 'male',
            ]
        );

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'password' => Hash::make('password'),
                'employee_id' => $adminEmployee->id,
                'first_time_login' => false,
            ]
        );

        $adminUser->assignRole('admin');

        // Create Manager User with Employee
        $managerEmployee = Employee::updateOrCreate(
            ['personal_email' => 'manager@example.com'],
            [
                'first_name' => 'Jane',
                'middle_name' => 'Manager',
                'last_name' => 'Smith',
                'employee_no' => 'MGR001',
                'date_of_birth' => '1988-03-22',
                'date_hired' => '2021-06-15',
                'phone_number' => '+1-555-0102',
                'gender' => 'female',
            ]
        );

        $managerUser = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'password' => Hash::make('password'),
                'employee_id' => $managerEmployee->id,
                'first_time_login' => false,
            ]
        );

        $managerUser->assignRole('manager');

        // Create Employee User with Employee
        $employeeEmployee = Employee::updateOrCreate(
            ['personal_email' => 'employee@example.com'],
            [
                'first_name' => 'Bob',
                'middle_name' => 'Regular',
                'last_name' => 'Johnson',
                'employee_no' => 'EMP001',
                'date_of_birth' => '1992-07-10',
                'date_hired' => '2022-09-01',
                'phone_number' => '+1-555-0103',
                'gender' => 'male',
            ]
        );

        $employeeUser = User::updateOrCreate(
            ['email' => 'employee@example.com'],
            [
                'password' => Hash::make('password'),
                'employee_id' => $employeeEmployee->id,
                'first_time_login' => true, // First time login for employee
            ]
        );

        $employeeUser->assignRole('employee');
    }
}
