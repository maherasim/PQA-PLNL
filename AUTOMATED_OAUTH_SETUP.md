# 🚀 Automated OAuth Client Creation for Multi-Tenant Laravel Passport

## 🎯 Problem Solved

**Before:** You had to manually run `php install_passport_for_tenant.php` every time you created a new tenant.

**After:** OAuth clients are automatically created when a new tenant is created! 🎉

## 🔧 How It Works

### 1. **Automatic Trigger**
When a new tenant is created, the `Tenant` model automatically dispatches a job:

```php
// In app/Models/Tenant.php
static::created(function (Tenant $tenant) {
    CreateTenantOAuthClients::dispatch($tenant)->delay(now()->addSeconds(5));
});
```

### 2. **Background Job Processing**
The `CreateTenantOAuthClients` job:
- Waits for the tenant database to be fully ready
- Creates Personal Access Client (required for API auth)
- Creates Password Grant Client (required for login)
- Handles errors gracefully without breaking tenant creation

### 3. **What Gets Created**
Each tenant automatically gets:
- ✅ Personal Access Client (`personal_access_client = true`)
- ✅ Password Grant Client (`password_client = true`)
- ✅ Proper provider mapping (`provider = 'users'`)

## 🚀 Usage - It's Automatic!

### **Creating a New Tenant**
```php
// Just create a tenant normally - OAuth clients are created automatically!
$tenant = Tenant::create([
    'domain' => 'newcompany.127.0.0.1.nip.io',
    'organization_id' => $orgId,
    'status' => 'active',
    // ... other fields
]);

// OAuth clients are created in the background automatically!
// No manual script running needed!
```

### **New Tenant Workflow**
1. **Create tenant** → Database created automatically
2. **Run migrations** → Tables created (including `oauth_clients`)
3. **OAuth clients created** → Automatically in background (5-second delay)
4. **Register users** → Login works immediately!

## 📁 Files Created/Modified

### **New Files:**
- `app/Jobs/CreateTenantOAuthClients.php` - The job that creates OAuth clients
- `AUTOMATED_OAUTH_SETUP.md` - This documentation

### **Modified Files:**
- `app/Models/Tenant.php` - Added automatic job dispatch

### **Legacy Files (Still Useful):**
- `install_passport_for_tenant.php` - Manual script (for emergencies)
- `fix_passport_asim_tenant.sql` - SQL backup solution
- `install_passport_commands.md` - Alternative solutions

## 🔄 Queue Configuration

### **Important:** Make sure your queue is running!
```bash
# Start the queue worker
php artisan queue:work

# Or use supervisor for production
# The job will fail if queues aren't running
```

### **Queue Driver Options:**
```env
# .env file
QUEUE_CONNECTION=database  # Uses database for queues
# or
QUEUE_CONNECTION=redis     # Uses Redis for queues
# or
QUEUE_CONNECTION=sync      # Runs immediately (not recommended for production)
```

## 🧪 Testing the New System

### **Test 1: Create a New Tenant**
```bash
# Create a new tenant via your admin panel or API
# The OAuth clients should be created automatically

# Check the logs
tail -f storage/logs/laravel.log
# Look for: "Successfully created OAuth clients for tenant: {tenant_id}"
```

### **Test 2: Login Immediately**
```bash
# Try to login with the new tenant immediately
curl -X POST http://newtenant.127.0.0.1.nip.io:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'

# Should work without any manual OAuth setup!
```

## 🚨 Troubleshooting

### **OAuth Clients Not Created?**
1. **Check queue worker:**
   ```bash
   php artisan queue:work --verbose
   ```

2. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Manual fallback:**
   ```bash
   # Still works as backup
   php install_passport_for_tenant.php
   ```

### **Common Issues:**
- **Queue not running** → Start `php artisan queue:work`
- **Database not ready** → Job waits up to 10 seconds
- **Migration not run** → Run tenant migrations first

## 🔒 Security Notes

- OAuth client secrets are randomly generated for each tenant
- Each tenant has isolated OAuth clients
- No cross-tenant access possible
- Secrets are stored securely in the database

## 📈 Benefits

✅ **Zero Manual Work** - OAuth clients created automatically  
✅ **Scalable** - Works for 1 or 1000 tenants  
✅ **Reliable** - Background job with retry logic  
✅ **Maintainable** - Clean, testable code  
✅ **Production Ready** - Proper error handling and logging  

## 🎉 Result

**Now when you:**
1. Clone the project anywhere
2. Create a new tenant
3. Register users
4. Try to login

**Everything works automatically!** No more manual OAuth client creation needed.

---

*This system ensures your multi-tenant Laravel Passport setup is truly production-ready and scalable.* 🚀