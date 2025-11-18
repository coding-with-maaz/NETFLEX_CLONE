const http = require('http');

const BASE_URL = 'http://localhost:8080/api/v1';

function makeRequest(path, callback) {
  const url = `${BASE_URL}${path}`;
  console.log(`\n[TEST] GET ${url}`);
  
  http.get(url, (res) => {
    let data = '';
    res.on('data', (chunk) => { data += chunk; });
    res.on('end', () => {
      try {
        const json = JSON.parse(data);
        console.log(`[RESPONSE] Status: ${res.statusCode}, Success: ${json.success}`);
        if (json.data && json.data.tvShows) {
          const tvShows = json.data.tvShows;
          console.log(`[RESPONSE] Found ${tvShows.length} TV shows`);
          
          // Check category_id distribution
          const categoryCounts = {};
          tvShows.forEach(tv => {
            const catId = tv.category_id || 'null';
            categoryCounts[catId] = (categoryCounts[catId] || 0) + 1;
          });
          console.log(`[RESPONSE] Category ID distribution:`, categoryCounts);
          
          // Show first 3 TV shows
          if (tvShows.length > 0) {
            console.log(`[RESPONSE] First 3 TV shows:`);
            tvShows.slice(0, 3).forEach((tv, i) => {
              console.log(`  ${i + 1}. ${tv.name} (category_id: ${tv.category_id || 'null'})`);
            });
          }
        }
        callback(null, json);
      } catch (e) {
        console.log(`[ERROR] ${e.message}`);
        callback(e, null);
      }
    });
  }).on('error', (err) => {
    console.log(`[ERROR] ${err.message}`);
    callback(err, null);
  });
}

async function testCategoryFilter(categoryValue, description) {
  return new Promise((resolve) => {
    console.log(`\n=== ${description} ===`);
    const encoded = encodeURIComponent(categoryValue);
    makeRequest(`/tvshows?category=${encoded}&limit=10`, (err, data) => {
      resolve(data);
    });
  });
}

async function runTests() {
  console.log('========================================');
  console.log('Testing Category Filtering');
  console.log('========================================');
  
  // Test with category ID
  await testCategoryFilter('1', 'Testing with Category ID: 1 (K-Drama)');
  
  // Test with category name
  await testCategoryFilter('K-Drama', 'Testing with Category Name: "K-Drama"');
  
  // Test with category slug
  await testCategoryFilter('k-drama', 'Testing with Category Slug: "k-drama"');
  
  // Test with lowercase name
  await testCategoryFilter('k-drama', 'Testing with lowercase: "k-drama"');
  
  // Test with invalid category
  await testCategoryFilter('InvalidCategory123', 'Testing with Invalid Category');
  
  console.log('\n========================================');
  console.log('Tests completed!');
  console.log('========================================');
}

runTests().catch(console.error);

