#!/bin/bash

# Test Price Filter Fix
# This script tests both API and Web filter endpoints

BASE_URL="http://localhost"
API_URL="${BASE_URL}/api/search/filter"
WEB_URL="${BASE_URL}/filter/adverts"

echo "=================================="
echo "Testing Price Filter Functionality"
echo "=================================="
echo ""

# First, let's get some sample adverts to see the price range
echo "1. Getting sample adverts to understand price range..."
curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"per_page": 5}' | jq -r '.data[] | "ID: \(.id) - \(.ad_title) - Price: \(.price)"' 2>/dev/null || echo "Could not parse response"
echo ""
echo "=================================="
echo ""

# Test 1: Filter with valid min and max prices
echo "2. Test API: Valid min (10000) and max (500000) prices"
RESPONSE=$(curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "min": 10000,
    "max": 500000,
    "per_page": 5
  }')

echo "Response success: $(echo $RESPONSE | jq -r '.success')"
echo "Total results: $(echo $RESPONSE | jq -r '.pagination.total')"
echo "Sample prices:"
echo "$RESPONSE" | jq -r '.data[] | "  - \(.ad_title): ₦\(.price)"' 2>/dev/null | head -5
echo ""
echo "=================================="
echo ""

# Test 2: Filter with EMPTY min and max (should return all results, not filter by price)
echo "3. Test API: Empty min and max strings (should NOT filter)"
RESPONSE=$(curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "min": "",
    "max": "",
    "per_page": 5
  }')

echo "Response success: $(echo $RESPONSE | jq -r '.success')"
echo "Total results: $(echo $RESPONSE | jq -r '.pagination.total')"
echo "Sample prices (should include all price ranges):"
echo "$RESPONSE" | jq -r '.data[] | "  - \(.ad_title): ₦\(.price)"' 2>/dev/null | head -5
echo ""
echo "=================================="
echo ""

# Test 3: Filter with only min price
echo "4. Test API: Only min price (100000)"
RESPONSE=$(curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "min": 100000,
    "per_page": 5
  }')

echo "Response success: $(echo $RESPONSE | jq -r '.success')"
echo "Total results: $(echo $RESPONSE | jq -r '.pagination.total')"
echo "Sample prices (all should be >= 100000):"
echo "$RESPONSE" | jq -r '.data[] | "  - \(.ad_title): ₦\(.price)"' 2>/dev/null | head -5
echo ""
echo "=================================="
echo ""

# Test 4: Filter with only max price
echo "5. Test API: Only max price (50000)"
RESPONSE=$(curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "max": 50000,
    "per_page": 5
  }')

echo "Response success: $(echo $RESPONSE | jq -r '.success')"
echo "Total results: $(echo $RESPONSE | jq -r '.pagination.total')"
echo "Sample prices (all should be <= 50000):"
echo "$RESPONSE" | jq -r '.data[] | "  - \(.ad_title): ₦\(.price)"' 2>/dev/null | head -5
echo ""
echo "=================================="
echo ""

# Test 5: No price parameters
echo "6. Test API: No price parameters (should return all)"
RESPONSE=$(curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "per_page": 5
  }')

echo "Response success: $(echo $RESPONSE | jq -r '.success')"
echo "Total results: $(echo $RESPONSE | jq -r '.pagination.total')"
echo ""
echo "=================================="
echo ""

# Test 6: Test with category filter + price
echo "7. Test API: Category filter + price range"
RESPONSE=$(curl -s -X POST "$API_URL" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "category": 1,
    "min": 20000,
    "max": 200000,
    "per_page": 5
  }')

echo "Response success: $(echo $RESPONSE | jq -r '.success')"
echo "Total results: $(echo $RESPONSE | jq -r '.pagination.total')"
echo "Sample results with category filter:"
echo "$RESPONSE" | jq -r '.data[] | "  - \(.ad_title): ₦\(.price) (Cat: \(.category))"' 2>/dev/null | head -5
echo ""
echo "=================================="
echo ""

# Test 7: Test car filter endpoint with prices
echo "8. Test API: Car filter with price range"
RESPONSE=$(curl -s -X POST "${BASE_URL}/api/search/filter-by-car" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "min": 500000,
    "max": 5000000,
    "per_page": 5
  }')

echo "Response success: $(echo $RESPONSE | jq -r '.success')"
echo "Total results: $(echo $RESPONSE | jq -r '.pagination.total')"
echo "Sample car prices:"
echo "$RESPONSE" | jq -r '.data[] | "  - \(.ad_title): ₦\(.price)"' 2>/dev/null | head -5
echo ""
echo "=================================="
echo ""

echo "✅ All tests completed!"
echo ""
echo "EXPECTED RESULTS:"
echo "- Test 2 (empty strings) should return same total as Test 5 (no params)"
echo "- Test 3 prices should all be >= 100,000"
echo "- Test 4 prices should all be <= 50,000"
echo "- No errors or unexpected 0 results from empty filters"
