/**
 * Generate PWA Icons
 * Run: node generate-pwa-icons.js
 *
 * This script creates placeholder icons for PWA.
 * Replace these with your actual logo later.
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const sizes = [72, 96, 128, 144, 152, 192, 384, 512];
const iconsDir = path.join(__dirname, 'public', 'images', 'icons');

// Ensure icons directory exists
if (!fs.existsSync(iconsDir)) {
    fs.mkdirSync(iconsDir, { recursive: true });
}

// Generate SVG icons (placeholder)
sizes.forEach(size => {
    const svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#4d46e5;stop-opacity:1" />
            <stop offset="100%" style="stop-color:#6366f1;stop-opacity:1" />
        </linearGradient>
    </defs>
    <rect width="${size}" height="${size}" fill="url(#grad)" rx="${size * 0.15}" />
    <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="${size * 0.4}" font-weight="bold" fill="white" text-anchor="middle" dominant-baseline="central">NH</text>
</svg>`;

    const filename = path.join(iconsDir, `icon-${size}x${size}.svg`);
    fs.writeFileSync(filename, svg);
    console.log(`✓ Created ${size}x${size} icon`);
});

console.log('\n✅ All icons generated successfully!');
console.log('\n📝 Note: These are placeholder icons with "NH" text.');
console.log('   To use your logo:');
console.log('   1. Open public/images/icons/generate-icons.html in browser');
console.log('   2. Or use online tool: https://realfavicongenerator.net/');
console.log('   3. Replace the generated SVG files with PNG versions\n');
