<?php

/**
 * HTML Generator Class
 * Provides reusable UI components and eliminates repetitive HTML generation
 * Follows DRY principles for consistent admin interface elements
 */
class HtmlGenerator {

    /**
     * =============================================================================
     * STATISTICS AND DASHBOARD COMPONENTS
     * =============================================================================
     */

    /**
     * Generate statistics card HTML
     */
    public static function renderStatCard($title, $number, $subtitle = '', $icon = 'fas fa-info', $css_class = '') {
        $html = '<div class="admin-stat-card ' . htmlspecialchars($css_class) . '">';
        $html .= '<div class="stat-icon"><i class="' . htmlspecialchars($icon) . '"></i></div>';
        $html .= '<div class="stat-content">';
        $html .= '<h3>' . htmlspecialchars($title) . '</h3>';
        $html .= '<div class="stat-number">' . htmlspecialchars($number) . '</div>';
        
        if (!empty($subtitle)) {
            $html .= '<small>' . htmlspecialchars($subtitle) . '</small>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * =============================================================================
     * ACTION BUTTONS AND INTERACTIVE ELEMENTS
     * =============================================================================
     */

    /**
     * Generate action button HTML
     */
    public static function renderActionButton($type, $id, $title, $onclick = '', $additional_classes = '') {
        $icons = [
            'view' => 'fas fa-eye',
            'edit' => 'fas fa-edit',
            'delete' => 'fas fa-trash',
            'toggle-on' => 'fas fa-toggle-on',
            'toggle-off' => 'fas fa-toggle-off'
        ];

        $icon = $icons[$type] ?? 'fas fa-cog';
        $class = "btn-action btn-{$type} {$additional_classes}";
        
        $html = '<a href="#" class="' . htmlspecialchars(trim($class)) . '" ';
        
        if (!empty($onclick)) {
            $html .= 'onclick="' . htmlspecialchars($onclick) . '; return false;" ';
        }
        
        $html .= 'title="' . htmlspecialchars($title) . '">';
        $html .= '<i class="' . htmlspecialchars($icon) . '"></i>';
        $html .= '</a>';
        
        return $html;
    }

    /**
     * Generate action buttons container
     */
    public static function renderActionButtons($buttons) {
        $html = '<div class="action-buttons">';
        
        foreach ($buttons as $button) {
            $html .= self::renderActionButton(
                $button['type'],
                $button['id'] ?? '',
                $button['title'] ?? '',
                $button['onclick'] ?? '',
                $button['class'] ?? ''
            );
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate status badge HTML
     */
    public static function renderStatusBadge($status, $custom_class = '') {
        require_once 'TableConfig.php';
        
        $style_class = TableConfig::getStatusStyle($status);
        $class = "badge bg-{$style_class} {$custom_class}";
        
        return '<span class="' . htmlspecialchars(trim($class)) . '">' . 
               htmlspecialchars(ucfirst($status)) . '</span>';
    }

    /**
     * Generate status dropdown for orders
     */
    public static function renderStatusDropdown($current_status, $order_id, $options = []) {
        $default_options = [
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'preparing' => 'Preparing',
            'ready' => 'Ready',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled'
        ];

        $status_options = !empty($options) ? $options : $default_options;
        $style_class = TableConfig::getStatusStyle($current_status);
        
        $html = '<select class="form-select form-select-sm order-status-dropdown bg-' . $style_class . ' text-white border-0" ';
        $html .= 'data-order-id="' . intval($order_id) . '" ';
        $html .= 'data-current-status="' . htmlspecialchars($current_status) . '" ';
        $html .= 'style="max-width: 180px; font-weight: 500;">';
        
        foreach ($status_options as $value => $label) {
            $selected = ($current_status === $value) ? 'selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '" ' . $selected . '>';
            $html .= htmlspecialchars($label);
            $html .= '</option>';
        }
        
        $html .= '</select>';
        
        return $html;
    }

    /**
     * Generate boolean display (Yes/No)
     */
    public static function renderBoolean($value, $true_text = 'Yes', $false_text = 'No') {
        $is_true = ($value == 1 || $value === 'true' || $value === true);
        $class = $is_true ? 'true' : 'false';
        $text = $is_true ? $true_text : $false_text;
        
        return '<span class="cell-boolean ' . $class . '">' . htmlspecialchars($text) . '</span>';
    }

    /**
     * Generate image cell HTML
     */
    public static function renderImageCell($image_path, $alt_text = 'Image', $placeholder_path = '') {
        if (empty($image_path)) {
            $image_path = $placeholder_path ?: '../assets/images/placeholder.jpg';
        }
        
        // Ensure proper path format
        if (strpos($image_path, '../') !== 0 && strpos($image_path, 'http') !== 0) {
            $image_path = '../' . ltrim($image_path, '/');
        }
        
        return '<img src="' . htmlspecialchars($image_path) . '" ' .
               'alt="' . htmlspecialchars($alt_text) . '" class="cell-image">';
    }

    /**
     * Generate price display
     */
    public static function renderPrice($amount, $currency = '$') {
        $formatted_amount = number_format((float)$amount, 2);
        return '<span class="cell-price">' . htmlspecialchars($currency . $formatted_amount) . '</span>';
    }

    /**
     * Generate date display
     */
    public static function renderDate($date, $format = 'M d, Y') {
        if (empty($date)) {
            return '<span class="cell-date text-muted">-</span>';
        }
        
        $formatted_date = date($format, strtotime($date));
        return '<span class="cell-date">' . htmlspecialchars($formatted_date) . '</span>';
    }

    /**
     * Generate datetime display
     */
    public static function renderDateTime($datetime, $format = 'M d, Y g:i A') {
        if (empty($datetime)) {
            return '<span class="cell-date text-muted">-</span>';
        }
        
        $formatted_datetime = date($format, strtotime($datetime));
        return '<span class="cell-date">' . htmlspecialchars($formatted_datetime) . '</span>';
    }

    /**
     * Generate email link
     */
    public static function renderEmail($email) {
        if (empty($email)) {
            return '<span class="text-muted">-</span>';
        }
        
        return '<a href="mailto:' . htmlspecialchars($email) . '" class="cell-email">' . 
               htmlspecialchars($email) . '</a>';
    }

    /**
     * Generate phone link
     */
    public static function renderPhone($phone) {
        if (empty($phone)) {
            return '<span class="text-muted">-</span>';
        }
        
        return '<a href="tel:' . htmlspecialchars($phone) . '" class="cell-phone">' . 
               htmlspecialchars($phone) . '</a>';
    }

    /**
     * Generate URL link
     */
    public static function renderUrl($url, $max_length = 30) {
        if (empty($url)) {
            return '<span class="text-muted">-</span>';
        }
        
        $display_url = strlen($url) > $max_length ? substr($url, 0, $max_length) . '...' : $url;
        return '<a href="' . htmlspecialchars($url) . '" target="_blank" class="cell-url">' . 
               htmlspecialchars($display_url) . '</a>';
    }

    /**
     * Generate text with truncation
     */
    public static function renderText($text, $max_length = 100) {
        if (empty($text)) {
            return '<span class="text-muted">-</span>';
        }
        
        $display_text = strlen($text) > $max_length ? substr($text, 0, $max_length) . '...' : $text;
        return '<span class="cell-text" title="' . htmlspecialchars($text) . '">' . 
               htmlspecialchars($display_text) . '</span>';
    }

    /**
     * Generate preparation time display
     */
    public static function renderPrepTime($minutes) {
        if (empty($minutes)) {
            return '<span class="text-muted">-</span>';
        }
        
        return '<span class="cell-prep-time">' . intval($minutes) . ' min</span>';
    }

    /**
     * Generate empty state HTML
     */
    public static function renderEmptyState($icon, $title, $message) {
        $html = '<div class="admin-empty-state">';
        $html .= '<i class="' . htmlspecialchars($icon) . '"></i>';
        $html .= '<h3>' . htmlspecialchars($title) . '</h3>';
        $html .= '<p>' . htmlspecialchars($message) . '</p>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate table header
     */
    public static function renderTableHeader($columns) {
        $html = '<thead><tr>';
        
        foreach ($columns as $column) {
            $html .= '<th>' . htmlspecialchars($column['label']) . '</th>';
        }
        
        $html .= '<th>Actions</th>';
        $html .= '</tr></thead>';
        
        return $html;
    }

    /**
     * Generate form input field
     */
    public static function renderFormField($type, $name, $value = '', $attributes = []) {
        $default_attributes = [
            'class' => 'form-control',
            'id' => $name
        ];
        
        $merged_attributes = array_merge($default_attributes, $attributes);
        $attr_string = '';
        
        foreach ($merged_attributes as $attr => $val) {
            $attr_string .= ' ' . htmlspecialchars($attr) . '="' . htmlspecialchars($val) . '"';
        }
        
        switch ($type) {
            case 'textarea':
                return '<textarea name="' . htmlspecialchars($name) . '"' . $attr_string . '>' . 
                       htmlspecialchars($value) . '</textarea>';
                       
            case 'select':
                $options = $attributes['options'] ?? [];
                $html = '<select name="' . htmlspecialchars($name) . '"' . $attr_string . '>';
                
                foreach ($options as $option_value => $option_label) {
                    $selected = ($value == $option_value) ? ' selected' : '';
                    $html .= '<option value="' . htmlspecialchars($option_value) . '"' . $selected . '>';
                    $html .= htmlspecialchars($option_label);
                    $html .= '</option>';
                }
                
                $html .= '</select>';
                return $html;
                
            default:
                return '<input type="' . htmlspecialchars($type) . '" name="' . htmlspecialchars($name) . 
                       '" value="' . htmlspecialchars($value) . '"' . $attr_string . '>';
        }
    }

    /**
     * Generate alert/notification HTML
     */
    public static function renderAlert($message, $type = 'info', $dismissible = true) {
        $html = '<div class="alert alert-' . htmlspecialchars($type);
        
        if ($dismissible) {
            $html .= ' alert-dismissible';
        }
        
        $html .= '" role="alert">';
        $html .= htmlspecialchars($message);
        
        if ($dismissible) {
            $html .= '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Generate pagination HTML
     */
    public static function renderPagination($current_page, $total_pages, $base_url) {
        if ($total_pages <= 1) {
            return '';
        }
        
        $html = '<nav aria-label="Table pagination">';
        $html .= '<ul class="pagination justify-content-center">';
        
        // Previous button
        $prev_disabled = ($current_page <= 1) ? ' disabled' : '';
        $prev_page = max(1, $current_page - 1);
        $html .= '<li class="page-item' . $prev_disabled . '">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($base_url . '?page=' . $prev_page) . '">Previous</a>';
        $html .= '</li>';
        
        // Page numbers
        $start = max(1, $current_page - 2);
        $end = min($total_pages, $current_page + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $current_page) ? ' active' : '';
            $html .= '<li class="page-item' . $active . '">';
            $html .= '<a class="page-link" href="' . htmlspecialchars($base_url . '?page=' . $i) . '">' . $i . '</a>';
            $html .= '</li>';
        }
        
        // Next button
        $next_disabled = ($current_page >= $total_pages) ? ' disabled' : '';
        $next_page = min($total_pages, $current_page + 1);
        $html .= '<li class="page-item' . $next_disabled . '">';
        $html .= '<a class="page-link" href="' . htmlspecialchars($base_url . '?page=' . $next_page) . '">Next</a>';
        $html .= '</li>';
        
        $html .= '</ul>';
        $html .= '</nav>';
        
        return $html;
    }
}

?>