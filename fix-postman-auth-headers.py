#!/usr/bin/env python3
"""
Fix Postman Collection - Add explicit Authorization headers
This script adds Authorization header to all requests that have bearer auth configured
"""

import json

def add_authorization_header(request):
    """Add Authorization header if bearer auth is configured"""
    if 'auth' in request and request['auth'].get('type') == 'bearer':
        # Get the token value from bearer auth
        bearer_config = request['auth'].get('bearer', [])
        token_value = None
        for item in bearer_config:
            if item.get('key') == 'token':
                token_value = item.get('value', '{{token}}')
                break
        
        if token_value:
            # Add Authorization header if not already present
            if 'header' not in request:
                request['header'] = []
            
            # Check if Authorization header already exists
            has_auth_header = False
            for header in request['header']:
                if header.get('key') == 'Authorization':
                    has_auth_header = True
                    # Update existing header
                    header['value'] = f'Bearer {token_value}'
                    header['type'] = 'text'
                    break
            
            # Add new Authorization header if not present
            if not has_auth_header:
                request['header'].insert(0, {
                    'key': 'Authorization',
                    'value': f'Bearer {token_value}',
                    'type': 'text'
                })
    
    return request

def process_items(items):
    """Recursively process all items in the collection"""
    for item in items:
        if 'item' in item:
            # This is a folder, process its children
            process_items(item['item'])
        elif 'request' in item:
            # This is a request, add Authorization header if needed
            item['request'] = add_authorization_header(item['request'])

def main():
    # Read the Postman collection
    with open('UNIFIED_APP_POSTMAN_COLLECTION.json', 'r', encoding='utf-8') as f:
        collection = json.load(f)
    
    # Process all items
    if 'item' in collection:
        process_items(collection['item'])
    
    # Write the updated collection
    with open('UNIFIED_APP_POSTMAN_COLLECTION.json', 'w', encoding='utf-8') as f:
        json.dump(collection, f, indent=4, ensure_ascii=False)
    
    print('✅ Successfully added Authorization headers to all authenticated requests')
    print('📝 Updated: UNIFIED_APP_POSTMAN_COLLECTION.json')

if __name__ == '__main__':
    main()
