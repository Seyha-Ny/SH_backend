@extends('admin.layouts.app')

@section('title', 'Reviews')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="h3 mb-0">Reviews</h1>
            <div class="page-subtitle">Moderate customer reviews</div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Review</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th class="text-center">Rating</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td class="fw-medium">
                            {{ \Illuminate\Support\Str::limit($review->comment ?: 'No comment', 60) }}
                            <div class="text-muted small">{{ $review->created_at?->diffForHumans() }}</div>
                        </td>
                        <td>{{ $review->product?->name ?? 'N/A' }}</td>
                        <td>{{ $review->user?->name ?? 'N/A' }}</td>
                        <td class="text-center">{{ $review->rating }}/5</td>
                        <td class="text-center">
                            @php
                                $statusClass = $review->approved ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning';
                            @endphp
                            <span class="badge badge-status {{ $statusClass }}">{{ $review->approved ? 'Approved' : 'Pending' }}</span>
                        </td>
                        <td class="text-end">
                            <form action="{{ route('admin.reviews.update', $review) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="approved" value="{{ $review->approved ? 0 : 1 }}">
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    {{ $review->approved ? 'Unapprove' : 'Approve' }}
                                </button>
                            </form>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-1" onclick="confirmDestroy() && document.getElementById('delete-review-{{ $review->id }}').submit()">Delete</button>
                            <form id="delete-review-{{ $review->id }}" action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No reviews found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($reviews, 'links'))
            <div class="p-3 border-top">{{ $reviews->links() }}</div>
        @endif
    </div>
@endsection
