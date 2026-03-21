#!/usr/bin/env python3
"""
Script to update the Postman collection with the following changes:
1. Add missing boost endpoints (GET /boost/options, POST /boost/calculate)
2. Fix endpoints with missing test data (replace placeholders)
3. Remove duplicate POST /adverts endpoints
4. Ensure consistent environment variables
"""

import json
import sys

def create_boost_options_endpoint():
    """Create GET /boost/options endpoint"""
    return {
        "name": "Get Boost Options",
        "request": {
            "method": "GET",
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
            "url": {
                "raw": "{{base_url}}/api/boost/options",
                "host": ["{{base_url}}"],
                "path": ["api", "boost", "options"]
            },
            "description": "🔒 **Requires Authentication**\n\nEndpoint: `GET /api/boost/options`\n\nGet available boost packages and pricing tiers\n\nExample response:\n```json\n{\n  \"success\": true,\n  \"data\": {\n    \"packages\": [\n      {\"id\": 1, \"name\": \"Basic\", \"duration_days\": 7, \"price\": 1000},\n      {\"id\": 2, \"name\": \"Premium\", \"duration_days\": 14, \"price\": 1800}\n    ]\n  }\n}\n```",
            "auth": {
                "type": "bearer",
                "bearer": [
                    {
                        "key": "token",
                        "value": "{{auth_token}}",
                        "type": "string"
                    }
                ]
            }
        },
        "response": []
    }

def create_boost_calculate_endpoint():
    """Create POST /boost/calculate endpoint"""
    return {
        "name": "Calculate Boost Price",
        "request": {
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
            "url": {
                "raw": "{{base_url}}/api/boost/calculate",
                "host": ["{{base_url}}"],
                "path": ["api", "boost", "calculate"]
            },
            "description": "🔒 **Requires Authentication**\n\nEndpoint: `POST /api/boost/calculate`\n\nCalculate boost pricing based on parameters",
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
            "body": {
                "mode": "raw",
                "raw": "{\n    \"package_id\": 1,\n    \"duration_days\": 7,\n    \"advert_id\": 123\n}",
                "options": {
                    "raw": {
                        "language": "json"
                    }
                }
            }
        },
        "response": []
    }

def fix_url_placeholders(collection):
    """Fix URL placeholders throughout the collection"""

    placeholder_fixes = [
        # Boost endpoints
        ("/boosts/{boostId}", "/boosts/{{boost_id}}"),
        ("{boostId}", "{{boost_id}}"),
        # User endpoints
        ("/boosts/user/{userId}", "/boosts/user/{{user_id}}"),
        ("{userId}", "{{user_id}}"),
        # Advert endpoints
        ("/adverts/{advertId}/boost-info", "/adverts/{{advert_id}}/boost-info"),
        ("/adverts/{advertId}/boost-status", "/adverts/{{advert_id}}/boost-status"),
        ("/messages/conversation/{advertId}/{receiverId}", "/messages/conversation/{{advert_id}}/{{receiver_id}}"),
        ("/messages/advert/{advertId}", "/messages/advert/{{advert_id}}"),
        ("/messages/conversation/{advertId}/{userId}/read", "/messages/conversation/{{advert_id}}/{{user_id}}/read"),
        # Payment endpoints
        ("{paymentId}", "{{payment_id}}"),
    ]

    def fix_item(item):
        if "request" in item and "url" in item["request"]:
            url_obj = item["request"]["url"]

            # Fix raw URL
            if "raw" in url_obj:
                for old, new in placeholder_fixes:
                    url_obj["raw"] = url_obj["raw"].replace(old, new)

            # Fix path array
            if "path" in url_obj:
                for i, segment in enumerate(url_obj["path"]):
                    for old, new in placeholder_fixes:
                        # Extract just the placeholder part
                        if old.startswith("/"):
                            old = old.lstrip("/")
                        if new.startswith("/"):
                            new = new.lstrip("/")

                        # Check each path segment
                        if segment == old or "{" + segment + "}" == old or old.endswith("/" + segment):
                            url_obj["path"][i] = new.split("/")[-1]  # Get last part after slash

        # Recursively process items in folders
        if "item" in item:
            for subitem in item["item"]:
                fix_item(subitem)

    # Process all folders
    for folder in collection["item"]:
        fix_item(folder)

    return collection

