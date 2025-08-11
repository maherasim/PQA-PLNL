<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        // Tenancy is initialized by middleware (stancl/tenancy). No manual DB switching needed.
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create'); // tenant middleware will switch connection based on ?tenant
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

                // Tenancy is initialized by middleware (stancl/tenancy). No manual DB switching needed.
        Product::create($request->all());

        // Fix the redirect to properly include tenant parameter
        $tenantParam = $request->query('tenant') ? '?tenant=' . $request->query('tenant') : '';
        return redirect('/products' . $tenantParam)->with('success', 'Product created successfully!');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

                // Tenancy is initialized by middleware (stancl/tenancy). No manual DB switching needed.
        $product->update($request->all());

        // Fix the redirect to properly include tenant parameter
        $tenantParam = $request->query('tenant') ? '?tenant=' . $request->query('tenant') : '';
        return redirect('/products' . $tenantParam)->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
                // Tenancy is initialized by middleware (stancl/tenancy). No manual DB switching needed.
        $product->delete();

        // Fix the redirect to properly include tenant parameter
        $tenantParam = request()->query('tenant') ? '?tenant=' . request()->query('tenant') : '';
        return redirect('/products' . $tenantParam)->with('success', 'Product deleted successfully!');
    }
}
