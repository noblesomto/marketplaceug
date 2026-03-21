#!/usr/bin/env python3
"""
Update Postman collection with:
1. Buy Direct endpoints (in Adverts section)
2. Category UI Config endpoints (new section)
"""

import json
import uuid
from datetime import datetime

def generate_uid():
    """Generate Postman-style UID"""
    return str(uuid.uuid4().hex[:10] + "-" + uuid.uuid4().hex[:4] + "-" + uuid.uuid4().hex[:4] + "-" + uuid.uuid4().hex[:4] + "-" + uuid.uuid4().hex[:12])

def create_buy_direct_endpoint():
    """Create GET /api/adverts/{id}/buy-direct endpoint"""
    return {
        "name": "Adverts {Id} Buy Direct",
        "request": {
            "auth": {
                "type": "bearer",
                "bearer": [
                    {
                        "key": "token",
                        "value": "{{auth_token}}",
                        "type": "string"
                    }
                ]
            },
            "method": "GET",
            "header": [
                {
                    "key": "Accept",
                    "value": "application/json",
                    "type": "text"
                }
            ],
            "url": {
                "raw": "{{base_url}}/api/adverts/:id/buy-direct",
                "host": ["{{base_url}}"],
                "path": ["api", "adverts", ":id", "buy-direct"],
                "variable": [
                    {
                        "key": "id",
                        "value": "1",
                        "description": "Advert ID"
                    }
                ]
            },
            "description": "Get details for direct purchase including ad info, authenticated user, and available states.\n\n**Authentication:** Required (Bearer token)\n\n**Response includes:**\n- Ad details with images and shipping options\n- User information\n- List of available states for shipping"
        },
        "response": []
    }

def create_buy_direct_payment_endpoint():
    """Create POST /api/adverts/{id}/buy-direct-payment endpoint"""
    return {
        "name": "Adverts {Id} Buy Direct Payment",
        "request": {
            "auth": {
                "type": "bearer",
                "bearer": [
                    {
                        "key": "token",
                        "value": "{{auth_token}}",
                        "type": "string"
                    }
                ]
            },
            "method": "POST",
            "header": [
                {
                    "key": "Accept",
                    "value": "application/json",
                    "type": "text"
                },
                {
                    "key": "Content-Type",
                    "value": "application/json",
                    "type": "text"
                }
            ],
            "body": {
                "mode": "raw",
                "raw": json.dumps({
                    "shipping_method_id": 1,
                    "shipping_data": {
                        "recipient_name": "John Doe",
                        "recipient_phone": "08012345678",
                        "delivery_address": "123 Main Street",
                        "state": "Lagos",
                        "city": "Ikeja",
                        "postal_code": "100001"
                    }
                }, indent=2)
            },
            "url": {
                "raw": "{{base_url}}/api/adverts/:id/buy-direct-payment",
                "host": ["{{base_url}}"],
                "path": ["api", "adverts", ":id", "buy-direct-payment"],
                "variable": [
                    {
                        "key": "id",
                        "value": "1",
                        "description": "Advert ID"
                    }
                ]
            },
            "description": "Get payment details for direct purchase with shipping information.\n\n**Authentication:** Required (Bearer token)\n\n**Request Body:**\n- `shipping_method_id` (required, integer): Selected shipping method ID\n- `shipping_data` (required, object): Shipping and delivery information\n  - `recipient_name`: Full name of recipient\n  - `recipient_phone`: Phone number\n  - `delivery_address`: Street address\n  - `state`: State name\n  - `city`: City name\n  - `postal_code`: Postal/ZIP code\n\n**Response includes:**\n- Ad details\n- User information\n- Shipping method details\n- Shipping data for confirmation"
        },
        "response": []
    }