def update_collection(file_path):
    """Main function to update the Postman collection"""

    print(f"Reading collection from: {file_path}")

    with open(file_path, 'r', encoding='utf-8') as f:
        collection = json.load(f)

    # Find the "08. Ad Boosts" folder
    boosts_folder = None
    boosts_index = None
    for i, folder in enumerate(collection["item"]):
        if folder.get("name") == "08. Ad Boosts":
            boosts_folder = folder
            boosts_index = i
            break

    if boosts_folder:
        print("Found '08. Ad Boosts' folder")

        # Check if endpoints already exist
        existing_names = [item.get("name", "") for item in boosts_folder["item"]]

        # Add GET /boost/options if it doesn't exist
        if "Get Boost Options" not in existing_names:
            print("Adding GET /boost/options endpoint")
            boosts_folder["item"].insert(0, create_boost_options_endpoint())
        else:
            print("GET /boost/options already exists, skipping")

        # Add POST /boost/calculate if it doesn't exist
        if "Calculate Boost Price" not in existing_names:
            print("Adding POST /boost/calculate endpoint")
            boosts_folder["item"].insert(1, create_boost_calculate_endpoint())
        else:
            print("POST /boost/calculate already exists, skipping")
    else:
        print("WARNING: '08. Ad Boosts' folder not found!")

    # Find the "06. User Manage Adverts (Protected)" folder
    adverts_folder = None
    for folder in collection["item"]:
        if folder.get("name") == "06. User Manage Adverts (Protected)":
            adverts_folder = folder
            break

    if adverts_folder:
        print("Found '06. User Manage Adverts (Protected)' folder")

        # Find and remove duplicate POST /adverts endpoints
        create_adverts = []
        other_items = []

        for item in adverts_folder["item"]:
            if (item.get("request", {}).get("method") == "POST" and
                "adverts" in item.get("request", {}).get("url", {}).get("raw", "")):
                create_adverts.append(item)
            else:
                other_items.append(item)

        print(f"Found {len(create_adverts)} POST /adverts endpoints")

        # Keep only "Create New Advert" or the first one if none match
        keep_item = None
        for item in create_adverts:
            if item.get("name") == "Create New Advert":
                keep_item = item
                break

        if not keep_item and create_adverts:
            keep_item = create_adverts[0]

        if keep_item:
            # Rebuild the items list with duplicates removed
            new_items = []
            found_position = False
            for item in adverts_folder["item"]:
                if item == keep_item and not found_position:
                    new_items.append(item)
                    found_position = True
                elif item not in create_adverts or item == keep_item:
                    new_items.append(item)

            adverts_folder["item"] = new_items
            print(f"Removed {len(create_adverts) - 1} duplicate POST /adverts endpoints")
    else:
        print("WARNING: '06. User Manage Adverts (Protected)' folder not found!")

    # Fix URL placeholders throughout the collection
    print("Fixing URL placeholders...")
    collection = fix_url_placeholders(collection)

    # Write updated collection
    output_path = file_path
    print(f"Writing updated collection to: {output_path}")

    with open(output_path, 'w', encoding='utf-8') as f:
        json.dump(collection, f, indent=4, ensure_ascii=False)

    print("Collection updated successfully!")
    return True

if __name__ == "__main__":
    file_path = "/home/www/laravel/marketplace/postman/Marketplace-API-Complete.postman_collection.json"

    try:
        update_collection(file_path)
        sys.exit(0)
    except Exception as e:
        print(f"ERROR: {e}")
        import traceback
        traceback.print_exc()
        sys.exit(1)
