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
        // Ensure we're using the tenant connection
        if (request()->has('tenant')) {
            $tenant = Tenant::find(request()->query('tenant'));
            if ($tenant) {
                config(['database.connections.tenant.database' => $tenant->database]);
                DB::purge('tenant');
                config(['database.default' => 'tenant']);
                DB::purge();
            }
        }
        
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

        // Ensure we're using the tenant connection
        if ($request->has('tenant')) {
            $tenant = Tenant::find($request->query('tenant'));
            if ($tenant) {
                config(['database.connections.tenant.database' => $tenant->database]);
                DB::purge('tenant');
                config(['database.default' => 'tenant']);
                DB::purge();
            }
        }

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

        // Ensure we're using the tenant connection
        if ($request->has('tenant')) {
            $tenant = Tenant::find($request->query('tenant'));
            if ($tenant) {
                config(['database.connections.tenant.database' => $tenant->database]);
                DB::purge('tenant');
                config(['database.default' => 'tenant']);
                DB::purge();
            }
        }

        $product->update($request->all());

        // Fix the redirect to properly include tenant parameter
        $tenantParam = $request->query('tenant') ? '?tenant=' . $request->query('tenant') : '';
        return redirect('/products' . $tenantParam)->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        // Ensure we're using the tenant connection
        if (request()->has('tenant')) {
            $tenant = Tenant::find(request()->query('tenant'));
            if ($tenant) {
                config(['database.connections.tenant.database' => $tenant->database]);
                DB::purge('tenant');
                config(['database.default' => 'tenant']);
                DB::purge();
            }
        }

        $product->delete();

        // Fix the redirect to properly include tenant parameter
        $tenantParam = request()->query('tenant') ? '?tenant=' . request()->query('tenant') : '';
        return redirect('/products' . $tenantParam)->with('success', 'Product deleted successfully!');
    }
}
