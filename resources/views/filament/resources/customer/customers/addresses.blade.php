{{-- filepath:
/Volumes/omar-ssd/storage/projects/hackerank/7ake/7ake-server/resources/views/filament/resources/customer/customers/addresses.blade.php
--}}
@forelse ($addresses as $address)
    <div class="mb-4">
        @foreach ($address->getAttributes() as $key => $value)
            @if (!in_array($key, ['id', 'customer_id', 'created_at', 'updated_at']))
                <p><strong>{{ str($key)->replace('_', ' ')->title() }}:</strong> {{ $value }}</p>
            @endif
        @endforeach
    </div>
@empty
    <p>No addresses found.</p>
@endforelse