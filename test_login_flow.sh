#!/bin/bash

# Login Flow Automated Test Script
# Tests various authentication scenarios

echo "=========================================="
echo "  LOGIN FLOW AUTOMATED TEST"
echo "=========================================="
echo ""

BASE_URL="http://localhost:8000"
TEST_EMAIL="test@example.com"
TEST_PASSWORD="password123"

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test counter
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# Function to run a test
run_test() {
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    TEST_NAME=$1
    echo -n "Testing: $TEST_NAME ... "
}

# Function to mark test as passed
pass_test() {
    PASSED_TESTS=$((PASSED_TESTS + 1))
    echo -e "${GREEN}✓ PASSED${NC}"
}

# Function to mark test as failed
fail_test() {
    FAILED_TESTS=$((FAILED_TESTS + 1))
    echo -e "${RED}✗ FAILED${NC}"
    if [ -n "$1" ]; then
        echo "  Error: $1"
    fi
}

# Function to mark test as warning
warn_test() {
    echo -e "${YELLOW}⚠ WARNING${NC}"
    if [ -n "$1" ]; then
        echo "  $1"
    fi
}

echo "1. CHECKING CONFIGURATION"
echo "=========================================="

# Test 1: Check if .env exists
run_test ".env file exists"
if [ -f .env ]; then
    pass_test
else
    fail_test ".env file not found"
fi

# Test 2: Check database connection
run_test "Database connection"
if php artisan db:show > /dev/null 2>&1; then
    pass_test
else
    fail_test "Cannot connect to database"
fi

