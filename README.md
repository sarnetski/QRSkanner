# QR-System Professional

QR-System Professional is a full-featured WordPress plugin for managing QR codes. It allows administrators to create codes, group them, view scan history and gather usage statistics while remaining compatible with older versions of the project.

## Features

- Scan QR codes directly in the browser using the device camera
- Manual code entry as a fallback
- Organize codes into groups with colour labels
- Track scan history and export reports
- Dashboard with statistics about total scans and active codes
- Automated archiving and data cleanup
- Support for "special" codes that only privileged users can scan
- Legacy shortcodes and AJAX endpoints for backward compatibility

## Requirements

- **WordPress:** version 5.0 or newer
- **PHP:** version 7.4 or newer

## Installation

1. Upload the folder `qr-system-professional` to the `wp-content/plugins/` directory or use the **Add Plugin** option in the WordPress dashboard to upload the ZIP package.
2. Navigate to **Plugins → Installed Plugins** in WordPress.
3. Locate **QR-System Professional** and click **Activate**.
4. Upon activation the plugin creates necessary database tables and a `Scanning` page with the `[qr_scanner]` shortcode.

## Usage

- Visit the automatically created `/skanowanie/` page or place the `[qr_scanner]` shortcode on any page to display the scanning interface.
- Use the `[qr_user_locations]` shortcode to show the logged-in user's configured locations in a dropdown list.
- Administrators can manage codes, groups and statistics from the **QR-System Pro** menu in the WordPress admin panel.

## Shortcodes Summary

| Shortcode | Description |
|-----------|-------------|
| `[qr_scanner]` | Displays the QR scanner interface (requires login) |
| `[qr_user_locations]` | Shows a select box with locations from the user profile |

## License

This project is distributed under the terms of the GNU General Public License v3. See the [LICENSE](LICENSE) file for details.

