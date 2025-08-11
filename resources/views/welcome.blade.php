<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Multi-Tenant Laravel App</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Styles -->
            <style>
            body {
                font-family: 'Figtree', sans-serif;
            }
            </style>
    </head>
    <body class="antialiased">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h1 class="text-center">Multi-Tenant Laravel Application</h1>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Tenant Management</h5>
                                            <p class="card-text">Manage your tenants and their databases.</p>
                                            <a href="{{ route('tenants.index') }}" class="btn btn-primary">Manage Tenants</a>
                                        </div>
                                    </div>
                                </div>
                               
                            </div>
                            
                            
                        </div>
                    </div>
                </div>
                </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
