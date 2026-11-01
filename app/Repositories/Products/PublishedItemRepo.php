<?php

namespace App\Repositories\Products;

use App\Enums\PublishStatus;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductCollection;
use Illuminate\Database\Eloquent\Builder;

class PublishedItemRepo
{
    public function getPublishedProducts():Builder{
        return Product::whereProductStatus(PublishStatus::Published);
    }

    public function getPublishedCollections():Builder{
        return ProductCollection::whereStatus(PublishStatus::Published);
    }
}