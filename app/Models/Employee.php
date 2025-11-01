<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'employees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'personal_email',
        'employee_no',
        'date_of_birth',
        'date_hired',
        'phone_number',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'gender',
        'nationality',
        'marital_status',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'height_cm',
        'weight_kg',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_hired' => 'date',
            'height_cm' => 'decimal:2',
            'weight_kg' => 'decimal:2',
        ];
    }

    /**
     * Get the user associated with the employee.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class);
    }
}
