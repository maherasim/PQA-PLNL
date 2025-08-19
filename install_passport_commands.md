# Commands to Fix Passport for Tenant 'asim'

## Tenant Information
- **Tenant ID**: `d815f98d-82df-4e2f-95a6-0a66b30d0c62`
- **Domain**: `asim.127.0.0.1.nip.io`
- **Tenant Database**: Likely `tenantd815f98d-82df-4e2f-95a6-0a66b30d0c62` (based on your tenancy config)

## Solution 1: Using Artisan Commands (Recommended)

If you have access to run artisan commands:

```bash
# Option A: Use the tenant:artisan command
php artisan tenant:artisan --tenant=d815f98d-82df-4e2f-95a6-0a66b30d0c62 passport:install --force

# Option B: Use the existing controller route
# Make a POST request to: http://127.0.0.1:8000/tenants/d815f98d-82df-4e2f-95a6-0a66b30d0c62/passport-install
curl -X POST "http://127.0.0.1:8000/tenants/d815f98d-82df-4e2f-95a6-0a66b30d0c62/passport-install"
```

## Solution 2: Using the PHP Script

If you can run PHP:

```bash
# First install dependencies (if not already done)
composer install

# Run the script
php install_passport_for_tenant.php
```

## Solution 3: Direct Database Access

If you have MySQL/database access, connect to the tenant database and run the SQL script:

```bash
# Connect to your tenant database
mysql -u your_username -p tenantd815f98d-82df-4e2f-95a6-0a66b30d0c62

# Then run the SQL commands from fix_passport_asim_tenant.sql
```

## Solution 4: Quick Fix via Tinker

```bash
php artisan tinker

# In tinker, run:
$tenant = App\Models\Tenant::find('d815f98d-82df-4e2f-95a6-0a66b30d0c62');
tenancy()->initialize($tenant);
Artisan::call('passport:install', ['--force' => true]);
tenancy()->end();
```

## Verification

After running any of the above solutions, test your login:

```bash
curl -X POST http://asim.127.0.0.1.nip.io:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "your-email@example.com", "password": "your-password"}'
```

The error should be resolved and you should get a successful authentication response.