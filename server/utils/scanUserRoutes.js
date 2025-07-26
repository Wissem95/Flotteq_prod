const fs = require('fs');
const path = require('path');

const scanUserRoutes = (baseDir) => {
  const results = [];
  const pattern = /["'`]\/(api\/)?users(\/?[^"'`]*)?["'`]/g;

  const scan = (dir) => {
    fs.readdirSync(dir).forEach(file => {
      const fullPath = path.join(dir, file);
      const stat = fs.statSync(fullPath);

      if (stat.isDirectory()) {
        scan(fullPath);
      } else if (file.endsWith('.ts') || file.endsWith('.tsx')) {
        const content = fs.readFileSync(fullPath, 'utf-8');
        const matches = [...content.matchAll(pattern)];

        if (matches.length > 0) {
          results.push({
            file: fullPath,
            matches: matches.map(m => m[0]),
          });
        }
      }
    });
  };

  scan(baseDir);
  return results;
};

module.exports = scanUserRoutes;

