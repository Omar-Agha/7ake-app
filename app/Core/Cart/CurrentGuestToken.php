<?php

namespace App\Core\Cart;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CurrentGuestToken
{
    protected ?string $token = null;

    private const SESSION_KEY = 'guest_token';

    public function __construct(protected Request $request) {}

    public function get(): ?string
    {
        if ($this->token) {
            return $this->token;
        }

        $this->set();

        return $this->token;
    }

    /**
     * Set the current customer manually (for testing or override cases).
     */
    public function set(): void
    {
        $session = $this->request->session();

        if ($session->has(self::SESSION_KEY)) {
            $this->token = $session->get(self::SESSION_KEY);

            return;
        }

        $this->token = Str::random(40);
        $session->put(self::SESSION_KEY, $this->token);
    }

    /**
     * Check if there is a current customer.
     */
    public function exists(): bool
    {
        return $this->get() !== null;
    }
}
