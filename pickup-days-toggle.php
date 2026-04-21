<?php
/*
Plugin Name: Pickup Days Toggle & 7-Day Locations
Plugin URI: https://github.com/sajidashrafdev/pickup-days-toggle
Description: Toggle pickup days, add locations for each day, and use specific shortcodes like [location_monday].
Version: 1.8
Author: Sajid Ashraf
Author URI: https://pk.linkedin.com/in/sajidashrafdev
Requires Plugins: woocommerce
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// ===============================
// CHECK WOOCOMMERCE
// ===============================
add_action('admin_init', function () {
    if ( ! class_exists('WooCommerce') ) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p><strong>Pickup Days Toggle:</strong> WooCommerce is required.</p></div>';
        });
    }
});

// ===============================
// ADMIN MENU
// ===============================
add_action('admin_menu', function () {
    add_menu_page(
        'Pickup Days Settings',
        'Pickup Days',
        'manage_options',
        'pickup-days-settings',
        'pdt_settings_page',
        'dashicons-calendar',
        25
    );
});

// ===============================
// SETTINGS PAGE (Backend)
// ===============================
function pdt_settings_page() {
    if (isset($_POST['pdt_save'])) {
        update_option('pdt_config', $_POST['pdt_config'] ?? []);
        echo '<div class="updated"><p>Settings Saved Successfully!</p></div>';
    }

    $config = get_option('pdt_config', []);
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    ?>

    <div class="wrap">
        <h1>Pickup Days & 7-Day Locations</h1>
        <form method="post" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <table class="form-table">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid #eee;">
                        <th style="padding-bottom: 15px;">Day</th>
                        <th style="padding-bottom: 15px;">Active Status</th>
                        <th style="padding-bottom: 15px;">Location (Shortcode)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($days as $day): 
                    $is_active = isset($config[$day]['active']) ? 'checked' : '';
                    $location_val = $config[$day]['location'] ?? '';
                ?>
                    <tr style="border-bottom: 1px solid #f9f9f9;">
                        <th><strong><?php echo ucfirst($day); ?></strong></th>
                        <td>
                            <input type="checkbox" name="pdt_config[<?php echo $day; ?>][active]" value="1" <?php echo $is_active; ?>> Active
                        </td>
                        <td>
                            <input type="text" name="pdt_config[<?php echo $day; ?>][location]" 
                                   value="<?php echo esc_attr($location_val); ?>" 
                                   class="regular-text" placeholder="Enter location">
                            <br><small>Shortcode: <code>[location_<?php echo $day; ?>]</code></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><input type="submit" name="pdt_save" class="button button-primary" value="Save Settings"></p>
        </form>
    </div>
    <?php
}

// ===============================
// REGISTER 7 SHORTCODES (One for each day)
// ===============================
add_action('init', function() {
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    
    foreach ($days as $day) {
        add_shortcode('location_' . $day, function() use ($day) {
            $config = get_option('pdt_config', []);
            
            // Agar day active hai aur location mojood hai
            if ( !empty($config[$day]['active']) && !empty($config[$day]['location']) ) {
                return '<div class="pdt-location-' . $day . '">' . esc_html($config[$day]['location']) . '</div>';
            }
            return ''; // Kuch nahi dikhayega agar inactive ho
        });
    }
});

// ===============================
// FRONTEND LOGIC (JS)
// ===============================
add_action('wp_footer', function () {
    if ( is_admin() ) return;
    $config = get_option('pdt_config', []);
    $active_days = [];
    foreach ($config as $day => $data) {
        if (!empty($data['active'])) { $active_days[] = $day; }
    }
?>
<script>
(function () {
    const activeDays = <?php echo json_encode($active_days); ?>;
    const allDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    const dayMap = { 1: "monday", 2: "tuesday", 3: "wednesday", 4: "thursday", 5: "friday", 6: "saturday", 0: "sunday" };

    function getToday() { return dayMap[new Date().getDay()]; }

    function getValidDay() {
        let today = getToday();
        if (activeDays.includes(today)) return today;
        for (let d of allDays) { if (activeDays.includes(d)) return d; }
        return null;
    }

    function hideInactiveTabs() {
        allDays.forEach(day => {
            let tab = document.getElementById("tab-" + day);
            if (tab && !activeDays.includes(day)) { tab.style.display = "none"; }
        });
    }

    function activateTab(day) {
        if (!day) return;
        let el = document.getElementById("tab-" + day);
        if (el) el.click();
    }

    window.addEventListener("load", function () {
        if (activeDays.length === 0) {
            document.querySelectorAll('[id^="tab-"]').forEach(el => el.style.display = "none");
            return;
        }

        let i = 0;
        let t = setInterval(() => {
            if (document.getElementById("tab-monday") || i > 20) {
                clearInterval(t);
                hideInactiveTabs();
                let params = new URLSearchParams(window.location.search);
                let dayParam = params.get("day");
                activateTab((dayParam && activeDays.includes(dayParam)) ? dayParam : getValidDay());
            }
            i++;
        }, 300);

        let btnWrap = document.getElementById("today-menu-btn");
        if (btnWrap) {
            let a = btnWrap.querySelector("a");
            let valid = getValidDay();
            if (a && valid) { a.href = "/menu/?day=" + valid; }
            else { btnWrap.style.display = "none"; }
        }
    });
})();
</script>
<?php
});
