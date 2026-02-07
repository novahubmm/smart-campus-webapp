#!/usr/bin/env python3
"""
Fix Free Period Activity paths in Postman Collection
Change /free-period/* to /teacher/free-period/*
"""

import json

def fix_free_period_paths(items):
    """Recursively fix free period paths in all items"""
    for item in items:
        if 'item' in item:
            # This is a folder, process its children
            fix_free_period_paths(item['item'])
        elif 'request' in item and 'url' in item['request']:
            url = item['request']['url']
            
            # Fix the raw URL
            if isinstance(url, dict) and 'raw' in url:
                if '/free-period/' in url['raw'] and '/teacher/free-period/' not in url['raw']:
                    url['raw'] = url['raw'].replace('/free-period/', '/teacher/free-period/')
                    print(f"Fixed raw URL: {url['raw']}")
            
            # Fix the path array
            if isinstance(url, dict) and 'path' in url:
                if isinstance(url['path'], list):
                    # Check if path starts with 'free-period'
                    if len(url['path']) > 0 and url['path'][0] == 'free-period':
                        # Insert 'teacher' at the beginning
                        url['path'].insert(0, 'teacher')
                        print(f"Fixed path array: {url['path']}")

def main():
    # Read the Postman collection
    with open('UNIFIED_APP_POSTMAN_COLLECTION.json', 'r', encoding='utf-8') as f:
        collection = json.load(f)
    
    # Process all items
    if 'item' in collection:
        fix_free_period_paths(collection['item'])
    
    # Write the updated collection
    with open('UNIFIED_APP_POSTMAN_COLLECTION.json', 'w', encoding='utf-8') as f:
        json.dump(collection, f, indent=4, ensure_ascii=False)
    
    print('\n✅ Successfully fixed all free period activity paths')
    print('📝 Updated: UNIFIED_APP_POSTMAN_COLLECTION.json')
    print('\nAll /free-period/* paths are now /teacher/free-period/*')

if __name__ == '__main__':
    main()
