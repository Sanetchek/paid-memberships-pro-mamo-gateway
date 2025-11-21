# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [4.0.0] - 2024-12-XX

### Added
- Complete MAMO payment gateway implementation
  - MAMO Payment Links support (standalone, modal, inline)
  - Webhook integration for real-time payment notifications
  - Native subscription support via MAMO API
  - MIT (Merchant Initiated Transactions) support for recurring payments
  - Refund functionality via MAMO API
- Advanced admin tools
  - MAMO Tools admin page under Memberships settings
  - Manual cron execution button with AJAX feedback
  - Connection test with detailed status information
- Refund functionality
  - Refund button integration in PMPro orders list
  - AJAX refund endpoint
  - Refund processing via MAMO API
  - Refund status updates
  - JavaScript UI integration
- WooCommerce compatibility
  - Custom order tables compatibility declaration
  - WooCommerce feature compatibility support
- Enhanced documentation
  - Comprehensive README with installation guide
  - API documentation references
  - Logs directory documentation

### Changed
- Improved error handling throughout the codebase
- Enhanced security measures
- Code quality improvements and PSR-12 compliance
- Better user feedback and error messages

### Fixed
- Discount code calculation edge cases
- Order status synchronization improvements
- Webhook processing reliability

---

## [3.0.0] - 2024-11-XX

### Added
- Webhook integration system
  - Webhook endpoint with secret validation
  - JSON payload parsing and validation
  - Event handling for:
    - `charge.succeeded` - Successful payment processing
    - `charge.failed` - Failed payment handling
    - `charge.refunded` - Refund event processing
  - Order lookup by external_id (order code)
  - Order lookup by charge ID
  - Automatic order status synchronization
  - User membership level updates on payment
  - Transaction ID storage in order metadata
  - Real-time payment status updates
  - Membership activation on successful payment
- Recurring payments system
  - Native MAMO subscription support
  - Subscription object creation with frequency mapping
  - Support for daily, weekly, monthly, yearly frequencies
  - Frequency interval configuration
  - Subscription start and end date handling
  - Card saving for recurring payments (`save_card` parameter)
  - Merchant Initiated Transactions (MIT) support
  - Saved card token management
  - Card verification handling
- Automated cron job system
  - `MamoCron` service class for recurring payment processing
  - WordPress cron scheduling with daily execution
  - Configurable execution time (default: 02:30)
  - User meta tracking (`_mamo_next_due`, `_mamo_retry_count`)
  - Retry mechanism with configurable max attempts (default: 3)
  - Failed payment handling and retry logic
  - Manual cron execution via admin interface
- Logging and debugging system
  - `MamoLogger` utility class
  - Log file creation in `/logs/` directory
  - Daily log file rotation with date-based naming
  - Log level support (info, warning, error)
  - Sensitive data redaction (API keys, tokens)
  - Context data logging
  - Configurable logging toggle in settings
- Admin interface enhancements
  - Connection test functionality with AJAX
  - API credentials validation
  - Test mode detection and feedback
  - Connection status display with error messages
  - Debug logging toggle in settings

### Changed
- Improved subscription handling logic
- Enhanced error logging and debugging capabilities
- Better webhook security validation

### Fixed
- Subscription frequency mapping edge cases
- Cron job scheduling reliability
- Webhook secret validation improvements

---

## [2.0.0] - 2024-10-XX

### Added
- Payment link creation system
  - Payment link generation via MAMO API
  - Standalone redirect mode implementation
  - Modal (IFRAME) popup mode implementation
  - Inline integration support
  - Payment link parameters mapping:
    - Product name and description
    - Amount calculation with discounts
    - Customer information (name, email)
    - Return URLs (success/error)
    - Invoice line items
  - Payment link ID storage in order metadata
- Order processing system
  - Order creation and validation
  - Order code generation
  - Order status management (pending → success/failed)
  - Order total calculation with discount codes
  - Zero-amount order handling
  - Free level detection and handling
  - Order metadata storage (`_mamo_payment_link_id`, `_mamo_charge_id`)
- Payment flow implementation
  - Checkout form integration
  - Payment method selection
  - Redirect to MAMO payment page
  - Popup/iframe payment flow
  - Payment confirmation handling
  - Error handling and user feedback
- Frontend integration
  - Checkout form modifications
  - Hide native credit card fields for MAMO
  - Required billing fields customization
  - Form validation
  - Discount code integration
  - Level selection handling
- Popup/Modal implementation
  - Bootstrap modal integration
  - Modal CSS styling
  - JavaScript for popup handling (`pmpro-mamo.js`)
  - AJAX payment link generation for popup
  - Iframe rendering endpoint
  - Payment flow in popup
  - Popup close handling
  - Success/error redirect handling