def create_ui_config_folder():
    """Create Category UI Config folder with all endpoints"""
    return {
        "name": "13. Category UI Config",
        "item": [
            {
                "name": "UI Config All",
                "request": {
                    "method": "GET",
                    "header": [
                        {
                            "key": "Accept",
                            "value": "application/json",
                            "type": "text"
                        }
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/ui-config/all",
                        "host": ["{{base_url}}"],
                        "path": ["api", "ui-config", "all"]
                    },
                    "description": "Get all UI configurations for categories and subcategories.\n\n**Authentication:** Not required (public)\n**Cache:** 24 hours server-side\n\n**Use Case:** Mobile app should fetch this on app start and cache locally\n\n**Response Structure:**\n```json\n{\n  \"success\": true,\n  \"data\": {\n    \"categories\": {\n      \"1\": {\"show\": [...], \"hide\": [...], \"labels\": {...}},\n      \"2\": {...}\n    },\n    \"subcategories\": {\n      \"1\": {\"show\": [...], \"hide\": [...], \"required\": [...]},\n      \"2\": {...}\n    }\n  },\n  \"cache_expires_at\": \"2026-02-01 12:00:00\"\n}\n```\n\n**Mobile Implementation:**\n1. Fetch on app start\n2. Cache locally for 24 hours\n3. Apply show/hide rules when user selects category/subcategory\n4. Dynamically show/hide form fields based on config\n5. Apply custom labels to dropdowns"
                },
                "response": []
            },
            {
                "name": "UI Config Category {CategoryId}",
                "request": {
                    "method": "GET",
                    "header": [
                        {
                            "key": "Accept",
                            "value": "application/json",
                            "type": "text"
                        }
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/ui-config/category/:id",
                        "host": ["{{base_url}}"],
                        "path": ["api", "ui-config", "category", ":id"],
                        "variable": [
                            {
                                "key": "id",
                                "value": "1",
                                "description": "Category ID"
                            }
                        ]
                    },
                    "description": "Get UI configuration for a specific category.\n\n**Authentication:** Not required\n\n**Example Response:**\n```json\n{\n  \"success\": true,\n  \"data\": {\n    \"show\": [\"salary\"],\n    \"hide\": [\"price\", \"shipment\", \"itemCondition\"],\n    \"labels\": {\n      \"brand\": \"Select Job Type:\"\n    }\n  }\n}\n```\n\n**Common Configurations:**\n- **Jobs Category:** Show salary, hide price/shipment\n- **Services Category:** Show services field, hide price\n- **Vehicles Category:** Show price, itemCondition, shipment\n- **Real Estate:** Show price, hide shipment"
                },
                "response": []
            },
            {
                "name": "UI Config Subcategory {SubcategoryId}",
                "request": {
                    "method": "GET",
                    "header": [
                        {
                            "key": "Accept",
                            "value": "application/json",
                            "type": "text"
                        }
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/ui-config/subcategory/:id",
                        "host": ["{{base_url}}"],
                        "path": ["api", "ui-config", "subcategory", ":id"],
                        "variable": [
                            {
                                "key": "id",
                                "value": "1",
                                "description": "Subcategory ID"
                            }
                        ]
                    },
                    "description": "Get UI configuration for a specific subcategory.\n\n**Authentication:** Not required\n\n**Note:** Subcategory rules are ADDITIVE to category rules\n\n**Example Response:**\n```json\n{\n  \"success\": true,\n  \"data\": {\n    \"show\": [\"divCar\", \"divModel\"],\n    \"hide\": [\"divPhone\"],\n    \"labels\": {\n      \"brand\": \"Select Car Brand:\"\n    },\n    \"required\": [\"model\"]\n  }\n}\n```\n\n**Mobile Implementation:**\n1. Fetch category config first\n2. Apply category show/hide rules\n3. Fetch subcategory config when user selects subcategory\n4. Apply subcategory rules ON TOP of category rules\n5. Show subcategory-specific sections (divCar, divPhone, etc.)\n6. Mark fields in `required` array as mandatory"
                },
                "response": []
            },
            {
                "name": "UI Config Clear Cache",
                "request": {
                    "method": "POST",
                    "header": [
                        {
                            "key": "Accept",
                            "value": "application/json",
                            "type": "text"
                        }
                    ],
                    "url": {
                        "raw": "{{base_url}}/api/ui-config/clear-cache",
                        "host": ["{{base_url}}"],
                        "path": ["api", "ui-config", "clear-cache"]
                    },
                    "description": "Clear the UI config cache (admin use only).\n\n**Authentication:** Not enforced but intended for admin use\n\n**Use Case:** After updating UI config in admin panel, call this to force refresh\n\n**Response:**\n```json\n{\n  \"success\": true,\n  \"message\": \"UI config cache cleared successfully\"\n}\n```"
                },
                "response": []
            }
        ],
        "description": "**Database-Driven UI Configuration System**\n\nThis API provides dynamic show/hide rules for Post Ad and Edit Ad forms. Admins can configure which form fields should be visible or hidden for each category/subcategory without code changes.\n\n**Key Concepts:**\n\n1. **Category Config:** Base configuration applied when user selects a category\n   - Example: Jobs category shows \"salary\" field, hides \"price\" field\n\n2. **Subcategory Config:** Additional rules applied on top of category config\n   - Example: Cars subcategory shows car-specific fields (divCar, divModel)\n   - Rules are ADDITIVE, not replacements\n\n3. **Available Elements:**\n   - **Form Fields:** price, salary, expectedSalary, services, quantity\n   - **Sections:** divCar (car details), divPhone (phone details), divModel (model dropdown)\n   - **Options:** shipment, shipping, itemCondition, buyDirect\n\n4. **Custom Labels:**\n   - Admins can customize field labels per category\n   - Example: \"Select Job Type:\" for Jobs, \"Select Car Brand:\" for Cars\n\n5. **Required Fields (Subcategories Only):**\n   - Mark specific fields as required for certain subcategories\n   - Example: \"model\" required for Cars subcategory\n\n**Mobile Implementation Guide:**\n\n```javascript\n// 1. Fetch all configs on app start\nconst uiConfig = await fetch('/api/ui-config/all');\nlocalStorage.setItem('uiConfig', JSON.stringify(uiConfig));\n\n// 2. When user selects category\nfunction onCategorySelect(categoryId) {\n  const config = uiConfig.categories[categoryId];\n  \n  // Hide elements\n  config.hide.forEach(element => {\n    document.getElementById(element).style.display = 'none';\n  });\n  \n  // Show elements\n  config.show.forEach(element => {\n    document.getElementById(element).style.display = 'block';\n  });\n  \n  // Apply custom labels\n  if (config.labels?.brand) {\n    document.querySelector('#brandLabel').textContent = config.labels.brand;\n  }\n}\n\n// 3. When user selects subcategory (additive)\nfunction onSubcategorySelect(subcategoryId) {\n  const subConfig = uiConfig.subcategories[subcategoryId];\n  \n  // Apply subcategory-specific show/hide\n  // These are applied ON TOP of category rules\n  subConfig.hide?.forEach(element => {\n    document.getElementById(element).style.display = 'none';\n  });\n  \n  subConfig.show?.forEach(element => {\n    document.getElementById(element).style.display = 'block';\n  });\n  \n  // Mark required fields\n  subConfig.required?.forEach(field => {\n    document.querySelector(`[name=\"${field}\"]`).setAttribute('required', true);\n  });\n}\n```\n\n**Performance:**\n- Cached server-side for 24 hours\n- Mobile apps should cache locally and refresh daily\n- Fallback config embedded in app for offline support\n\n**Admin Panel:**\n- Admins manage configs at `/admin/category-ui`\n- Changes take effect immediately after cache clear\n- No code deployment required for UI changes"
    }

