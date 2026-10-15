<?php

namespace App\Services;

use App\Http\Requests\CustomerAddressSaveRequest;
use App\Http\Requests\CustomerSaveRequest;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Auth;

class CustomerService
{
    /**
     * Create a new customer.
     *
     * @param \App\Http\Requests\CustomerSaveRequest $request
     * @return \App\Models\Customer
     */
    public function create(CustomerSaveRequest $request): \App\Models\Customer
    {
        $data = $request->validated();

        
        $data['user_id'] = Auth::user()->id;

        // If avatar is uploaded, handle file upload (if required by your business logic)
        if ($request->hasFile('avatar')) {
            $uploader = app(\App\Core\FileUploader::class);
            $data['avatar'] = $uploader->uploadFile($request->file('avatar'), 'avatars');
        }

        return \App\Models\Customer::create($data);
    }

    /**
     * Update the given customer.
     *
     * @param \App\Http\Requests\CustomerSaveRequest $request
     * @param \App\Models\Customer $customer
     * @return \App\Models\Customer
     */
    public function update(CustomerSaveRequest $request, Customer $customer): \App\Models\Customer
    {
        $data = $request->validated();

        // If avatar is uploaded, handle file upload
        if ($request->hasFile('avatar')) {
            $uploader = app(\App\Core\FileUploader::class);
            $data['avatar'] = $uploader->uploadFile($request->file('avatar'), 'avatars');
        }

        $customer->update($data);

        return $customer;
    }

    /**
     * Attach a new address to the given customer.
     *
     * @param \App\Models\Customer $customer
     * @param \App\Http\Requests\CustomerAddressSaveRequest $request
     * @return \App\Models\CustomerAddress
     */
    public function attachAddress(Customer $customer, CustomerAddressSaveRequest $request): CustomerAddress
    {
        $data = $request->validated();
        return $customer->addresses()->create($data);
    }

    /**
     * Update the given address for a customer.
     *
     * @param \App\Models\CustomerAddress $address
     * @param \App\Http\Requests\CustomerAddressSaveRequest $request
     * @return \App\Models\CustomerAddress
     */
    public function updateAddress(CustomerAddress $address, CustomerAddressSaveRequest $request): CustomerAddress
    {
        $data = $request->validated();
        $address->update($data);
        return $address;
    }

    /**
     * Delete the given address for a customer.
     *
     * @param \App\Models\CustomerAddress $address
     * @return bool|null
     */
    public function deleteAddress(CustomerAddress $address): ?bool
    {
        return $address->delete();
    }
}