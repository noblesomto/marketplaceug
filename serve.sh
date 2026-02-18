#!/bin/bash

# Kill both processes when script exits (Ctrl+C)
trap 'kill 0' EXIT

echo "Starting PHP server and Vite/Tailwind..."

php artisan serve --port=8030 &
npm run dev &

wait
