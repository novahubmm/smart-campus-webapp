#!/bin/bash

# Script to generate one-time invoices for all students
# This will dispatch jobs for all active one-time fees

echo "=========================================="
echo "Generate One-Time Invoices for All Students"
echo "=========================================="
echo ""

# Run the artisan command
php artisan payment:generate-one-time-invoices

echo ""
echo "=========================================="
echo "Jobs dispatched! Check the logs for progress."
echo "=========================================="
