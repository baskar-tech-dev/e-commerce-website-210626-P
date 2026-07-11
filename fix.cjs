const fs = require('fs');
const path = require('path');
const dir = 'resources/js/views/admin';

fs.readdirSync(dir).forEach(file => {
  if (file.endsWith('.vue')) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;
    
    // Fix: class="grid-2" class="responsive-grid-1-1" => class="grid-2 responsive-grid-1-1"
    content = content.replace(/class=\"([^\"]+)\"\s+class=\"([^\"]+)\"/g, 'class=\"$1 $2\"');
    
    if (content !== original) {
      fs.writeFileSync(filePath, content);
      console.log('Fixed', file);
    }
  }
});
