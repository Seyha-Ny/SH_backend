<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::with('category')->latest()->paginate(20),
        ]);
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $products = Product::with('category')->latest()->get();

        $filename = 'products_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $columns = ['ID', 'Name', 'SKU', 'Category', 'Price', 'Stock', 'Status', 'Created At'];

        return Response::streamDownload(function () use ($products, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->id,
                    $product->name,
                    $product->sku ?? '',
                    $product->category?->name ?? '',
                    $product->price,
                    $product->stock,
                    $product->status ?? 'inactive',
                    $product->created_at?->format('Y-m-d H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'categories' => Category::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:4096'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:4096'],
        ]);

        $validated['slug'] = $validated['slug'] ?? '';
        $validated['slug'] = $validated['slug'] ?: Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        Cache::flush();

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->productImages()->create([
                    'path' => $image->store('products', 'public'),
                ]);
            }
        }

        $this->logActivity('created product: ' . $product->name, $product, ['name' => $product->name]);

        return redirect('/admin/products')->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::all(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . $product->id],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'category_id' => ['required', 'exists:categories,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:4096'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:4096'],
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $product->productImages()->create([
                    'path' => $image->store('products', 'public'),
                ]);
            }
        }

        $product->update($validated);

        Cache::flush();

        $this->logActivity('updated product: ' . $product->name, $product, ['changes' => $validated]);

        return redirect('/admin/products')->with('status', 'Product updated successfully.');
    }

    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'stock_delta' => ['required', 'integer'],
        ]);

        $delta = (int) $validated['stock_delta'];
        $newStock = max(0, (int) $product->stock + $delta);

        $product->update([
            'stock' => $newStock,
        ]);

        $this->logActivity('adjusted stock for product: ' . $product->name, $product, [
            'delta' => $delta,
            'new_stock' => $newStock,
        ]);

        return back()->with('status', 'Stock adjusted successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        foreach ($product->productImages as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        Cache::flush();

        $this->logActivity('deleted product: ' . $product->name, $product, ['name' => $product->name, 'id' => $product->getKey()]);

        return redirect('/admin/products')->with('status', 'Product deleted successfully.');
    }
}
