<?php

namespace App\Components;

/**
 * PhoneNumberField Component
 * Reusable phone number field with country code selector
 *
 * Usage:
 * $field = new PhoneNumberField();
 * echo $field->render($options);
 */
class PhoneNumberField {

    /**
     * Country codes with flag emojis
     */
    private static $countryCodes = [
        '+1' => '🇺🇸 +1 (USA/Canada)',
        '+44' => '🇬🇧 +44 (UK)',
        '+91' => '🇮🇳 +91 (India)',
        '+86' => '🇨🇳 +86 (China)',
        '+81' => '🇯🇵 +81 (Japan)',
        '+49' => '🇩🇪 +49 (Germany)',
        '+33' => '🇫🇷 +33 (France)',
        '+39' => '🇮🇹 +39 (Italy)',
        '+34' => '🇪🇸 +34 (Spain)',
        '+7' => '🇷🇺 +7 (Russia)',
        '+55' => '🇧🇷 +55 (Brazil)',
        '+61' => '🇦🇺 +61 (Australia)',
        '+27' => '🇿🇦 +27 (South Africa)',
        '+82' => '🇰🇷 +82 (South Korea)',
        '+52' => '🇲🇽 +52 (Mexico)',
        '+62' => '🇮🇩 +62 (Indonesia)',
        '+90' => '🇹🇷 +90 (Turkey)',
        '+31' => '🇳🇱 +31 (Netherlands)',
        '+46' => '🇸🇪 +46 (Sweden)',
        '+47' => '🇳🇴 +47 (Norway)',
        '+45' => '🇩🇰 +45 (Denmark)',
        '+41' => '🇨🇭 +41 (Switzerland)',
        '+43' => '🇦🇹 +43 (Austria)',
        '+32' => '🇧🇪 +32 (Belgium)',
        '+351' => '🇵🇹 +351 (Portugal)',
        '+353' => '🇮🇪 +353 (Ireland)',
        '+48' => '🇵🇱 +48 (Poland)',
        '+420' => '🇨🇿 +420 (Czech Republic)',
        '+36' => '🇭🇺 +36 (Hungary)',
        '+30' => '🇬🇷 +30 (Greece)',
        '+358' => '🇫🇮 +358 (Finland)',
        '+64' => '🇳🇿 +64 (New Zealand)',
        '+65' => '🇸🇬 +65 (Singapore)',
        '+60' => '🇲🇾 +60 (Malaysia)',
        '+66' => '🇹🇭 +66 (Thailand)',
        '+84' => '🇻🇳 +84 (Vietnam)',
        '+63' => '🇵🇭 +63 (Philippines)',
        '+92' => '🇵🇰 +92 (Pakistan)',
        '+880' => '🇧🇩 +880 (Bangladesh)',
        '+94' => '🇱🇰 +94 (Sri Lanka)',
        '+977' => '🇳🇵 +977 (Nepal)',
        '+20' => '🇪🇬 +20 (Egypt)',
        '+234' => '🇳🇬 +234 (Nigeria)',
        '+254' => '🇰🇪 +254 (Kenya)',
        '+971' => '🇦🇪 +971 (UAE)',
        '+966' => '🇸🇦 +966 (Saudi Arabia)',
        '+972' => '🇮🇱 +972 (Israel)',
        '+98' => '🇮🇷 +98 (Iran)',
        '+54' => '🇦🇷 +54 (Argentina)',
        '+56' => '🇨🇱 +56 (Chile)',
        '+57' => '🇨🇴 +57 (Colombia)',
        '+58' => '🇻🇪 +58 (Venezuela)',
        '+51' => '🇵🇪 +51 (Peru)'
    ];

    /**
     * Parse phone number into country code and number
     *
     * @param string|null $fullPhone Full phone number with country code
     * @return array ['country_code' => string, 'phone_number' => string]
     */
    public static function parse($fullPhone) {
        $countryCode = '+91'; // Default to India
        $phoneNumber = '';

        if (!empty($fullPhone)) {
            foreach (array_keys(self::$countryCodes) as $code) {
                if (strpos($fullPhone, $code) === 0) {
                    $countryCode = $code;
                    $phoneNumber = substr($fullPhone, strlen($code));
                    break;
                }
            }
        }

        return [
            'country_code' => $countryCode,
            'phone_number' => $phoneNumber
        ];
    }