- AJAX endpoints
  - `pmpro_mamo_get_redirect` - Popup payment link generation
  - `pmpro_mamo_iframe` - Iframe rendering
- Advanced payment features
  - Discount code support with proper amount calculation
  - Trial level tracking
  - User meta storage for trial levels (`pmpro_trial_level_used`)
  - Trial level detection
  - Membership level management
  - Level change handling
  - Recurring payment cancellation on level change
  - User meta cleanup on level cancellation

### Changed
- Improved payment flow user experience
- Enhanced error handling in payment processing
- Better integration with PMPro checkout system

### Fixed
- Discount calculation issues
- Order total calculation with discounts
- Free level handling

---

## [1.0.0] - 2024-09-XX

### Added
- Initial plugin foundation
  - Plugin header and metadata configuration
  - Plugin directory constants and paths
  - WordPress plugin activation hooks
  - PMPro dependency checking and loading
  - Namespace structure (`PMPro\Mamo`)
  - Autoloading and file structure organization
- Basic gateway integration
  - PMPro gateway class extension (`PMProGateway_mamo`)
  - Gateway registration with PMPro
  - Gateway options registration
  - Payment gateway settings page integration
  - Gateway environment detection (sandbox/production)
- API communication layer
  - `MamoApi` service class creation
  - API credential management (test/live keys)
  - HTTP request handling with WordPress HTTP API
  - JSON response parsing and error handling
  - API endpoint URL construction
  - Bearer token authentication
  - Response code mapping and error translation
- Basic admin interface
  - Gateway settings integration in PMPro
  - Live API key input field
  - Sandbox/test API key input field
  - Webhook secret display and management
  - Webhook URL display for MAMO dashboard
  - Display mode selector (Popup/Redirect)
  - Environment selector integration
- Security foundation
  - Webhook secret validation
  - Nonce verification for AJAX requests
  - Capability checks (`manage_options`)
  - Input sanitization
  - Output escaping
  - SQL injection prevention
  - XSS protection
  - CSRF protection
- Code architecture
  - Separation of concerns (Services, Adapters, Utils)
  - PSR-12 code style compliance
  - Object-oriented architecture
  - Interface-based design (`PaymentProviderInterface`)
  - Adapter pattern implementation (`MamoAdapter`)
  - Namespace organization
- WordPress standards compliance
  - WordPress coding standards
  - Proper use of WordPress hooks
  - WordPress HTTP API usage
  - WordPress cron integration
  - WordPress user meta API
  - WordPress options API

### Technical Details
- **Namespace**: `PMPro\Mamo`
- **Service Classes**:
  - `MamoApi` - API communication and credential management
  - `MamoCron` - Recurring payment processing (added in v3.0.0)
  - `MamoAdmin` - Admin interface and tools (added in v4.0.0)
- **Adapter Classes**:
  - `PaymentProviderInterface` - Payment provider abstraction
  - `MamoAdapter` - MAMO-specific implementation
- **Utility Classes**:
  - `MamoLogger` - Logging with data redaction (added in v3.0.0)

### Requirements
- WordPress 5.0+
- Paid Memberships Pro plugin
- PHP 7.4+
- cURL extension

---

## Development Timeline

### Phase 1: Foundation (v1.0.0)
- Core infrastructure setup
- Basic gateway integration
- API communication layer
- Security foundation

### Phase 2: Payment Processing (v2.0.0)
- Payment link creation
- Order processing system
- Frontend integration
- Popup/modal implementation

### Phase 3: Advanced Features (v3.0.0)
- Webhook integration
- Recurring payments
- Cron job system
- Logging system

### Phase 4: Tools & Enhancement (v4.0.0)
- Admin tools enhancement
- Refund functionality
- Documentation completion

---

## Upgrade Path

- **Fresh Install**: Follow standard installation procedure
- **Existing Install**: Update plugin files and configure MAMO API credentials

---

## Security

All versions include:
- Webhook secret validation
- Nonce verification for AJAX requests
- Capability checks for admin functions
- Sensitive data redaction in logs
- Input sanitization and validation
- SQL injection prevention
- XSS protection
- CSRF protection

---

## Statistics

- **Total Development Time**: ~93 hours (average estimate)
- **Total Features**: 150+ individual features
- **Service Classes**: 3
- **Adapter Classes**: 1
- **Utility Classes**: 2
- **AJAX Endpoints**: 7
- **Admin Pages**: 1
- **Payment Modes**: 3 (Standalone, Modal, Inline)
- **Webhook Events**: 3+ (charge.succeeded, charge.failed, charge.refunded)
