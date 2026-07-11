const fs = require('fs');
const path = require('path');
const dir = 'resources/js/views/admin';

const replacements = [
  { search: 'style="display: grid; grid-template-columns: 2fr 1fr; ', replace: 'class="responsive-grid-2-1" style="' },
  { search: 'style="display: grid; grid-template-columns: 1fr 1fr; ', replace: 'class="responsive-grid-1-1" style="' },
  { search: 'style="display: grid; grid-template-columns: 1fr 2fr; ', replace: 'class="responsive-grid-1-2" style="' },
  { search: 'style="display: grid; grid-template-columns: 3fr 1fr; ', replace: 'class="responsive-grid-3-1" style="' },
  { search: 'style="display: grid; grid-template-columns: 1fr 3fr; ', replace: 'class="responsive-grid-1-3" style="' },
  { search: 'style="display: grid; grid-template-columns: 3fr 2fr; ', replace: 'class="responsive-grid-3-2" style="' },
  { search: 'style="display: grid; grid-template-columns: 200px 1fr; ', replace: 'class="responsive-grid-200-1" style="' },
  { search: 'style="display: grid; grid-template-columns: 80px 1fr; ', replace: 'class="responsive-grid-80-1" style="' },
  { search: 'style="grid-template-columns: 1fr 1fr; ', replace: 'class="responsive-grid-1-1" style="' },
  
  // Cases without trailing space
  { search: 'style="display: grid; grid-template-columns: 2fr 1fr;"', replace: 'class="responsive-grid-2-1"' },
  { search: 'style="display: grid; grid-template-columns: 1fr 1fr;"', replace: 'class="responsive-grid-1-1"' },
  { search: 'style="display: grid; grid-template-columns: 1fr 2fr;"', replace: 'class="responsive-grid-1-2"' },
  { search: 'style="display: grid; grid-template-columns: 3fr 1fr;"', replace: 'class="responsive-grid-3-1"' },
  { search: 'style="display: grid; grid-template-columns: 1fr 3fr;"', replace: 'class="responsive-grid-1-3"' },
  { search: 'style="display: grid; grid-template-columns: 3fr 2fr;"', replace: 'class="responsive-grid-3-2"' },
  { search: 'style="display: grid; grid-template-columns: 200px 1fr;"', replace: 'class="responsive-grid-200-1"' },
  { search: 'style="display: grid; grid-template-columns: 80px 1fr;"', replace: 'class="responsive-grid-80-1"' },
  { search: 'style="grid-template-columns: 1fr 1fr;"', replace: 'class="responsive-grid-1-1"' }
];

let filesChanged = 0;

fs.readdirSync(dir).forEach(file => {
  if (file.endsWith('.vue')) {
    const filePath = path.join(dir, file);
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;
    
    replacements.forEach(r => {
      content = content.split(r.search).join(r.replace);
    });
    
    if (content !== original) {
      fs.writeFileSync(filePath, content);
      console.log('Updated', file);
      filesChanged++;
    }
  }
});
console.log('Total files updated:', filesChanged);
