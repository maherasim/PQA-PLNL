<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenants - Multi-Tenant App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Tenants</h1>
            <a href="{{ route('tenants.create') }}" class="btn btn-primary">Create New Tenant</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Domain</th>
                            <th>Database</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tenants as $tenant)
                            <tr>
                                <td>{{ $tenant->id }}</td>
                                <td>{{ $tenant->name }}</td>
                                <td>{{ optional($tenant->domains()->first())->domain }}</td>
                                <td>{{ $tenant->database }}</td>
                                <td>
                                    <span class="badge bg-{{ $tenant->is_active ? 'success' : 'danger' }}">
                                        {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('products.index') }}?tenant={{ $tenant->id }}" class="btn btn-sm btn-success">View Products</a>
                                    <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" class="d-inline">
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
