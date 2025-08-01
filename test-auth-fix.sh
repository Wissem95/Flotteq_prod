#!/bin/bash

# Test script to verify authentication fix

echo "🧪 Testing FlotteQ Authentication Fix"
echo "======================================"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'

# Function to test endpoint
test_endpoint() {
    local endpoint=$1
    local data=$2
    local description=$3
    
    echo -n "Testing $description... "
    
    response=$(curl -s -X POST "http://localhost:8000$endpoint" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d "$data")
    
    if echo "$response" | grep -q "token"; then
        echo -e "${GREEN}✓ PASS${NC}"
        return 0
    else
        echo -e "${RED}✗ FAIL${NC}"
        echo "Response: $response"
        return 1
    fi
}

# Test backend health
echo -n "Testing backend health... "
if curl -s http://localhost:8000/api/health | grep -q "ok"; then
    echo -e "${GREEN}✓ PASS${NC}"
else
    echo -e "${RED}✗ FAIL - Backend not running${NC}"
    exit 1
fi

# Test internal auth (correct route)
test_endpoint "/api/internal/auth/login" '{"email": "admin@flotteq.com", "password": "demo123"}' "Internal Auth (correct route)"

# Test tenant auth
test_endpoint "/api/auth/login" '{"login": "wissemkarboubbb@gmail.com", "password": "demo123"}' "Tenant Auth"

# Test wrong route (should fail)
echo -n "Testing wrong route (should fail)... "
response=$(curl -s -X POST "http://localhost:8000/api/internal/internal/auth/login" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"email": "admin@flotteq.com", "password": "demo123"}')

if echo "$response" | grep -q "not be found\|404"; then
    echo -e "${GREEN}✓ PASS (correctly fails)${NC}"
else
    echo -e "${RED}✗ FAIL (should have failed)${NC}"
    echo "Response: $response"
fi

echo
echo "🎉 Authentication fix verification complete!"