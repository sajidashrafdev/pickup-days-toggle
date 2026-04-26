# Pickup Days & Locations Toggle (v3.1)

A powerful yet lightweight WordPress plugin to manage pickup schedules, control Elementor tabs visibility, and enhance ordering UX with smart cutoff logic.

---

## ✨ New Features (v3.1)

* **⏰ Cutoff Time Logic**

  * Automatically switches to the **next available day** after cutoff time
  * Prevents users from ordering for expired days

* **🇮🇪 Ireland Timezone Support**

  * All time-based logic runs on **Europe/Dublin timezone**
  * Ensures accurate behavior regardless of server location

* **💬 Dynamic Pickup Message (NEW)**

  * Admin-controlled messages:

    * Before cutoff
    * After cutoff
  * Display anywhere using shortcode:

    ```
    [pickup_message]
    ```

* **📅 7-Day Location Management**

  * Set pickup locations for each day of the week

* **🔄 Auto Tab Switching**

  * Automatically activates the correct day tab on page load

* **👁 Smart Tab Visibility**

  * Hides disabled days in Elementor Tabs / Nested Tabs

* **🔗 URL-Based Navigation**

  * Supports query parameter:

    ```
    /menu/?day=monday
    ```

---

## 🚀 How it Works

1. Enable/disable days from the admin panel
2. Set pickup locations for each active day
3. Configure cutoff time (e.g. 10:00 AM)
4. Add pickup message shortcode where needed

### The plugin will automatically:

* Hide inactive days
* Switch to next valid day after cutoff
* Activate correct Elementor tab
* Display correct location via shortcode
* Show dynamic pickup message

---

## 🛠 Shortcodes

### 📍 Location Shortcodes

| Day       | Shortcode              |
| --------- | ---------------------- |
| Monday    | `[location_monday]`    |
| Tuesday   | `[location_tuesday]`   |
| Wednesday | `[location_wednesday]` |
| Thursday  | `[location_thursday]`  |
| Friday    | `[location_friday]`    |
| Saturday  | `[location_saturday]`  |
| Sunday    | `[location_sunday]`    |

---

### 💬 Pickup Message Shortcode

```
[pickup_message]
```

Displays dynamic message based on:

* Ireland time
* Admin-defined cutoff

---

## ⚙️ Admin Settings

Navigate to **Pickup Days** in WordPress dashboard.

### Available Options:

* Enable / Disable days
* Set pickup locations
* Set cutoff time
* Customize:

  * Message before cutoff
  * Message after cutoff

---

## 🔧 Elementor Integration

To ensure proper tab control:

1. Open page in Elementor
2. Edit each tab
3. Go to **Advanced → CSS ID**
4. Assign:

```
tab-monday
tab-tuesday
tab-wednesday
tab-thursday
tab-friday
tab-saturday
tab-sunday
```

> ✅ Plugin also supports automatic detection via tab index (1–7)

---

## 🧠 Example Behavior

| Scenario                         | Result                   |
| -------------------------------- | ------------------------ |
| Thursday 9 AM                    | Shows Thursday menu      |
| Thursday 11 PM                   | Shows Friday menu        |
| Friday disabled                  | Shows next active day    |
| URL `?day=thursday` after cutoff | Auto-switch to valid day |

---

## 📦 Installation

1. Upload plugin to `/wp-content/plugins/`
2. Activate from WordPress Plugins menu
3. Go to **Pickup Days**
4. Configure settings

---

## 📌 Requirements

* WordPress
* Elementor (Required for tab control)
* WooCommerce (Optional, for shop integration)

---

## 👤 Author

**Sajid Ashraf**
WordPress & Shopify Developer

* LinkedIn: https://pk.linkedin.com/in/sajidashrafdev
* Website: https://sajidashraf.me

---

## 🔗 GitHub Repository

https://github.com/sajidashrafdev/pickup-days-toggle

---

## ⭐ Future Improvements

* Countdown timer before cutoff
* Review slider integration
* Product size variations (small/large)
* Klaviyo email integration

---