def update_postman_collection():
    """Update Postman collection with new endpoints"""

    # Read existing collection
    with open('postman/Marketplace-API-Complete.postman_collection.json', 'r') as f:
        collection = json.load(f)

    # Find Adverts section
    adverts_section = None
    for i, item in enumerate(collection['item']):
        if item['name'] == '02. Adverts':
            adverts_section = i
            break

    if adverts_section is not None:
        # Add buy_direct endpoints to Adverts section
        buy_direct_get = create_buy_direct_endpoint()
        buy_direct_post = create_buy_direct_payment_endpoint()

        # Insert after "Adverts  Id  Apply" endpoint
        insert_index = None
        for i, endpoint in enumerate(collection['item'][adverts_section]['item']):
            if 'Apply' in endpoint.get('name', ''):
                insert_index = i + 1
                break

        if insert_index:
            collection['item'][adverts_section]['item'].insert(insert_index, buy_direct_get)
            collection['item'][adverts_section]['item'].insert(insert_index + 1, buy_direct_post)
            print(f"✅ Added Buy Direct endpoints to Adverts section (index {insert_index})")
        else:
            # Append to end of Adverts section
            collection['item'][adverts_section]['item'].extend([buy_direct_get, buy_direct_post])
            print("✅ Added Buy Direct endpoints to end of Adverts section")

    # Add Category UI Config folder
    ui_config_folder = create_ui_config_folder()
    collection['item'].append(ui_config_folder)
    print("✅ Added Category UI Config section (new folder)")

    # Save updated collection
    with open('postman/Marketplace-API-Complete.postman_collection.json', 'w') as f:
        json.dump(collection, f, indent=2)

    print("\n✅ Postman collection updated successfully!")
    print("\nAdded endpoints:")
    print("  - GET /api/adverts/{id}/buy-direct")
    print("  - POST /api/adverts/{id}/buy-direct-payment")
    print("  - GET /api/ui-config/all")
    print("  - GET /api/ui-config/category/{id}")
    print("  - GET /api/ui-config/subcategory/{id}")
    print("  - POST /api/ui-config/clear-cache")
    print("\n📝 Total endpoints in collection:", sum(len(item.get('item', [])) if 'item' in item else 1 for item in collection['item']))

if __name__ == '__main__':
    try:
        update_postman_collection()
    except Exception as e:
        print(f"❌ Error: {e}")
        import traceback
        traceback.print_exc()
