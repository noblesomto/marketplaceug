#!/bin/bash

# FTP Credentials
HOST='ftp.marketplace.ng'
USER='somto@marketplace.ng'
PASS='NL%c?_F46?lH'

# Local to remote folder mapping
declare -A FOLDERS=(
  ["./"]="public_html/marketplace/"
  ["./public/frontend/"]="public_html/frontend/"
  ["./public/backend/"]="public_html/backend/"
  ["./public/build/"]="public_html/build/"
)

# Excludes (folders/files to ignore)
EXCLUDES=(
  ".git/"
  ".gitignore"
  "node_modules/"
  "vendor/"
  ".env"
  "deploy.sh"
  "gitpush.sh"
  "storage/"
  "bootstrap/"
  "public/hot"
  "public/uploads/"
  "public/ckeditor/"
)

# Create a dynamic exclude string for lftp and rsync
EXCLUDE_ARGS=""
for pattern in "${EXCLUDES[@]}"; do
  EXCLUDE_ARGS+=" --exclude-glob $pattern"
done

# Function to check for changes using rsync
check_changes() {
    local LOCAL_DIR=$1
    CHANGES=$(rsync -av --dry-run $LOCAL_DIR /tmp/deploy_check $RSYNC_EXCLUDES | grep -v '/$' | wc -l)
    echo $CHANGES
}

# Build RSYNC_EXCLUDES for rsync
RSYNC_EXCLUDES=""
for pattern in "${EXCLUDES[@]}"; do
    RSYNC_EXCLUDES+=" --exclude=$pattern"
done

# Function to upload folder if changes exist
upload_if_changed() {
    local LOCAL_DIR=$1
    local REMOTE_DIR=$2
    local NAME=$3

    CHANGED=$(rsync -av --dry-run $RSYNC_EXCLUDES "$LOCAL_DIR" "/tmp/deploy_check" | grep -v '/$' | wc -l)

    if [ "$CHANGED" -gt 0 ]; then
        echo "🔄 Changes detected in $NAME ($CHANGED files). Uploading..."
        lftp -e "
        set ssl:verify-certificate no;
        open -u $USER,$PASS $HOST;
        mirror --reverse \
               --only-newer \
               $EXCLUDE_ARGS \
               --verbose \
               $LOCAL_DIR $REMOTE_DIR;
        bye
        "
    else
        echo "✅ No changes detected in $NAME. Skipping upload."
    fi
}

# Main loop through all folders
for LOCAL_DIR in "${!FOLDERS[@]}"; do
  REMOTE_DIR=${FOLDERS[$LOCAL_DIR]}
  NAME=$(basename "$REMOTE_DIR")
  upload_if_changed "$LOCAL_DIR" "$REMOTE_DIR" "$NAME"
done
