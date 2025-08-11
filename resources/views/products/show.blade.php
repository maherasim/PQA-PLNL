<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Multi-Tenant App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Product Details</h1>
            <a href="{{ route('products.index') }}?tenant={{ request()->query('tenant') }}" class="btn btn-secondary">Back to Products</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Product Information</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th>ID:</th>
                                <td>{{ $product->id }}</td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td>{{ $product->name }}</td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td>{{ $product->description ?: 'No description' }}</td>
                            </tr>
                            <tr>
                                <th>Price:</th>
                                <td>${{ number_format($product->price, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Stock:</th>
                                <td>{{ $product->stock }}</td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td>{{ $product->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('products.edit', $product) }}?tenant={{ request()->query('tenant') }}" class="btn btn-warning">Edit Product</a>
                    <form action="{{ url('products/' . $product->id . '?tenant=' . request()->query('tenant')) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
