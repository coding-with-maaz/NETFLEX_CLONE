const http = require('http');

const BASE_URL = 'http://localhost:8080/api/v1';

// Test utilities
function makeRequest(path, callback) {
  const url = `${BASE_URL}${path}`;
  console.log(`\n[TEST] GET ${url}`);
  
  http.get(url, (res) => {
    let data = '';
    
    res.on('data', (chunk) => {
      data += chunk;
    });
    
    res.on('end', () => {
      try {
        const json = JSON.parse(data);
        console.log(`[RESPONSE] Status: ${res.statusCode}`);
        console.log(`[RESPONSE] Success: ${json.success}`);
        if (json.data) {
          if (Array.isArray(json.data)) {
            console.log(`[RESPONSE] Items: ${json.data.length}`);
          } else if (json.data.tvShows) {
            console.log(`[RESPONSE] TV Shows: ${json.data.tvShows.length}`);
            if (json.data.pagination) {
              console.log(`[RESPONSE] Pagination:`, json.data.pagination);
            }
          } else if (json.data.categories) {
            console.log(`[RESPONSE] Categories: ${json.data.categories.length}`);
            if (json.data.categories.length > 0) {
              console.log(`[RESPONSE] First category:`, json.data.categories[0]);
            }
          }
        }
        callback(null, json);
      } catch (e) {
        console.log(`[ERROR] Failed to parse JSON: ${e.message}`);
        console.log(`[RESPONSE] Raw: ${data.substring(0, 200)}...`);
        callback(e, null);
      }
    });
  }).on('error', (err) => {
    console.log(`[ERROR] Request failed: ${err.message}`);
    callback(err, null);
  });
}

// Test functions
async function testUtils() {
  return new Promise((resolve) => {
    console.log('\n=== Testing Utils Endpoint ===');
    makeRequest('/utils/all', (err, data) => {
      if (!err && data && data.data && data.data.categories) {
        console.log(`\n[SUCCESS] Found ${data.data.categories.length} categories`);
        if (data.data.categories.length > 0) {
          console.log('Sample categories:');
          data.data.categories.slice(0, 4).forEach((cat, i) => {
            console.log(`  ${i + 1}. ${cat.name} (slug: ${cat.slug || 'N/A'}, id: ${cat.id})`);
          });
        }
      }
      resolve(data);
    });
  });
}

async function testTVShowsList() {
  return new Promise((resolve) => {
    console.log('\n=== Testing TV Shows List (No Filter) ===');
    makeRequest('/tvshows?limit=5', (err, data) => {
      if (!err && data && data.data && data.data.tvShows) {
        console.log(`\n[SUCCESS] Found ${data.data.tvShows.length} TV shows`);
        if (data.data.tvShows.length > 0) {
          console.log('Sample TV show:', {
            id: data.data.tvShows[0].id,
            name: data.data.tvShows[0].name,
            category_id: data.data.tvShows[0].category_id
          });
        }
      }
      resolve(data);
    });
  });
}

async function testTVShowsWithCategory(categoryName) {
  return new Promise((resolve) => {
    console.log(`\n=== Testing TV Shows with Category Filter: "${categoryName}" ===`);
    const encoded = encodeURIComponent(categoryName);
    makeRequest(`/tvshows?category=${encoded}&limit=5`, (err, data) => {
      if (!err && data && data.data && data.data.tvShows) {
        console.log(`\n[SUCCESS] Found ${data.data.tvShows.length} TV shows for category "${categoryName}"`);
        if (data.data.tvShows.length > 0) {
          console.log('Sample TV shows:');
          data.data.tvShows.slice(0, 3).forEach((tv, i) => {
            console.log(`  ${i + 1}. ${tv.name} (category_id: ${tv.category_id})`);
          });
        } else {
          console.log(`[WARNING] No TV shows found for category "${categoryName}"`);
        }
      }
      resolve(data);
    });
  });
}

async function testTVShowsWithCategorySlug(categorySlug) {
  return new Promise((resolve) => {
    console.log(`\n=== Testing TV Shows with Category Slug: "${categorySlug}" ===`);
    const encoded = encodeURIComponent(categorySlug);
    makeRequest(`/tvshows?category=${encoded}&limit=5`, (err, data) => {
      if (!err && data && data.data && data.data.tvShows) {
        console.log(`\n[SUCCESS] Found ${data.data.tvShows.length} TV shows for category slug "${categorySlug}"`);
        if (data.data.tvShows.length > 0) {
          console.log('Sample TV shows:');
          data.data.tvShows.slice(0, 3).forEach((tv, i) => {
            console.log(`  ${i + 1}. ${tv.name} (category_id: ${tv.category_id})`);
          });
        } else {
          console.log(`[WARNING] No TV shows found for category slug "${categorySlug}"`);
        }
      }
      resolve(data);
    });
  });
}

// Main test function
async function runTests() {
  console.log('========================================');
  console.log('Testing Nazaara Box Backend APIs');
  console.log('========================================');
  
  // Test 1: Get utilities (categories)
  const utilsData = await testUtils();
  
  // Test 2: Get TV shows list
  await testTVShowsList();
  
  // Test 3: Test category filtering with common category names
  if (utilsData && utilsData.data && utilsData.data.categories && utilsData.data.categories.length > 0) {
    const categories = utilsData.data.categories;
    
    // Test with first category name
    if (categories[0]) {
      await testTVShowsWithCategory(categories[0].name);
    }
    
    // Test with category slug if available
    if (categories[0] && categories[0].slug) {
      await testTVShowsWithCategorySlug(categories[0].slug);
    }
    
    // Test with "kdramas" if it exists
    const kdramas = categories.find(c => 
      c.name.toLowerCase().includes('kdrama') || 
      c.name.toLowerCase().includes('k-drama') ||
      c.slug?.toLowerCase().includes('kdrama')
    );
    if (kdramas) {
      console.log(`\n[INFO] Found "kdramas" category: ${kdramas.name} (slug: ${kdramas.slug})`);
      await testTVShowsWithCategory(kdramas.name);
      if (kdramas.slug) {
        await testTVShowsWithCategorySlug(kdramas.slug);
      }
    } else {
      console.log('\n[INFO] "kdramas" category not found in categories list');
      // Try testing with "kdramas" anyway
      await testTVShowsWithCategory('kdramas');
    }
  } else {
    console.log('\n[WARNING] Could not get categories list, testing with common names');
    await testTVShowsWithCategory('kdramas');
  }
  
  console.log('\n========================================');
  console.log('Tests completed!');
  console.log('========================================');
}

// Run tests
runTests().catch(console.error);

