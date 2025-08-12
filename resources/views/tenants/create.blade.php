<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Tenant - Multi-Tenant App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Create New Tenant</h1>
            <a href="{{ route('tenants.index') }}" class="btn btn-secondary">Back to Tenants</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('tenants.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Tenant Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="subdomain" class="form-label">Subdomain</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('subdomain') is-invalid @enderror" id="subdomain" name="subdomain" value="{{ old('subdomain') }}" placeholder="acme" required>
                            <span class="input-group-text">.{{ env('TENANCY_BASE_DOMAIN') }}</span>
                        </div>
                        @error('subdomain')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Create Tenant</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
