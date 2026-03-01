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

    // 1. Slate -> Gray
    content = content.replace(/-slate-/g, '-gray-');

    // 2. Badges
    content = content.replace(/bg-emerald-50 text-emerald-600 border-emerald-200/g, 'bg-emerald-100 text-emerald-800 border-transparent');
    content = content.replace(/bg-emerald-50 text-emerald-600 border border-emerald-200/g, 'bg-emerald-100 text-emerald-800 border-transparent');
    
    content = content.replace(/bg-amber-50 text-amber-600 border-amber-200/g, 'bg-amber-100 text-amber-700 border-transparent');
    content = content.replace(/bg-amber-50 text-amber-600 border border-amber-200/g, 'bg-amber-100 text-amber-700 border-transparent');
    
    content = content.replace(/bg-red-50 text-red-600 border-red-200/g, 'bg-red-100 text-red-700 border-transparent');
    content = content.replace(/bg-red-50 text-red-600 border border-red-200/g, 'bg-red-100 text-red-700 border-transparent');
    
    content = content.replace(/bg-blue-50 text-blue-600 border-blue-200/g, 'bg-blue-100 text-blue-700 border-transparent');
    content = content.replace(/bg-blue-50 text-blue-600 border border-blue-200/g, 'bg-blue-100 text-blue-700 border-transparent');

    content = content.replace(/bg-gray-50 text-gray-700 border-gray-200/g, 'bg-gray-100 text-gray-700 border-transparent');
    content = content.replace(/bg-gray-50 text-gray-700 border border-gray-200/g, 'bg-gray-100 text-gray-700 border-transparent');

    // 3. Colored Cards -> White with emphasis border
    content = content.replace(/bg-emerald-50 border border-emerald-200/g, 'bg-white border border-gray-200 border-l-4 border-l-emerald-700');
    content = content.replace(/bg-blue-50 border border-blue-200/g, 'bg-white border border-gray-200 border-l-4 border-l-emerald-700');
    content = content.replace(/bg-amber-50 border border-amber-200/g, 'bg-white border border-gray-200 border-l-4 border-l-emerald-700');
    content = content.replace(/bg-red-50 border border-red-200/g, 'bg-white border border-gray-200 border-l-4 border-l-emerald-700');

    // Card titles/labels inside the colored cards were colored too (e.g. text-emerald-600)
    // Make them gray-500
    content = content.replace(/text-emerald-600 uppercase tracking-widest/g, 'text-gray-500 uppercase tracking-widest');
    content = content.replace(/text-blue-600 uppercase tracking-widest/g, 'text-gray-500 uppercase tracking-widest');
    content = content.replace(/text-amber-500 uppercase tracking-widest/g, 'text-gray-500 uppercase tracking-widest');
    content = content.replace(/text-red-600 uppercase tracking-widest/g, 'text-gray-500 uppercase tracking-widest');

    // 4. Buttons
    // Primary Button
    content = content.replace(/bg-gray-800 hover:bg-gray-700 text-white/g, 'bg-emerald-700 hover:bg-emerald-800 text-white');
    content = content.replace(/bg-gray-800 text-white hover:bg-gray-700/g, 'bg-emerald-700 text-white hover:bg-emerald-800');
    content = content.replace(/bg-blue-600 hover:bg-blue-700 text-white/g, 'bg-emerald-700 hover:bg-emerald-800 text-white');
    
    // Secondary Button
    content = content.replace(/bg-white border border-gray-200 hover:bg-gray-50 text-gray-700/g, 'bg-gray-200 hover:bg-gray-300 text-gray-800 border-transparent');
    content = content.replace(/bg-white border border-gray-200 text-gray-700 hover:bg-gray-50/g, 'bg-gray-200 hover:bg-gray-300 text-gray-800 border-transparent');
    
    // Icon buttons for Edit/Delete
    content = content.replace(/bg-white border border-gray-200 text-gray-700 hover:text-gray-800/g, 'bg-gray-200 text-gray-800 hover:bg-gray-300 border-transparent');
    content = content.replace(/bg-white border border-gray-200 text-gray-700 hover:text-red-600/g, 'bg-red-50 text-red-600 hover:bg-red-600 hover:text-white border-transparent');

    // 5. Tables
    content = content.replace(/bg-gray-50 text-gray-700 uppercase/g, 'bg-gray-100 text-gray-700 uppercase');
    
    // 6. Sidebar (if it's sidebar.php)
    if (filePath.includes('sidebar.php')) {
        content = content.replace(/hover:bg-gray-700/g, 'hover:bg-emerald-700\/80');
        content = content.replace(/bg-blue-600/g, 'bg-emerald-700');
        content = content.replace(/shadow-blue-900\/20/g, 'shadow-emerald-900\/20');
        content = content.replace(/shadow-blue-900\/20/g, 'shadow-emerald-900/20');
    }

    // 7. General fixes
    // replace blue borders/rings in inputs with emerald
    content = content.replace(/focus:border-blue-600/g, 'focus:border-emerald-700');
    content = content.replace(/focus:ring-blue-600/g, 'focus:ring-emerald-700');
    content = content.replace(/text-blue-600/g, 'text-emerald-700');
    content = content.replace(/border-blue-600/g, 'border-emerald-700');

    // Charts Palette in unit_kerja_detail.php & index.php
    content = content.replace(/'#059669'/g, "'#047857'"); // emerald-600 to emerald-700
    content = content.replace(/'#2563eb'/g, "'#10b981'"); // blue to emerald-500
    content = content.replace(/'#f59e0b'/g, "'#6ee7b7'"); // amber to emerald-300
    content = content.replace(/'#334155'/g, "'#9ca3af'"); // slate-700 to gray-400
    content = content.replace(/'#475569'/g, "'#d1d5db'"); // slate-600 to gray-300
    content = content.replace(/'#94a3b8'/g, "'#e5e7eb'"); // slate-400 to gray-200

    // Also chart color arrays
    content = content.replace(/\['#2563eb', '#059669', '#f59e0b', '#dc2626', '#1e293b', '#334155', '#f1f5f9', '#f8fafc'\]/g, "['#047857', '#10b981', '#6ee7b7', '#dc2626', '#9ca3af', '#e5e7eb', '#f3f4f6', '#f9fafb']");

    // rounded-xl -> rounded-lg
    content = content.replace(/rounded-xl/g, 'rounded-lg');

    // Layout Background
    if (filePath.includes('layouts/main.php')) {
        content = content.replace(/bg-gray-50/g, 'bg-gray-50'); // Just checking, we mapped slate-50 to gray-50 already.
    }

    if (content !== original) {
        fs.writeFileSync(filePath, content, 'utf8');
        console.log(`Updated ${filePath}`);
    }
}

walkDir('app/Views', processFile);
walkDir('public/js', processFile);
