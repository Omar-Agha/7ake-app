<?php

namespace App\Core\Currents;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CurrentCustomer
{
    protected ?Customer $customer = null;

    /**
     * Get the currently authenticated customer.
     */
    public function get(): ?Customer
    {
        if ($this->customer) {
            return $this->customer;
        }

        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $this->set($user);

        return $this->customer;
    }

    /**
     * Set the current customer manually (for testing or override cases).
     */
    public function set(User $user): void
    {
        $this->customer = Customer::where("user_id", $user->id)->first();
   
    }

    /**
     * Check if there is a current customer.
     */
    public function exists(): bool
    {
        return $this->get() !== null;
    }
}
