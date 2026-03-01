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

const colorReplacements = {
    'emerald': 'slate',
    'blue': 'slate',
    'indigo': 'slate',
    'green': 'slate',
    'gray': 'slate',
    'zinc': 'slate',
    'neutral': 'slate',
};

function refactorFile(file) {
    let content = fs.readFileSync(file, 'utf8');
    const originalContent = content;

    // 1. Full replace for gray/zinc/neutral as they are always replaced
    content = content.replace(/(gray|zinc|neutral)-/g, 'slate-');

    // 2. Selective replace for emerald/blue/indigo/green
    // Only if they are NOT followed by 100 or 50 (standard badge backgrounds)
    // and NOT followed by 800 (standard badge text) if it's part of bg-X-100 text-X-800
    
    const families = ['emerald', 'blue', 'indigo', 'green'];
    families.forEach(family => {
        // bg-family-700 -> bg-slate-700
        // but avoid bg-family-100, bg-family-50
        const bgRegex = new RegExp(`bg-${family}-(?!100|50)(\\d+)`, 'g');
        content = content.replace(bgRegex, `bg-slate-$1`);

        // text-family-700 -> text-slate-700
        // but avoid text-family-800 (semantic)
        const textRegex = new RegExp(`text-${family}-(?!800)(\\d+)`, 'g');
        content = content.replace(textRegex, `text-slate-$1`);

        // border-family-X -> border-slate-X
        content = content.replace(new RegExp(`border-${family}-`, 'g'), 'border-slate-');
        
        // ring-family-X -> ring-slate-X
        content = content.replace(new RegExp(`ring-${family}-`, 'g'), 'ring-slate-');
        
        // focus: variants
        content = content.replace(new RegExp(`focus:ring-${family}-`, 'g'), 'focus:ring-slate-');
        content = content.replace(new RegExp(`focus:border-${family}-`, 'g'), 'focus:border-slate-');
        
        // hover: variants (avoid semantic hover if any, but usually they are 800)
        content = content.replace(new RegExp(`hover:bg-${family}-`, 'g'), 'hover:bg-slate-');
        content = content.replace(new RegExp(`hover:text-${family}-`, 'g'), 'hover:text-slate-');
    });

    if (content !== originalContent) {
        fs.writeFileSync(file, content, 'utf8');
        console.log(`Updated: ${file}`);
    }
}

const files = walk(targetDir);
files.forEach(refactorFile);
console.log('Refactor complete.');