    /**
     * Combine country code and phone number
     *
     * @param string $countryCode Country code (e.g., '+91')
     * @param string $phoneNumber Phone number without country code
     * @return string Combined phone number or empty string
     */
    public static function combine($countryCode, $phoneNumber) {
        return !empty($phoneNumber) ? $countryCode . $phoneNumber : '';
    }

    /**
     * Get all country codes
     *
     * @return array Country codes array
     */
    public static function getCountryCodes() {
        return self::$countryCodes;
    }

    /**
     * Render phone number field
     *
     * @param array $options Options for rendering
     *   - label: string (default: 'Phone')
     *   - name_prefix: string (default: '')
     *   - country_code_name: string (default: 'country_code')
     *   - phone_number_name: string (default: 'phone_number')
     *   - value: string (full phone number with country code)
     *   - selected_country_code: string (default: '+91')
     *   - phone_number_value: string (phone number without country code)
     *   - placeholder: string (default: '9876543210')
     *   - help_text: string (optional help text below field)
     *   - required: bool (default: false)
     *   - id_prefix: string (default: '')
     * @return string HTML output
     */
    public static function render($options = []) {
        // Default options
        $defaults = [
            'label' => 'Phone',
            'name_prefix' => '',
            'country_code_name' => 'country_code',
            'phone_number_name' => 'phone_number',
            'value' => null,
            'selected_country_code' => '+91',
            'phone_number_value' => '',
            'placeholder' => '9876543210',
            'help_text' => '',
            'required' => false,
            'id_prefix' => ''
        ];

        $opts = array_merge($defaults, $options);

        // Parse value if provided
        if (!empty($opts['value'])) {
            $parsed = self::parse($opts['value']);
            $opts['selected_country_code'] = $parsed['country_code'];
            $opts['phone_number_value'] = $parsed['phone_number'];
        }

        // Add prefix to names if provided
        $countryCodeName = $opts['name_prefix'] . $opts['country_code_name'];
        $phoneNumberName = $opts['name_prefix'] . $opts['phone_number_name'];

        // Add prefix to IDs if provided
        $countryCodeId = $opts['id_prefix'] . 'country_code';
        $phoneNumberId = $opts['id_prefix'] . 'phone_number';

        // Build HTML
        $html = '<div class="form-group">' . "\n";
        $html .= '    <label for="' . htmlspecialchars($phoneNumberId) . '" class="form-label">' . "\n";
        $html .= '        ' . htmlspecialchars($opts['label']);
        if ($opts['required']) {
            $html .= ' <span style="color: var(--danger-color);">*</span>';
        }
        $html .= "\n" . '    </label>' . "\n";
        $html .= '    <div class="phone-field-wrapper">' . "\n";
        $html .= '        <select' . "\n";
        $html .= '            id="' . htmlspecialchars($countryCodeId) . '"' . "\n";
        $html .= '            name="' . htmlspecialchars($countryCodeName) . '"' . "\n";
        $html .= '        >' . "\n";

        foreach (self::$countryCodes as $code => $label) {
            $selected = ($code === $opts['selected_country_code']) ? ' selected' : '';
            $html .= '            <option value="' . htmlspecialchars($code) . '"' . $selected . '>' . "\n";
            $html .= '                ' . htmlspecialchars($label) . "\n";
            $html .= '            </option>' . "\n";
        }

        $html .= '        </select>' . "\n";
        $html .= '        <input' . "\n";
        $html .= '            type="tel"' . "\n";
        $html .= '            id="' . htmlspecialchars($phoneNumberId) . '"' . "\n";
        $html .= '            name="' . htmlspecialchars($phoneNumberName) . '"' . "\n";
        $html .= '            value="' . htmlspecialchars($opts['phone_number_value']) . '"' . "\n";
        $html .= '            placeholder="' . htmlspecialchars($opts['placeholder']) . '"' . "\n";
        $html .= '            pattern="[0-9]+"' . "\n";
        if ($opts['required']) {
            $html .= '            required' . "\n";
        }
        $html .= '        >' . "\n";
        $html .= '    </div>' . "\n";

        if (!empty($opts['help_text'])) {
            $html .= '    <small class="text-muted">' . htmlspecialchars($opts['help_text']) . '</small>' . "\n";
        }

        $html .= '</div>' . "\n";

        return $html;
    }
}
