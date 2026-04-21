<?php
/*
Plugin Name: Pickup Days Toggle
Plugin URI: https://github.com/sajidashrafdev/pickup-days-toggle
Description: Toggle pickup days from backend and control Elementor tabs visibility.
Version: 1.1
Author: Sajid Ashraf
Author URI: https://pk.linkedin.com/in/sajidashrafdev
Requires Plugins: woocommerce
*/


if ( ! defined( 'ABSPATH' ) ) exit;

// ===============================
// 1. CREATE SETTINGS MENU
// ===============================
add_action('admin_menu', function() {
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
// 2. SETTINGS PAGE HTML
// ===============================
function pdt_settings_page() {

    if (isset($_POST['pdt_save'])) {
        update_option('pdt_days', $_POST['days'] ?? []);
        echo '<div class="updated"><p>Settings Saved</p></div>';
    }

    $saved_days = get_option('pdt_days', []);
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    ?>

    <div class="wrap">
        <h1>Pickup Days Settings</h1>

        <form method="post">
            <table class="form-table">
                <?php foreach ($days as $day): ?>
                    <tr>
                        <th><?php echo ucfirst($day); ?></th>
                        <td>
                            <input type="checkbox" name="days[]" value="<?php echo $day; ?>"
                                <?php checked(in_array($day, $saved_days)); ?>>
                            Activate
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <p>
                <input type="submit" name="pdt_save" class="button button-primary" value="Save Settings">
            </p>
        </form>

        <hr>

        <p style="margin-top:20px;">
            <strong>Developed by:</strong> Sajid Ashraf<br>
            <strong>Contact:</strong> 
            <a href="https://pk.linkedin.com/in/sajidashrafdev" target="_blank">
                LinkedIn Profile
            </a>
        </p>
    </div>

    <?php
}

// ===============================
// 3. FRONTEND TAB CONTROL
// ===============================
add_action('wp_footer', function() {

    if ( !is_shop() && !is_page() ) return;

    $active_days = get_option('pdt_days', []);
?>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const activeDays = <?php echo json_encode($active_days); ?>;
    const allDays = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

    allDays.forEach(day => {

        const tab = document.getElementById("tab-" + day);

        if (!tab) return;

        if (!activeDays.includes(day)) {
            tab.style.display = "none";
        } else {
            tab.style.display = "block";
        }

    });

    // AUTO OPEN FIRST ACTIVE TAB
    let firstActive = null;

    allDays.forEach(day => {
        if (activeDays.includes(day) && !firstActive) {
            firstActive = document.getElementById("tab-" + day);
        }
    });

    if (firstActive) {
        firstActive.click();
    }

});
</script>
<?php
});
