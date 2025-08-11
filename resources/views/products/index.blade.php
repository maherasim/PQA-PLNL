<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Multi-Tenant App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Products</h1>
            <div>
                <a href="{{ route('tenants.index') }}" class="btn btn-secondary me-2">Back to Tenants</a>
                <a href="{{ url('products/create?tenant=' . request()->query('tenant')) }}" class="btn btn-primary">Create New Product</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(request()->query('success'))
            <div class="alert alert-success">
                {{ request()->query('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                <td>{{ $product->id }}</td>
                                <td>{{ $product->name }}</td>
                                <td>{{ Str::limit($product->description, 50) }}</td>
                                <td>${{ number_format($product->price, 2) }}</td>
                                <td>{{ $product->stock }}</td>
                                <td>
                                    <a href="{{ url('products/' . $product->id . '?tenant=' . request()->query('tenant')) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ url('products/' . $product->id . '/edit?tenant=' . request()->query('tenant')) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ url('products/' . $product->id . '?tenant=' . request()->query('tenant')) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
