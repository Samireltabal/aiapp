#!/bin/bash

# Generate self-signed SSL certificate for local development
# This script creates a self-signed SSL certificate valid for 365 days

echo "Generating self-signed SSL certificate..."

openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout key.pem \
    -out cert.pem \
    -subj "/C=US/ST=State/L=City/O=Organization/OU=Development/CN=localhost"

echo "SSL certificate generated successfully!"
echo "Certificate: cert.pem"
echo "Private Key: key.pem"
