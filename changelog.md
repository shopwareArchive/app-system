# Changelog
All notable changes to this project will be documented in this file.

## 0.2.0

**Additions/Changes**
- Added app install and uninstall commands
- Moved hmac signature to header 
- Added signature to every outgoing request
- If there is an error during App installation the app will be removed, so that it can be easily reinstalled
- Added shop url to confirmation request and requests for loading iframes
- Renamed `shop` header to `shop-url` in registration request

## 0.1.0

**Initial Release**
