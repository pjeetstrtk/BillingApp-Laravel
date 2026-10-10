<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $op = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $products = Product::query()
            ->when($search !== '', fn ($query) => $query->where("name", $op, "%{$search}%"))
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products', 'search'));
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Product::create($this->validated($request));

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request));

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    /** Delete confirmation page. */
    public function delete(Product $product): View
    {
        return view('products.delete', compact('product'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->billingItems()->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'This product appears on invoices and cannot be deleted.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0', 'max:9999999999999999.99'],
            'stock'       => ['required', 'integer', 'min:0'],
        ]);
    }
}
