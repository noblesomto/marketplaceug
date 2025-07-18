#!/bin/bash

# FTP Credentials
HOST='ftp.marketplace.ng'
USER='somto@marketplace.ng'
PASS='NL%c?_F46?lH'

# Local folder to upload (this script sits inside jjhome/)
LOCAL_DIR='./'
REMOTE_DIR='/public_html/marketplace/'

# Upload using smart sync (only new/changed files)
lftp -e "
set ssl:verify-certificate no;
open -u $USER,$PASS $HOST;
mirror --reverse \
       --only-newer \
       --exclude-glob .git/** \
       --exclude-glob .gitignore \
       --exclude-glob node_modules/** \
       --exclude-glob vendor/** \
       --exclude-glob .env \
       --exclude-glob deploy.sh \
       --exclude-glob gitpush.sh \
       --exclude-glob storage/** \
       --exclude-glob public/hot \
       --exclude-glob public/uploads/** \
       --exclude-glob public/ckeditor/** \
       --exclude-glob public/backend/** \
       --exclude-glob public/frontend/images/** \
       --verbose \
       $LOCAL_DIR $REMOTE_DIR;
bye
"
