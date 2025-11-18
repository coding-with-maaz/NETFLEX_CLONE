const http = require('http');

const BASE_URL = 'http://localhost:8080/api/v1';

function testCategory(categoryValue) {
  return new Promise((resolve) => {
    const url = `${BASE_URL}/tvshows?category=${encodeURIComponent(categoryValue)}&limit=5`;
    console.log(`\nTesting: "${categoryValue}"`);
    console.log(`URL: ${url}`);
    
    http.get(url, (res) => {
      let data = '';
      res.on('data', (chunk) => { data += chunk; });
      res.on('end', () => {
        try {
          const json = JSON.parse(data);
          if (json.data && json.data.tvShows) {
            const tvShows = json.data.tvShows;
            const categoryIds = tvShows.map(tv => tv.category_id || 'null');
            const uniqueCategories = [...new Set(categoryIds)];
            console.log(`Result: ${tvShows.length} TV shows`);
            console.log(`Category IDs found: ${uniqueCategories.join(', ')}`);
            if (uniqueCategories.length === 1 && uniqueCategories[0] === '1') {
              console.log('✅ CORRECT: All TV shows have category_id = 1');
            } else {
              console.log('❌ INCORRECT: Mixed category IDs - filter not working');
            }
          }
          resolve(json);
        } catch (e) {
          console.log(`Error: ${e.message}`);
          resolve(null);
        }
      });
    }).on('error', (err) => {
      console.log(`Request error: ${err.message}`);
      resolve(null);
    });
  });
}

async function run() {
  console.log('========================================');
  console.log('Debugging Category Filter');
  console.log('========================================');
  
  await testCategory('1');           // ID
  await testCategory('k-drama');     // Slug (lowercase)
  await testCategory('K-drama');     // Slug (mixed case)
  await testCategory('K-Drama');    // Name (exact)
  await testCategory('k-Drama');    // Name (mixed case)
  await testCategory('kdrama');      // Without hyphen
  
  console.log('\n========================================');
  console.log('Check backend console for SQL queries');
  console.log('========================================');
}

run();

