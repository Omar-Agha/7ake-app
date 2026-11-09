<?php

namespace App\Services;

use App\Http\Requests\CustomerAddressSaveRequest;
use App\Http\Requests\CustomerSaveRequest;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerAddress;
use Auth;
use Illuminate\Http\UploadedFile;

class CustomerService
{

    public function create(string $f_name, string $l_name, ?string $phone, string $email, int $user_id, ?UploadedFile $avatar = null): \App\Models\Customer\Customer
    {
        $data = [
            'f_name' => $f_name,
            'l_name' => $l_name,
            'phone' => $phone,
            'email' => $email,
            'avatar' => $avatar,
            'user_id' => $user_id,
        ];




        // If avatar is uploaded, handle file upload (if required by your business logic)
        if ($avatar) {
            $uploader = app(\App\Core\FileUploader::class);
            $data['avatar'] = $uploader->uploadFile($avatar, 'avatars');
        }

        return \App\Models\Customer\Customer::create($data);
    }

    /**
     * Update the given customer.
     *
     * @param \App\Http\Requests\CustomerSaveRequest $request
     * @param \App\Models\Customer\Customer $customer
     * @return \App\Models\Customer\Customer
     */
    public function update(CustomerSaveRequest $request, Customer $customer): \App\Models\Customer\Customer
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
     * @param \App\Models\Customer\Customer $customer
     * @param \App\Http\Requests\CustomerAddressSaveRequest $request
     * @return \App\Models\Customer\CustomerAddress
     */
    public function attachAddress(Customer $customer, CustomerAddressSaveRequest $request): CustomerAddress
    {
        $data = $request->validated();
        return $customer->addresses()->create($data);
    }

    /**
     * Update the given address for a customer.
     *
     * @param \App\Models\Customer\CustomerAddress $address
     * @param \App\Http\Requests\CustomerAddressSaveRequest $request
     * @return \App\Models\Customer\CustomerAddress
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
     * @param \App\Models\Customer\CustomerAddress $address
     * @return bool|null
     */
    public function deleteAddress(CustomerAddress $address): ?bool
    {
        return $address->delete();
    }
}
