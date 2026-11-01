<div>
    @if($products->isEmpty())
        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
            <x-filament::icon
                alias="panels::empty-state"
                icon="heroicon-o-shopping-bag"
                class="h-12 w-12 mx-auto mb-3 text-gray-400 dark:text-gray-500"
            />
            <p class="text-sm font-medium">No products found in this collection.</p>
        </div>
    @else
        <!-- Grid Container -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto p-1">
            @foreach($products as $product)
                <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-4 shadow-sm flex flex-col justify-between transition hover:border-gray-300 dark:hover:border-white/20">
                    
                    <div>
                        <!-- Product Image Container (Relative for absolute badge) -->
                        <div class="relative w-full h-36 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden mb-3 border border-gray-100 dark:border-white/5 flex items-center justify-center">
                            @if($product->image)
                                <img 
                                    src="{{ Storage::url($product->image) }}" 
                                    alt="{{ $product->name }}" 
                                    class="w-full h-full object-cover"
                                />
                            @else
                                <!-- Fallback Placeholder Icon -->
                                <div class="flex flex-col items-center text-gray-400 dark:text-gray-600">
                                    <x-filament::icon icon="heroicon-o-photo" class="w-8 h-8 mb-1" />
                                    <span class="text-xs">No image</span>
                                </div>
                            @endif

                            <!-- Floating Status Badge on Image Corner -->
                            @if($product->product_status)
                                <div class="absolute top-2 right-2 shadow-md">
                                    <x-filament::badge :color="$product->product_status->getColor()" size="sm">
                                        {{ $product->product_status->getLabel() }}
                                    </x-filament::badge>
                                </div>
                            @endif
                        </div>

                        <!-- Product Name & Category Badge -->
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <a href="{{ \App\Filament\Resources\Catalog\Products\ProductResource::getUrl('edit', ['record' => $product]) }}" 
                               target="_blank"
                               class="font-semibold text-base text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1 group">
                                {{ $product->name }}
                                <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="w-4 h-4 opacity-70 group-hover:opacity-100" />
                            </a>
                            
                            <!-- Category Badge -->
                            @if($product->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 shrink-0">
                                    {{ $product->category->name }}
                                </span>
                            @endif
                        </div>

                        <!-- Description -->
                        <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-4">
                            {{ $product->description ?? 'No description provided.' }}
                        </p>
                    </div>

                    <!-- Footer: Price & Variants Count -->
                    <div class="pt-3 border-t border-gray-100 dark:border-white/5 text-sm space-y-2">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1 font-medium text-gray-700 dark:text-gray-300">
                                <x-filament::icon icon="heroicon-m-squares-2x2" class="w-4 h-4" />
                                <span>Variants Pricing</span>
                            </span>
                            <span>{{ $product->productVariants->count() }} Total</span>
                        </div>

                        <!-- Vertical Variant Rows (Scrollable if too many variants) -->
                        <div class="space-y-1.5 max-h-28 overflow-y-auto pr-1">
                            @forelse($product->productVariants as $variant)
                                <div class="flex items-center justify-between text-xs bg-gray-50 dark:bg-gray-800/50 px-2 py-1.5 rounded-md border border-gray-100 dark:border-white/5">
                                    <span class="text-gray-600 dark:text-gray-300 truncate max-w-[150px]">
                                        {{ $variant->name ?? $variant->sku ?? 'Variant #' . $loop->iteration }}
                                    </span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        ${{ number_format($variant->price ?? 0, 2) }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-xs text-gray-400 dark:text-gray-500 italic py-1 text-center">
                                    No variants found
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</div>