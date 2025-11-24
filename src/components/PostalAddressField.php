<?php

namespace App\Components;

/**
 * PostalAddressField Component
 * Reusable postal address field with geocoding capability
 *
 * Includes: street_address, city, state, postal_code, country, latitude, longitude
 *
 * Usage:
 * $field = new PostalAddressField();
 * echo $field->render($options);
 */
class PostalAddressField {

    /**
     * Common countries list
     */
    private static $countries = [
        'United States',
        'United Kingdom',
        'Canada',
        'Australia',
        'India',
        'Germany',
        'France',
        'Italy',
        'Spain',
        'China',
        'Japan',
        'South Korea',
        'Brazil',
        'Mexico',
        'Argentina',
        'Chile',
        'Colombia',
        'Netherlands',
        'Belgium',
        'Switzerland',
        'Sweden',
        'Norway',
        'Denmark',
        'Finland',
        'Poland',
        'Austria',
        'Ireland',
        'Portugal',
        'Greece',
        'Czech Republic',
        'Hungary',
        'New Zealand',
        'Singapore',
        'Malaysia',
        'Thailand',
        'Indonesia',
        'Philippines',
        'Vietnam',
        'Pakistan',
        'Bangladesh',
        'Sri Lanka',
        'Nepal',
        'Egypt',
        'Nigeria',
        'Kenya',
        'South Africa',
        'United Arab Emirates',
        'Saudi Arabia',
        'Israel',
        'Turkey',
        'Russia',
        'Ukraine',
        'Romania',
        'Bulgaria'
    ];

    /**
     * Get all countries
     *
     * @return array Countries array
     */
    public static function getCountries() {
        return self::$countries;
    }

