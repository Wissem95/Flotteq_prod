const fs = require('fs');
const path = require('path');

const baseDir = path.join(__dirname, 'clients/src');
const targetPattern = /["'`]\/(api\/)?users(\/?[^"'`]*)?["'`]/g;

function scanFiles(dir) {
  fs.readdirSync(dir).forEach((file) => {
    const fullPath = path.join(dir, file);
    const stat = fs.statSync(fullPath);

    if (stat.isDirectory()) {
      scanFiles(fullPath);
    } else if (file.endsWith('.ts') || file.endsWith('.tsx')) {
      const content = fs.readFileSync(fullPath, 'utf-8');
      const matches = [...content.matchAll(targetPattern)];

      if (matches.length > 0) {
        console.log(`\n🔍 ${fullPath}`);
        matches.forEach(match => {
          console.log(`   👉 ${match[0]}`);
        });
      }
    }
  });
}

console.log('🔎 Recherche des anciens appels à "/users"...');
scanFiles(baseDir);
console.log('\n✅ Scan terminé.');

