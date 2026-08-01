<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShippingMethodController extends Controller
{
    public function index(): View
    {
        $methods = ShippingMethod::orderByDesc('active')->orderByDesc('created_at')->paginate(20);

        return view('admin.shipping-methods.index', [
            'methods' => $methods,
        ]);
    }

    public function create(): View
    {
        return view('admin.shipping-methods.form', [
            'method' => new ShippingMethod(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'courier' => ['nullable', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:50', 'unique:courier_shipping_methods,code'],
            'fee' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['boolean'],
            'estimated_days_min' => ['nullable', 'integer', 'min:1'],
            'estimated_days_max' => ['nullable', 'integer', 'min:1', 'gte:estimated_days_min'],
        ]);

        ShippingMethod::create($validated);

        return redirect()->route('admin.shipping-methods.index')->with('status', 'Shipping method created successfully.');
    }

    public function edit(ShippingMethod $method): View
    {
        return view('admin.shipping-methods.form', [
            'method' => $method,
        ]);
    }

    public function update(Request $request, ShippingMethod $method): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'courier' => ['nullable', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:50', 'unique:courier_shipping_methods,code,' . $method->id],
            'fee' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['boolean'],
            'estimated_days_min' => ['nullable', 'integer', 'min:1'],
            'estimated_days_max' => ['nullable', 'integer', 'min:1', 'gte:estimated_days_min'],
        ]);

        $method->update($validated);

        return redirect()->route('admin.shipping-methods.index')->with('status', 'Shipping method updated successfully.');
    }

    public function destroy(Request $request, ShippingMethod $method): RedirectResponse
    {
        $request->validate([
            'confirm_code' => ['required', 'string', Rule::in([$method->code])],
        ]);

        $method->delete();

        return redirect()->route('admin.shipping-methods.index')->with('status', 'Shipping method deleted successfully.');
    }
}
