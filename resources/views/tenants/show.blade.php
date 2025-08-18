<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Details - Multi-Tenant App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Tenant Details</h1>
            <a href="{{ route('tenants.index') }}" class="btn btn-secondary">Back to Tenants</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Tenant Information</h5>
                        <table class="table table-borderless">
                            <tr>
                                <th>ID:</th>
                                <td>{{ $tenant->id }}</td>
                            </tr>
                            <tr>
                                <th>Tenant ID:</th>
                                <td>{{ $tenant->id }}</td>
                            </tr>
                            <tr>
                                <th>Domain:</th>
                                <td>{{ $tenant->domain }}</td>
                            </tr>
                            <tr>
                                <th>Database:</th>
                                <td>{{ $tenant->db_name }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td><span class="badge bg-secondary">—</span></td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td>{{ $tenant->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="{{ route('tenants.edit', $tenant) }}" class="btn btn-warning">Edit Tenant</a>
                    <form action="{{ route('tenants.destroy', $tenant) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete Tenant</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