# Test 3: Check if users table exists
run_test "Users table exists"
TABLE_CHECK=$(php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasTable('users') ? 'yes' : 'no';" 2>/dev/null | grep -o 'yes\|no')
if [ "$TABLE_CHECK" = "yes" ]; then
    pass_test
else
    fail_test "Users table does not exist"
fi

echo ""
echo "2. CHECKING REQUIRED COLUMNS"
echo "=========================================="

# Test 4: Check for google_id column
run_test "google_id column exists"
GOOGLE_ID_CHECK=$(php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasColumn('users', 'google_id') ? 'yes' : 'no';" 2>/dev/null | grep -o 'yes\|no')
if [ "$GOOGLE_ID_CHECK" = "yes" ]; then
    pass_test
else
    warn_test "Missing google_id column - Social login may fail"
fi

# Test 5: Check for facebook_id column
run_test "facebook_id column exists"
FACEBOOK_ID_CHECK=$(php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasColumn('users', 'facebook_id') ? 'yes' : 'no';" 2>/dev/null | grep -o 'yes\|no')
if [ "$FACEBOOK_ID_CHECK" = "yes" ]; then
    pass_test
else
    warn_test "Missing facebook_id column - Social login may fail"
fi

# Test 6: Check for otp_expires_at column
run_test "otp_expires_at column exists"
OTP_EXPIRES_CHECK=$(php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasColumn('users', 'otp_expires_at') ? 'yes' : 'no';" 2>/dev/null | grep -o 'yes\|no')
if [ "$OTP_EXPIRES_CHECK" = "yes" ]; then
    pass_test
else
    fail_test "Missing otp_expires_at column - OTP expiration won't work"
fi

# Test 7: Check for trusted_devices table
run_test "trusted_devices table exists"
TRUSTED_DEVICES_CHECK=$(php artisan tinker --execute="echo \Illuminate\Support\Facades\Schema::hasTable('trusted_devices') ? 'yes' : 'no';" 2>/dev/null | grep -o 'yes\|no')
if [ "$TRUSTED_DEVICES_CHECK" = "yes" ]; then
    pass_test
else
    fail_test "Missing trusted_devices table - Remember device won't work"
fi

echo ""
echo "3. CHECKING SECURITY CONFIGURATION"
echo "=========================================="

# Test 8: Check if CSRF is enabled
run_test "CSRF middleware enabled"
if grep -q "VerifyCsrfToken" app/Http/Kernel.php; then
    pass_test
else
    fail_test "CSRF middleware not found"
fi

# Test 9: Check session configuration
run_test "Session driver configured"
SESSION_DRIVER=$(grep "SESSION_DRIVER=" .env | cut -d '=' -f2)
if [ -n "$SESSION_DRIVER" ]; then
    pass_test
    echo "  Using: $SESSION_DRIVER"
else
    warn_test "Session driver not set in .env"
fi

# Test 10: Check mail configuration
run_test "Mail configuration exists"
MAIL_MAILER=$(grep "MAIL_MAILER=" .env | cut -d '=' -f2)
if [ -n "$MAIL_MAILER" ]; then
    pass_test
    echo "  Using: $MAIL_MAILER"
else
    fail_test "Mail configuration missing - OTP emails won't work"
fi

echo ""
echo "4. CHECKING ROUTES"
echo "=========================================="

# Test 11: Check login route
run_test "Login route exists"
if php artisan route:list | grep -q "login"; then
    pass_test
else
    fail_test "Login route not found"
fi

# Test 12: Check OTP route
run_test "OTP authentication route exists"
if php artisan route:list | grep -q "authenticate"; then
    pass_test
else
    fail_test "OTP authentication route not found"
fi

# Test 13: Check social auth routes
run_test "Social auth routes exist"
if php artisan route:list | grep -q "auth/google"; then
    pass_test
else
    warn_test "Google auth route not found"
fi

echo ""
echo "5. CHECKING MIDDLEWARE"
echo "=========================================="

# Test 14: Check AutoLoginFromCookie middleware
run_test "AutoLoginFromCookie middleware exists"
if [ -f app/Http/Middleware/AutoLoginFromCookie.php ]; then
    pass_test
else
    fail_test "AutoLoginFromCookie middleware missing"
fi

# Test 15: Check CheckUserSession middleware
run_test "CheckUserSession middleware exists"
if [ -f app/Http/Middleware/CheckUserSession.php ]; then
    pass_test
else
    fail_test "CheckUserSession middleware missing"
fi

echo ""
echo "6. CHECKING CODE QUALITY"
echo "=========================================="

# Test 16: Check for hardcoded secure flag
run_test "Dynamic secure flag (not hardcoded)"
if grep -q "true,  // secure" app/Http/Middleware/CheckUserSession.php; then
    warn_test "Hardcoded secure flag found - may fail on HTTP"
else
    pass_test
fi

# Test 17: Check password minimum length
run_test "Password minimum length (should be 8+)"
MIN_LENGTH=$(grep "password.*required.*min:" app/Http/Controllers/AccountController.php | head -1 | grep -oP 'min:\K\d+')
if [ "$MIN_LENGTH" -ge 8 ]; then
    pass_test
    echo "  Minimum: $MIN_LENGTH characters"
else
    warn_test "Password min length is $MIN_LENGTH (recommended: 8+)"
fi

# Test 18: Check for rate limiting
run_test "Login rate limiting configured"
if grep -A 2 "login.*AccountController" routes/web.php | grep -q "throttle"; then
    pass_test
else
    warn_test "No rate limiting on login route"
fi

echo ""
echo "=========================================="
echo "  TEST SUMMARY"
echo "=========================================="
echo -e "Total Tests:  $TOTAL_TESTS"
echo -e "${GREEN}Passed:       $PASSED_TESTS${NC}"
echo -e "${RED}Failed:       $FAILED_TESTS${NC}"
echo -e "Warnings:     $((TOTAL_TESTS - PASSED_TESTS - FAILED_TESTS))"
echo ""

# Calculate score
SCORE=$((PASSED_TESTS * 100 / TOTAL_TESTS))
echo -e "Score: ${SCORE}%"

if [ $SCORE -ge 90 ]; then
    echo -e "${GREEN}Status: EXCELLENT ✓${NC}"
elif [ $SCORE -ge 75 ]; then
    echo -e "${YELLOW}Status: GOOD (minor improvements needed)${NC}"
elif [ $SCORE -ge 60 ]; then
    echo -e "${YELLOW}Status: FAIR (several improvements needed)${NC}"
else
    echo -e "${RED}Status: NEEDS WORK${NC}"
fi

echo ""
echo "=========================================="
echo ""

exit $FAILED_TESTS
