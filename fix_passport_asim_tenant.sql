-- SQL script to fix Passport OAuth clients for tenant 'asim'
-- Tenant ID: d815f98d-82df-4e2f-95a6-0a66b30d0c62
-- Database: tenantd815f98d-82df-4e2f-95a6-0a66b30d0c62 (or similar based on your tenant DB naming)

-- First, connect to the tenant database
-- USE tenantd815f98d-82df-4e2f-95a6-0a66b30d0c62;

-- Check if personal access client already exists
SELECT * FROM oauth_clients WHERE personal_access_client = 1;

-- Check if password client already exists  
SELECT * FROM oauth_clients WHERE password_client = 1;

-- If no personal access client exists, create one
INSERT INTO oauth_clients (
    user_id, 
    name, 
    secret, 
    provider, 
    redirect, 
    personal_access_client, 
    password_client, 
    revoked, 
    created_at, 
    updated_at
) VALUES (
    NULL,
    'Laravel Personal Access Client',
    'your-generated-secret-key-here',
    'users',
    'http://localhost',
    1,
    0,
    0,
    NOW(),
    NOW()
);

-- Get the ID of the personal access client we just created (replace X with actual ID)
-- INSERT INTO oauth_personal_access_clients (client_id, created_at, updated_at) 
-- VALUES (X, NOW(), NOW());

-- If no password client exists, create one
INSERT INTO oauth_clients (
    user_id, 
    name, 
    secret, 
    provider, 
    redirect, 
    personal_access_client, 
    password_client, 
    revoked, 
    created_at, 
    updated_at
) VALUES (
    NULL,
    'Laravel Password Grant Client',
    'your-generated-secret-key-here-2',
    'users',
    'http://localhost',
    0,
    1,
    0,
    NOW(),
    NOW()
);

-- Verify the clients were created
SELECT id, name, personal_access_client, password_client, provider FROM oauth_clients;
SELECT * FROM oauth_personal_access_clients;