# Route Cache Fix for Content Requests API

## Issue
The route `api/v1/requests` is returning 404 error: "The route api/v1/requests could not be found."

## Solution

The routes are correctly defined in `routes/api.php` (lines 78-79), but Laravel's route cache needs to be cleared.

### Steps to Fix:

1. **SSH into your server** or access the Laravel project directory

2. **Clear the route cache:**
   ```bash
   php artisan route:clear
   ```

3. **Clear all caches (recommended):**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

4. **Verify the routes are registered:**
   ```bash
   php artisan route:list | grep requests
   ```
   
   You should see:
   ```
   POST   api/v1/requests ................ ContentRequestApiController@store
   GET    api/v1/requests ................ ContentRequestApiController@index
   ```

5. **If routes still don't appear, check:**
   - Ensure `ContentRequestApiController` exists at: `app/Http/Controllers/Api/ContentRequestApiController.php`
   - Verify the namespace is correct: `App\Http\Controllers\Api`
   - Check that the controller methods `store()` and `index()` exist

6. **For production, rebuild the cache (after clearing):**
   ```bash
   php artisan route:cache
   php artisan config:cache
   ```

## Verification

After clearing the cache, test the endpoint:
```bash
curl -X POST https://nazaarabox.com/api/v1/requests \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "type": "movie",
    "title": "Test Movie"
  }'
```

You should get a successful response instead of 404.

## Alternative: Check Route Registration

If clearing cache doesn't work, verify the routes are being loaded:

1. Check `bootstrap/app.php` - ensure `api.php` is registered:
   ```php
   ->withRouting(
       api: __DIR__.'/../routes/api.php',
   )
   ```

2. Check if there are any route service providers that might be interfering

3. Verify the `ContentRequestApiController` class exists and is properly namespaced

