const fs = require('fs');
const content = fs.readFileSync('resources/views/events/index.blade.php', 'utf8');
const lines = content.split('\n');
let stack = [];
let errorFound = false;

for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    const tags = line.match(/<div[ >]|<\/div>/g) || [];
    for (const tag of tags) {
        if (tag.startsWith('<div')) {
            stack.push(i + 1);
        } else {
            if (stack.length === 0) {
                console.log(`EXTRA CLOSE at line ${i + 1}: ${line.trim()}`);
                errorFound = true;
            } else {
                stack.pop();
            }
        }
    }
}

if (stack.length > 0) {
    console.log(`UNCLOSED openings at: ${stack.join(', ')}`);
} else if (!errorFound) {
    console.log('PERFECTLY BALANCED');
}
