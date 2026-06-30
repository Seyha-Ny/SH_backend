<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $query = Order::with('user')->latest();

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', (int) $search)
                  ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if ($from = request('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = request('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(20)->appends(request()->query()),
        ]);
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', [
            'order' => $order->load('items.product', 'user'),
        ]);
    }

    public function exportCsv()
    {
        $query = Order::with('user')->latest();

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', (int) $search)
                  ->orWhereHas('user', fn ($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if ($from = request('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = request('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query->get();

        $filename = 'orders_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $columns = ['Order ID', 'Customer', 'Date', 'Total', 'Status'];

        return Response::streamDownload(function () use ($orders, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->id,
                    $order->user?->name ?? 'N/A',
                    $order->created_at?->format('Y-m-d H:i'),
                    $order->total,
                    $order->status,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,completed,cancelled'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
        ]);

        $order->status = $validated['status'];
        $order->tracking_number = $validated['tracking_number'] ?? $order->tracking_number;
        $order->save();

        if ($order->user && filter_var($order->user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($order->user->email)->send(new OrderStatusUpdated($order));
            } catch (\Throwable $e) {
                // ignore mail failures
            }
        }

        $this->logActivity('updated order #' . $order->id . ' to: ' . $order->status, $order, ['status' => $order->status, 'tracking_number' => $order->tracking_number]);

        return back()->with('status', 'Order status updated.');
    }

    public function sendMail(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

        $recipient = $order->user?->email;

        if (! $recipient || ! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Customer email is missing or invalid.');
        }

        try {
            Mail::send('emails.admin.order-message', [
                'order' => $order,
                'customSubject' => $validated['subject'],
                'customMessage' => $validated['message'],
            ], function ($message) use ($recipient, $validated) {
                $message->to($recipient)->subject($validated['subject']);
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }

        $this->logActivity('sent email to order #' . $order->id, $order, ['subject' => $validated['subject']]);

        return back()->with('status', 'Email sent to customer.');
    }

    public function processRequest(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:approve,reject'],
        ]);

        if (! in_array($order->status, ['cancellation_requested', 'return_requested'], true)) {
            return back()->with('error', 'This request cannot be processed.');
        }

        if ($order->status === 'cancellation_requested') {
            $order->status = $validated['action'] === 'approve' ? 'cancelled' : 'pending';
        }

        if ($order->status === 'return_requested') {
            $order->status = $validated['action'] === 'approve' ? 'completed' : 'completed';
        }

        $order->save();

        if ($order->user && filter_var($order->user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($order->user->email)->send(new OrderStatusUpdated($order));
            } catch (\Throwable $e) {
                // ignore mail failures
            }
        }

        $this->logActivity('processed action ' . $validated['action'] . ' for order #' . $order->id, $order, ['status' => $order->status]);

        return back()->with('status', 'Request processed successfully.');
    }
}
