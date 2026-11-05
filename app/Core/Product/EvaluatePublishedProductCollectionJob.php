<?php

namespace App\Core\Product;

use App\Enums\PublishStatus;
use App\Models\Catalog\ProductCollection;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Mark product collections as published when their publication time is reached.
 */
class EvaluatePublishedProductCollectionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(): void
    {
        ProductCollection::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereStatus(PublishStatus::Draft->value)
            ->chunkById(100, function ($collections): void {
                foreach ($collections as $collection) {
                    $collection->status = PublishStatus::Published;
                    $collection->save();
                }
            });
    }
}
