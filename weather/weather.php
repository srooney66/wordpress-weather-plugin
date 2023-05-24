<?php
/**
 * Plugin Name: Weather
 * Description: Retrieves weather information using the WeatherAPI.com API.
 * Version: 1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Register the shortcode
add_shortcode("weather", "weather_shortcode");

// Enqueue the style.css file
add_action("wp_enqueue_scripts", "weather_enqueue_styles");

function weather_enqueue_styles()
{
    wp_enqueue_style(
        "weather-style",
        plugin_dir_url(__FILE__) . "assets/css/style.css"
    );
}

// Callback function to render the settings page content
function weather_settings_page_content() {
    ?>
    <div class="wrap">
        <h1>Weather Settings</h1>
        <form method="post" action="options.php">
            <?php
            // Output the settings fields
            settings_fields('weather_settings');
            do_settings_sections('weather_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register the settings page
add_action("admin_menu", "weather_settings_page");

function weather_settings_page()
{
    add_options_page(
        "Weather Settings",
        "Weather",
        "manage_options",
        "weather-settings",
        "weather_settings_page_content"
    );

    add_action("admin_init", "weather_settings_init");
}

// Register settings and sections
function weather_settings_init() {
    // Register the settings
    register_setting('weather_settings', 'weather_api_key');
    register_setting('weather_settings', 'weather_background_color');
    register_setting('weather_settings', 'weather_text_color');
    register_setting('weather_settings', 'weather_hide_fields');
	register_setting('weather_settings', 'weather_id_field');
	register_setting('weather_settings', 'weather_class_field');

    // Add General section to the settings page
    add_settings_section(
        'weather_general_section',
        'General Settings',
        'weather_section_callback',
        'weather_settings'
    );
    // Add fields to the general section
    add_settings_field(
        'weather_api_key',
        'API Key',
        'weather_api_key_callback',
        'weather_settings',
        'weather_general_section'
    );
    add_settings_field(
        'weather_hide_fields',
        'Hide Fields',
        'weather_hide_fields_callback',
        'weather_settings',
        'weather_general_section'
    );
	
	// Add CSS section to the settings page
    add_settings_section(
        'weather_css_section',
        'CSS Settings',
        'weather_css_section_callback',
        'weather_settings'
    );
    // Add fields to the css section
	add_settings_field(
        'weather_id_field',
        'ID',
        'weather_id_field_callback',
        'weather_settings',
        'weather_css_section'
    );
	add_settings_field(
        'weather_class_field',
        'Custom Class',
        'weather_class_field_callback',
        'weather_settings',
        'weather_css_section'
    );
    add_settings_field(
        'weather_background_color',
        'Background Color',
        'weather_background_color_callback',
        'weather_settings',
        'weather_css_section'
    );
    add_settings_field(
        'weather_text_color',
        'Text Color',
        'weather_text_color_callback',
        'weather_settings',
        'weather_css_section'
    );
	
	// Add shortcode explanation and example section to the settings page
    add_settings_section(
        'weather_shortcode_section',
        'Shortcode Examples',
        'weather_shortcode_section_callback',
        'weather_settings'
    );
}

function weather_section_callback()
{
    echo "Configure the Weather plugin settings below:";
}

function weather_api_key_callback()
{
    $api_key = esc_html(get_option("weather_api_key"));
    echo "<input type='text' name='weather_api_key' value='$api_key' />";
}

function weather_hide_fields_callback()
{
    $hide_fields = get_option("weather_hide_fields");
    // if not found, is set to FALSE so update to empty array for upcoming in_array fcn
    if (!$hide_fields) { 
        $hide_fields = [];
    }
    $hide_location = in_array("location", $hide_fields) ? "checked" : "";
    $hide_temperature = in_array("temperature", $hide_fields) ? "checked" : "";
    $hide_datetime = in_array("datetime", $hide_fields) ? "checked" : "";
    $hide_wind = in_array("wind", $hide_fields) ? "checked" : "";
    $hide_pressure = in_array("pressure", $hide_fields) ? "checked" : "";
    $hide_visibility = in_array("visibility", $hide_fields) ? "checked" : "";

    echo "
        <label>
            <input type='checkbox' name='weather_hide_fields[]' value='location' $hide_location /> Location
        </label><br />
        <label>
            <input type='checkbox' name='weather_hide_fields[]' value='temperature' $hide_temperature /> Temperature</label><br />
        <label>
            <input type='checkbox' name='weather_hide_fields[]' value='datetime' $hide_datetime /> Date and Time
        </label><br />
        <label>
            <input type='checkbox' name='weather_hide_fields[]' value='wind' $hide_wind /> Wind
        </label><br />
        <label>
            <input type='checkbox' name='weather_hide_fields[]' value='pressure' $hide_pressure /> Pressure
        </label><br />
        <label>
            <input type='checkbox' name='weather_hide_fields[]' value='visibility' $hide_visibility /> Visibility
        </label>
    ";
}

// css section settings callback functions
function weather_css_section_callback()
{
    echo "Configure the Weather plugin CSS settings:";
}

function weather_id_field_callback()
{
    $weather_id_field = esc_html(get_option("weather_id_field"));
    echo "<input type='text' name='weather_id_field' value='$weather_id_field' />";
}

function weather_class_field_callback()
{
    $weather_class_field = esc_html(get_option("weather_class_field"));
    echo "<input type='text' name='weather_class_field' value='$weather_class_field' />";
}

function weather_background_color_callback()
{
    $background_color = esc_html(get_option("weather_background_color"));
    echo "<input type='color' name='weather_background_color' value='$background_color' />";
}

function weather_text_color_callback()
{
    $text_color = esc_html(get_option("weather_text_color"));
    echo "<input type='color' name='weather_text_color' value='$text_color' />";
}

function weather_shortcode_section_callback()
{
    echo "<div class='docs'><p>The shortcode <code>[weather]</code> allows you to display weather information for a specific location on your WordPress site. It has the following parameters:</p>
<ol>
<li>
<p><code>query</code> (required): Specifies the location for which you want to display the weather information. You can use a city name, ZIP code, or coordinates. Example: <code>[weather query=\"New York\"]</code></p>
</li>
<li>
<p><code>backgroundcolor</code> (optional): Specifies the background color of the weather box. Use a valid CSS color value. Example: <code>[weather query=\"London\" backgroundcolor=\"#eaeaea\"]</code></p>
</li>
<li>
<p><code>textcolor</code> (optional): Specifies the text color of the weather box. Use a valid CSS color value. Example: <code>[weather query=\"Paris\" textcolor=\"#333333\"]</code></p>
</li>
</ol>
<p>Note: If <code>backgroundcolor</code> or <code>textcolor</code> attributes are not provided, the shortcode will use the default values defined in the plugin settings.</p>
<p>Example usage with all parameters:</p>
<code>[weather query=\"Sydney\"</span> backgroundcolor=\"#f5f5f5\" textcolor=\"#333333\"]
</code>
<p>Make sure to replace \"Sydney\" with the desired location, and adjust the background and text colors as per your preference.</p>
<p>Remember to save the changes and preview/update the page or post to see the weather information displayed.</p></div>";
}

// Shortcode callback function
function weather_shortcode($atts)
{
    // Extract shortcode attributes
    $atts = shortcode_atts(
        [
            "query" => "",
			"backgroundcolor" => "",
			"textcolor" => ""
        ],
        $atts
    );

    // Check if the query parameter is provided
    if (empty($atts["query"])) {
        return "<p class='weather-sc-error'>Please provide a valid query parameter.</p>";
    }

    // Get plugin settings
    $api_key = esc_html(get_option("weather_api_key"));
    $background_color = esc_html(get_option("weather_background_color"));
    $text_color = esc_html(get_option("weather_text_color"));
    $hide_fields = get_option("weather_hide_fields", []);
    
	$id = esc_html(get_option("weather_id_field"));
	$class = esc_html(get_option("weather_class_field"));

    // Format the query parameter
    $query = urlencode($atts["query"]);

    // WeatherAPI.com API endpoint & key
    $api_url =
        "https://api.weatherapi.com/v1/current.json?key=" .
        $api_key .
        "&q=" .
        $query;

    // Make the API request
    $response = wp_remote_get($api_url);

    // Check for API request errors
    if (is_wp_error($response)) {
        return "<p class='weather-sc-error'>Error retrieving weather data.</p>";
    }

    // Parse the API response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check if the API response is valid
    if (empty($data) || isset($data["error"])) {
        return "<p class='weather-sc-error'>Invalid API response.</p>";
    }

    // Extract the weather information
    $location = $data["location"]["name"] . ", " . $data["location"]["country"];
    $datetime = $data["location"]["localtime"];
    $temperature = $data["current"]["temp_c"] . "°C";
    $wind = $data["current"]["wind_kph"] . " kph";
    $pressure = $data["current"]["pressure_mb"] . " mb";
    $visibility = $data["current"]["vis_km"] . " km";
	
    // override default background color if passed in as shortcode attribute
	if (!empty($atts["backgroundcolor"])) {
        $background_color = $atts["backgroundcolor"];
    }

    // override default text color if passed in as shortcode attribute
	if (!empty($atts["textcolor"])) {
        $text_color = $atts["textcolor"];
    }

    // Format the output
    $output = "<div ".($id ? 'id="'.$id.'"' : '')." class='location-box ".($class ? $class : '')."' style='background-color: $background_color; color: $text_color;'>";
    
    if( empty($hide_fields) ){
    	$hide_fields = array();
    }

    if (!in_array("location", $hide_fields)) {
        $output .= "<div class='location'>$location</div>";
    }

    if (!in_array("temperature", $hide_fields)) {
        $output .= "<div class='temperature'>$temperature</div>";
    }

    if (!in_array("datetime", $hide_fields)) {
        $output .= "<div class='date'><strong>Date and Time:</strong> $datetime</div>";
    }

    if (!in_array("wind", $hide_fields)) {
        $output .= "<div class='wind'><strong>Wind:</strong> $wind</div>";
    }

    if (!in_array("pressure", $hide_fields)) {
        $output .= "<div class='pressure'><strong>Pressure:</strong> $pressure</div>";
    }

    if (!in_array("visibility", $hide_fields)) {
        $output .= "<div class='visibility'><strong>Visibility:</strong> $visibility</div>";
    }
    $output .= "</div>";

    return $output;
}