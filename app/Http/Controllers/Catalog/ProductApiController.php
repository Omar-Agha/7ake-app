<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Category;
use App\Models\Catalog\Product;
use App\Models\Catalog\ProductCollection;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('productCollections')->paginate(10);
        return response()->json($products);
    }
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->with('productCollections')->firstOrFail();
        return response()->json($product);
    }
    public function getCollections()
    {
        // $collections = Product::with('productCollections')->get()->pluck('productCollections')->flatten()->unique('id');
        $collections = Product::with('productCollections')->get()->pluck('productCollections')->flatten()->unique('id');
        return response()->json($collections);
    }
    public function getUpcomingCollections()
    {
        $collections = ProductCollection::wherePublishedAt('>', now())->get();
        return response()->json($collections);
    }
    public function getCollectionBySlug(string $slug)
    {
        $collection = ProductCollection::whereSlug($slug)->get();
        return response()->json($collection);
    }
    public function getCategories()
    {
        // $categories = Product::with('productCategories')->get()->pluck('productCategories')->flatten()->unique('id');
        $categories = Category::all();
        return response()->json($categories);
    }
}
