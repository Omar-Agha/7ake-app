<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Add your fillable attributes here. Example:
        'customer_id',
        'address_name',
        'country',
        'city',
        'address_description',
        'is_default'
        
        
    ];
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    
    public function customer():BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}


// In Laravel, services responsible for handling "creating, updating, and other" business logic 
// are typically placed inside an app/Services directory (which you can create if it doesn't exist).
// For customer address operations (create, update, delete, etc.), you would create a service class, 
// such as app/Services/CustomerAddressService.php. 
// This keeps your controllers thin and your models focused on data relationships, 
// while the service encapsulates business logic and orchestration.
//
// Example service class path:
//   app/Services/CustomerAddressService.php
//
// Use this service within controllers or other parts of your application as needed.
//
// For example, a stub:
//
// namespace App\Services;
//
// use App\Models\CustomerAddress;
//
// class CustomerAddressService
// {
//     public function create(array $data): CustomerAddress
//     {
//         // Add validations, defaults, etc. here if needed
//         return CustomerAddress::create($data);
//     }
//
//     public function update(CustomerAddress $address, array $data): bool
//     {
//         return $address->update($data);
//     }
//
//     // ...other methods as needed
// }
//
// Summarized: put your business logic for address CRUD in a `Service` class under `app/Services`.