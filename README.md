# Pickup Days & Locations Toggle (v2.0)

A powerful yet lightweight WordPress plugin to manage pickup schedules, toggle Elementor tabs visibility, and display day-specific locations using shortcodes.

## ✨ New Features

- **7-Day Location Management:** Add specific pickup locations/addresses for each day of the week.
- **Dynamic Shortcodes:** 7 unique shortcodes (`[location_monday]`, etc.) to display locations anywhere on your site.
- **Enhanced Elementor Support:** Fully compatible with both standard Elementor Tabs and the new **Elementor Nested Tabs** (Containers).
- **Auto-Tab Switching:** Automatically detects the current day and activates the corresponding tab for the user.

## 🚀 How it Works

1. Enable/Disable specific days from the backend.
2. Enter a location/address for each active day.
3. The plugin will:
   - **Hide** tabs for disabled days.
   - **Auto-select** the current day's tab on page load.
   - **Display** the correct location wherever you use the shortcode.

## 🛠 Shortcodes

You can use the following shortcodes to display the location for a specific day:

| Day | Shortcode |
| :--- | :--- |
| **Monday** | `[location_monday]` |
| **Tuesday** | `[location_tuesday]` |
| **Wednesday** | `[location_wednesday]` |
| **Thursday** | `[location_thursday]` |
| **Friday** | `[location_friday]` |
| **Saturday** | `[location_saturday]` |
| **Sunday** | `[location_sunday]` |

## 🔧 Elementor Integration

To ensure the plugin controls your tabs correctly:

1. Open your page in Elementor.
2. For each tab, go to **Advanced > CSS ID**.
3. Use the following IDs: `tab-monday`, `tab-tuesday`, `tab-wednesday`, `tab-thursday`, `tab-friday`, `tab-saturday`, `tab-sunday`.
4. *Note: Even without manual IDs, the plugin will attempt to target tabs by their index (1-7) automatically.*

## 📋 Installation

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin via the WordPress 'Plugins' menu.
3. Navigate to the **Pickup Days** menu in your sidebar.
4. Configure your active days and enter locations in the provided input fields.

## 📌 Requirements

- **WordPress**
- **WooCommerce** (Required for shop logic)
- **Elementor** (For Tabs UI control)

## 👤 Author

**Sajid Ashraf** *WordPress & Shopify Developer*

- **LinkedIn:** [sajidashrafdev](https://pk.linkedin.com/in/sajidashrafdev)
- **Website:** [sajidashraf.me](https://sajidashraf.me)

## 🔗 GitHub Repository

[https://github.com/sajidashrafdev/pickup-days-toggle](https://github.com/sajidashrafdev/pickup-days-toggle)
