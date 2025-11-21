<?php

namespace App\Components;

use App\Classes\OrganizationRepository;

/**
 * OrganizationField Component
 * Reusable read-only field for organization selection
 * Displays organization's full name as label and id as value
 *
 * Usage:
 * $field = new OrganizationField();
 * echo $field->render($options);
 */
class OrganizationField {

    /**
     * Render organization field (read-only)
     *
     * @param array $options Options for rendering
     *   - label: string (default: 'Organization')
     *   - name: string (default: 'organization_id')
     *   - organization_id: int (required - organization ID)
     *   - organization_name: string (required - organization full name)
     *   - help_text: string (optional help text below field)
     *   - required: bool (default: true)
     *   - id: string (default: 'organization_id')
     * @return string HTML output
     */
    public static function render($options = []) {
        // Default options
        $defaults = [
            'label' => 'Organization',
            'name' => 'organization_id',
            'organization_id' => null,
            'organization_name' => null,
            'help_text' => '',
            'required' => true,
            'id' => 'organization_id'
        ];

        $opts = array_merge($defaults, $options);

        // Validate required fields
        if (empty($opts['organization_id']) || empty($opts['organization_name'])) {
            throw new \Exception("OrganizationField requires both 'organization_id' and 'organization_name' options");
        }

        // Build HTML
        $html = '<div style="margin-bottom: 1.5rem;">' . "\n";
        $html .= '    <label for="' . htmlspecialchars($opts['id']) . '" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">' . "\n";
        $html .= '        ' . htmlspecialchars($opts['label']);
        if ($opts['required']) {
            $html .= ' <span style="color: #f44336;">*</span>';
        }
        $html .= "\n" . '    </label>' . "\n";

        // Hidden input for organization_id
        $html .= '    <input' . "\n";
        $html .= '        type="hidden"' . "\n";
        $html .= '        id="' . htmlspecialchars($opts['id']) . '"' . "\n";
        $html .= '        name="' . htmlspecialchars($opts['name']) . '"' . "\n";
        $html .= '        value="' . htmlspecialchars($opts['organization_id']) . '"' . "\n";
        if ($opts['required']) {
            $html .= '        required' . "\n";
        }
        $html .= '    >' . "\n";

        // Display field with organization name (read-only)
        $html .= '    <input' . "\n";
        $html .= '        type="text"' . "\n";
        $html .= '        style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-color); border-radius: 4px; font-size: 1rem; background-color: #f5f5f5; color: #666;"' . "\n";
        $html .= '        value="' . htmlspecialchars($opts['organization_name']) . '"' . "\n";
        $html .= '        readonly' . "\n";
        $html .= '        tabindex="-1"' . "\n";
        $html .= '    >' . "\n";

        if (!empty($opts['help_text'])) {
            $html .= '    <small class="text-muted">' . htmlspecialchars($opts['help_text']) . '</small>' . "\n";
        }

        $html .= '</div>' . "\n";

        return $html;
    }
}
