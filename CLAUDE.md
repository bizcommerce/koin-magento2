# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Module Overview

This is a Magento 2 payment module (`Koin_Payment`) that integrates Koin payment services. The module implements three payment methods: Credit Card (with transparent checkout), PIX (QR code), and Boleto Parcelado (installment boleto).

## Commands

### Installation & Setup
```bash
# After making changes to PHP classes or DI configuration
php bin/magento setup:di:compile

# After changes to module version or database schema
php bin/magento setup:upgrade

# After changes to frontend assets or templates
php bin/magento setup:static-content:deploy pt_BR en_US

# Clear cache after configuration changes
php bin/magento cache:clean
php bin/magento cache:flush
```

### Development Commands
```bash
# Run unit tests for this module (from Magento root)
./vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Koin/Payment/Test/Unit/

# Check module status
php bin/magento module:status Koin_Payment

# Enable/disable module
php bin/magento module:enable Koin_Payment
php bin/magento module:disable Koin_Payment

# Reindex after payment configuration changes
php bin/magento indexer:reindex
```

## Architecture

### Payment Flow
1. **Checkout**: Customer selects payment method (Credit Card/PIX/Boleto)
2. **Order Creation**: Payment information is captured and sent to Koin API
3. **Callback Processing**: Koin sends webhooks to `koin/notifications/index` endpoint
4. **Status Updates**: Order status is updated based on payment confirmation
5. **Antifraud**: Credit card payments include fraud analysis via cron job

### Key Components

**Payment Methods**:
- `Model/Payment/Cc.php`: Credit card implementation with transparent checkout
- `Model/Payment/Pix.php`: PIX payment with QR code generation
- `Model/Payment/Redirect.php`: Boleto Parcelado with redirect flow

**API Integration**:
- `Gateway/Http/Client.php`: HTTP client for Koin API communication
- `Gateway/Request/*Handler.php`: Request builders for various API calls
- `Gateway/Response/*Handler.php`: Response processors
- JWT authentication using Firebase JWT library

**Data Flow**:
- **Callbacks**: Stored in `koin_callback` table, processed via admin grid
- **Antifraud Queue**: `koin_antifraud_queue` table, processed by cron
- **API Requests**: Logged in `koin_request` table for debugging

**Frontend Components**:
- `view/frontend/web/js/view/payment/method-renderer/`: Payment method renderers
- `view/frontend/web/template/payment/`: Knockout.js templates
- Fingerprint.js integration for fraud prevention

### Database Tables
- `koin_callback`: Webhook notifications from Koin
- `koin_antifraud_queue`: Pending antifraud analyses
- `koin_request`: API request/response logs
- `koin_seller`: Seller configurations (multi-seller support)

### Configuration Structure
Admin configuration at: Stores > Configuration > Sales > Payment Methods > Koin
- API credentials (Environment, Token, Private Key)
- Payment method specific settings (enabled, title, instructions)
- Installment rules and interest rates
- Antifraud settings (enabled, analyze amount, failure action)

### Important Files
- `etc/config.xml`: Default configuration values
- `etc/payment.xml`: Payment method definitions
- `etc/adminhtml/system.xml`: Admin configuration fields
- `etc/di.xml`: Dependency injection configuration
- `Gateway/Config/Config.php`: Configuration reader