const fs = require('fs');
const path = require('path');

function walkDir(dir, callback) {
    fs.readdirSync(dir).forEach(f => {
        let dirPath = path.join(dir, f);
        let isDirectory = fs.statSync(dirPath).isDirectory();
        isDirectory ? walkDir(dirPath, callback) : callback(dirPath);
    });
}

function processFile(filePath) {
    if (!filePath.endsWith('.php') && !filePath.endsWith('.js')) return;
    
    let content = fs.readFileSync(filePath, 'utf8');
    let original = content;

    // Progress bars
    content = content.replace(/bg-blue-600 h-full/g, 'bg-emerald-700 h-full');
    
    // Pager
    content = content.replace(/bg-blue-600 text-white shadow-lg shadow-blue-900\/40/g, 'bg-emerald-700 text-white shadow-lg shadow-emerald-900/40');
    
    // Flash message
    content = content.replace(/bg-blue-600 text-white/g, 'bg-emerald-700 text-white');
    
    // Chart Legends PHP
    content = content.replace(/\$bgClass = 'bg-blue-600'/g, "$bgClass = 'bg-emerald-500'");
    
    // JS text-blue-400
    content = content.replace(/'INFO': 'text-blue-400'/g, "'INFO': 'text-emerald-500'");
    
    // Card Metric component
    content = content.replace(/'bg-blue-600'/g, "'bg-emerald-500'");
    content = content.replace(/'border-blue-200'/g, "'border-gray-200'");
    content = content.replace(/'group-hover:ring-blue-600'/g, "'group-hover:ring-emerald-500'");
    content = content.replace(/'bg-blue-50'/g, "'bg-white border-l-4 border-l-emerald-700'");

    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`Updated ${filePath}`);
    }
}

walkDir('app/Views', processFile);
walkDir('public/js', processFile);