    /**
     * Render postal address field
     *
     * @param array $options Options for rendering
     *   - label: string (default: 'Address')
     *   - name_prefix: string (default: '')
     *   - street_address_value: string
     *   - city_value: string
     *   - state_value: string
     *   - postal_code_value: string
     *   - country_value: string
     *   - latitude_value: float
     *   - longitude_value: float
     *   - required: bool (default: false)
     *   - id_prefix: string (default: '')
     *   - show_geocode_button: bool (default: true)
     *   - help_text: string (optional help text below field)
     * @return string HTML output
     */
    public static function render($options = []) {
        // Default options
        $defaults = [
            'label' => 'Address',
            'name_prefix' => '',
            'street_address_value' => '',
            'city_value' => '',
            'state_value' => '',
            'postal_code_value' => '',
            'country_value' => '',
            'latitude_value' => '',
            'longitude_value' => '',
            'required' => false,
            'id_prefix' => '',
            'show_geocode_button' => true,
            'help_text' => ''
        ];

        $opts = array_merge($defaults, $options);

        // Add prefix to names if provided
        $streetAddressName = $opts['name_prefix'] . 'street_address';
        $cityName = $opts['name_prefix'] . 'city';
        $stateName = $opts['name_prefix'] . 'state';
        $postalCodeName = $opts['name_prefix'] . 'postal_code';
        $countryName = $opts['name_prefix'] . 'country';
        $latitudeName = $opts['name_prefix'] . 'latitude';
        $longitudeName = $opts['name_prefix'] . 'longitude';

        // Add prefix to IDs if provided
        $streetAddressId = $opts['id_prefix'] . 'street_address';
        $cityId = $opts['id_prefix'] . 'city';
        $stateId = $opts['id_prefix'] . 'state';
        $postalCodeId = $opts['id_prefix'] . 'postal_code';
        $countryId = $opts['id_prefix'] . 'country';
        $latitudeId = $opts['id_prefix'] . 'latitude';
        $longitudeId = $opts['id_prefix'] . 'longitude';
        $geocodeButtonId = $opts['id_prefix'] . 'geocode_button';

        // Build HTML
        $html = '<div class="form-group">' . "\n";
        $html .= '    <label class="form-label">' . "\n";
        $html .= '        ' . htmlspecialchars($opts['label']);
        if ($opts['required']) {
            $html .= ' <span style="color: var(--danger-color);">*</span>';
        }
        $html .= "\n" . '    </label>' . "\n";

        // Street Address
        $html .= '    <div style="margin-bottom: 0.75rem;">' . "\n";
        $html .= '        <input' . "\n";
        $html .= '            type="text"' . "\n";
        $html .= '            id="' . htmlspecialchars($streetAddressId) . '"' . "\n";
        $html .= '            name="' . htmlspecialchars($streetAddressName) . '"' . "\n";
        $html .= '            class="form-input"' . "\n";
        $html .= '            value="' . htmlspecialchars($opts['street_address_value']) . '"' . "\n";
        $html .= '            placeholder="Street Address"' . "\n";
        if ($opts['required']) {
            $html .= '            required' . "\n";
        }
        $html .= '        >' . "\n";
        $html .= '    </div>' . "\n";

        // City, State, Postal Code row - now using CSS class for responsive grid
        $html .= '    <div class="address-fields-row">' . "\n";

        // City
        $html .= '        <input' . "\n";
        $html .= '            type="text"' . "\n";
        $html .= '            id="' . htmlspecialchars($cityId) . '"' . "\n";
        $html .= '            name="' . htmlspecialchars($cityName) . '"' . "\n";
        $html .= '            class="form-input address-city"' . "\n";
        $html .= '            value="' . htmlspecialchars($opts['city_value']) . '"' . "\n";
        $html .= '            placeholder="City"' . "\n";
        if ($opts['required']) {
            $html .= '            required' . "\n";
        }
        $html .= '        >' . "\n";

        // State
        $html .= '        <input' . "\n";
        $html .= '            type="text"' . "\n";
        $html .= '            id="' . htmlspecialchars($stateId) . '"' . "\n";
        $html .= '            name="' . htmlspecialchars($stateName) . '"' . "\n";
        $html .= '            class="form-input address-state"' . "\n";
        $html .= '            value="' . htmlspecialchars($opts['state_value']) . '"' . "\n";
        $html .= '            placeholder="State"' . "\n";
        $html .= '        >' . "\n";

        // Postal Code
        $html .= '        <input' . "\n";
        $html .= '            type="text"' . "\n";
        $html .= '            id="' . htmlspecialchars($postalCodeId) . '"' . "\n";
        $html .= '            name="' . htmlspecialchars($postalCodeName) . '"' . "\n";
        $html .= '            class="form-input address-postal"' . "\n";
        $html .= '            value="' . htmlspecialchars($opts['postal_code_value']) . '"' . "\n";
        $html .= '            placeholder="Postal Code"' . "\n";
        $html .= '        >' . "\n";

        $html .= '    </div>' . "\n";

        // Country
        $html .= '    <div style="margin-bottom: 0.75rem;">' . "\n";
        $html .= '        <select' . "\n";
        $html .= '            id="' . htmlspecialchars($countryId) . '"' . "\n";
        $html .= '            name="' . htmlspecialchars($countryName) . '"' . "\n";
        $html .= '            class="form-input"' . "\n";
        if ($opts['required']) {
            $html .= '            required' . "\n";
        }
        $html .= '        >' . "\n";
        $html .= '            <option value="">Select Country</option>' . "\n";

        foreach (self::$countries as $country) {
            $selected = ($country === $opts['country_value']) ? ' selected' : '';
            $html .= '            <option value="' . htmlspecialchars($country) . '"' . $selected . '>' . "\n";
            $html .= '                ' . htmlspecialchars($country) . "\n";
            $html .= '            </option>' . "\n";
        }

        $html .= '        </select>' . "\n";
        $html .= '    </div>' . "\n";

        // Geocoding section
        $html .= '    <div class="geocode-section">' . "\n";
        $html .= '        <div class="geocode-header">' . "\n";
        $html .= '            <label class="form-label" style="margin: 0;">Geographic Coordinates</label>' . "\n";

        if ($opts['show_geocode_button']) {
            $html .= '            <button' . "\n";
            $html .= '                type="button"' . "\n";
            $html .= '                id="' . htmlspecialchars($geocodeButtonId) . '"' . "\n";
            $html .= '                onclick="geocodeAddress_' . htmlspecialchars($opts['id_prefix']) . '()"' . "\n";
            $html .= '                class="btn btn-geocode"' . "\n";
            $html .= '            >' . "\n";
            $html .= '                📍 <span class="geocode-btn-text">Update Coordinates</span>' . "\n";
            $html .= '            </button>' . "\n";
        }

        $html .= '        </div>' . "\n";

        // Latitude and Longitude row
        $html .= '        <div class="coordinates-row">' . "\n";

        // Latitude
        $html .= '            <div>' . "\n";
        $html .= '                <label for="' . htmlspecialchars($latitudeId) . '" class="form-label text-small">Latitude' . ($opts['required'] ? ' <span style="color: var(--danger-color);">*</span>' : '') . '</label>' . "\n";
        $html .= '                <input' . "\n";
        $html .= '                    type="number"' . "\n";
        $html .= '                    id="' . htmlspecialchars($latitudeId) . '"' . "\n";
        $html .= '                    name="' . htmlspecialchars($latitudeName) . '"' . "\n";
        $html .= '                    class="form-input"' . "\n";
        $html .= '                    value="' . htmlspecialchars($opts['latitude_value']) . '"' . "\n";
        $html .= '                    placeholder="e.g., 40.7128"' . "\n";
        $html .= '                    step="any"' . "\n";
        $html .= '                    min="-90"' . "\n";
        $html .= '                    max="90"' . "\n";
        if ($opts['required']) {
            $html .= '                    required' . "\n";
        }
        $html .= '                >' . "\n";
        $html .= '            </div>' . "\n";

        // Longitude
        $html .= '            <div>' . "\n";
        $html .= '                <label for="' . htmlspecialchars($longitudeId) . '" class="form-label text-small">Longitude' . ($opts['required'] ? ' <span style="color: var(--danger-color);">*</span>' : '') . '</label>' . "\n";
        $html .= '                <input' . "\n";
        $html .= '                    type="number"' . "\n";
        $html .= '                    id="' . htmlspecialchars($longitudeId) . '"' . "\n";
        $html .= '                    name="' . htmlspecialchars($longitudeName) . '"' . "\n";
        $html .= '                    class="form-input"' . "\n";
        $html .= '                    value="' . htmlspecialchars($opts['longitude_value']) . '"' . "\n";
        $html .= '                    placeholder="e.g., -74.0060"' . "\n";
        $html .= '                    step="any"' . "\n";
        $html .= '                    min="-180"' . "\n";
        $html .= '                    max="180"' . "\n";
        if ($opts['required']) {
            $html .= '                    required' . "\n";
        }
        $html .= '                >' . "\n";
        $html .= '            </div>' . "\n";

        $html .= '        </div>' . "\n";

        // Geocoding status message
        $html .= '        <div id="' . htmlspecialchars($opts['id_prefix']) . 'geocode_status" style="margin-top: 0.5rem; font-size: 0.875rem; color: #666;"></div>' . "\n";

        $html .= '    </div>' . "\n";

        if (!empty($opts['help_text'])) {
            $html .= '    <small class="text-muted">' . htmlspecialchars($opts['help_text']) . '</small>' . "\n";
        }

        $html .= '</div>' . "\n";

        // Add JavaScript for geocoding
        if ($opts['show_geocode_button']) {
            $html .= "\n" . '<script>' . "\n";
            $html .= 'function geocodeAddress_' . htmlspecialchars($opts['id_prefix']) . '() {' . "\n";
            $html .= '    const streetAddress = document.getElementById("' . htmlspecialchars($streetAddressId) . '").value;' . "\n";
            $html .= '    const city = document.getElementById("' . htmlspecialchars($cityId) . '").value;' . "\n";
            $html .= '    const state = document.getElementById("' . htmlspecialchars($stateId) . '").value;' . "\n";
            $html .= '    const postalCode = document.getElementById("' . htmlspecialchars($postalCodeId) . '").value;' . "\n";
            $html .= '    const country = document.getElementById("' . htmlspecialchars($countryId) . '").value;' . "\n";
            $html .= '    const statusDiv = document.getElementById("' . htmlspecialchars($opts['id_prefix']) . 'geocode_status");' . "\n";
            $html .= '    const button = document.getElementById("' . htmlspecialchars($geocodeButtonId) . '");' . "\n";
            $html .= "\n";
            $html .= '    // Validate that we have at least some address information' . "\n";
            $html .= '    if (!streetAddress && !city && !postalCode) {' . "\n";
            $html .= '        statusDiv.style.color = "#f44336";' . "\n";
            $html .= '        statusDiv.textContent = "Please enter at least a street address, city, or postal code.";' . "\n";
            $html .= '        return;' . "\n";
            $html .= '    }' . "\n";
            $html .= "\n";
            $html .= '    // Build full address string' . "\n";
            $html .= '    const addressParts = [streetAddress, city, state, postalCode, country].filter(p => p);' . "\n";
            $html .= '    const fullAddress = addressParts.join(", ");' . "\n";
            $html .= "\n";
            $html .= '    // Update button state' . "\n";
            $html .= '    button.disabled = true;' . "\n";
            $html .= '    button.textContent = "🔄 Geocoding...";' . "\n";
            $html .= '    statusDiv.style.color = "#2196f3";' . "\n";
            $html .= '    statusDiv.textContent = "Looking up coordinates...";' . "\n";
            $html .= "\n";
            $html .= '    // Use Nominatim (OpenStreetMap) geocoding API (free, no API key required)' . "\n";
            $html .= '    const geocodeUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(fullAddress)}&limit=1`;' . "\n";
            $html .= "\n";
            $html .= '    fetch(geocodeUrl, {' . "\n";
            $html .= '        headers: {' . "\n";
            $html .= '            "User-Agent": "PostalAddressField/1.0"' . "\n";
            $html .= '        }' . "\n";
            $html .= '    })' . "\n";
            $html .= '    .then(response => response.json())' . "\n";
            $html .= '    .then(data => {' . "\n";
            $html .= '        if (data && data.length > 0) {' . "\n";
            $html .= '            const lat = parseFloat(data[0].lat);' . "\n";
            $html .= '            const lon = parseFloat(data[0].lon);' . "\n";
            $html .= "\n";
            $html .= '            // Update latitude and longitude fields' . "\n";
            $html .= '            document.getElementById("' . htmlspecialchars($latitudeId) . '").value = lat.toFixed(6);' . "\n";
            $html .= '            document.getElementById("' . htmlspecialchars($longitudeId) . '").value = lon.toFixed(6);' . "\n";
            $html .= "\n";
            $html .= '            statusDiv.style.color = "#4caf50";' . "\n";
            $html .= '            statusDiv.textContent = `✓ Coordinates updated: ${lat.toFixed(6)}, ${lon.toFixed(6)}`;' . "\n";
            $html .= '        } else {' . "\n";
            $html .= '            statusDiv.style.color = "#ff9800";' . "\n";
            $html .= '            statusDiv.textContent = `⚠ Could not find coordinates for: "${fullAddress}". Please enter them manually.`;' . "\n";
            $html .= '        }' . "\n";
            $html .= '    })' . "\n";
            $html .= '    .catch(error => {' . "\n";
            $html .= '        console.error("Geocoding error:", error);' . "\n";
            $html .= '        statusDiv.style.color = "#f44336";' . "\n";
            $html .= '        statusDiv.textContent = "✗ Geocoding failed. Please check your internet connection and try again.";' . "\n";
            $html .= '    })' . "\n";
            $html .= '    .finally(() => {' . "\n";
            $html .= '        button.disabled = false;' . "\n";
            $html .= '        button.textContent = "📍 Update Coordinates from Address";' . "\n";
            $html .= '    });' . "\n";
            $html .= '}' . "\n";
            $html .= '</script>' . "\n";
        }

        return $html;
    }
}
