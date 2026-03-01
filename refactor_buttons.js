const fs = require('fs');
const path = require('path');

const targetDir = './app/Views';

function walk(dir) {
    let results = [];
    const list = fs.readdirSync(dir);
    list.forEach(file => {
        file = path.join(dir, file);
        const stat = fs.statSync(file);
        if (stat && stat.isDirectory()) {
            results = results.concat(walk(file));
        } else {
            if (file.endsWith('.php')) results.push(file);
        }
    });
    return results;
}

function refactorFile(file) {
    let content = fs.readFileSync(file, 'utf8');
    const originalContent = content;

    // Replace btn-primary with btn-solid
    content = content.replace(/btn-primary/g, 'btn-solid');
    
    // Replace btn-danger with btn-solid-danger
    content = content.replace(/btn-danger/g, 'btn-solid-danger');
    
    // Replace btn-secondary with btn-outline (merging secondary logic into outline)
    content = content.replace(/btn-secondary/g, 'btn-outline');

    if (content !== originalContent) {
        fs.writeFileSync(file, content, 'utf8');
        console.log(`Updated: ${file}`);
    }
}

const files = walk(targetDir);
files.forEach(refactorFile);
console.log('Refactor complete.');
