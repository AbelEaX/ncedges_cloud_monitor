<?php
/**
 * Theme Configuration
 * 
 * Centralized theme and UI settings.
 * Supports multiple themes and CSS variable management.
 */

return [
    // Default Theme
    'default' => env('APP_THEME', 'dark'),
    
    // Available Themes
    'themes' => [
        'light' => [
            'name' => 'Light',
            'file' => 'resources/themes/light.css',
        ],
        'dark' => [
            'name' => 'Dark',
            'file' => 'resources/themes/dark.css',
        ],
    ],
    
    // Color Palette
    'colors' => [
        // Primary Colors
        'primary' => env('THEME_PRIMARY_COLOR', '#ffc107'),
        'secondary' => env('THEME_SECONDARY_COLOR', '#29b6f6'),
        
        // Status Colors
        'success' => env('THEME_SUCCESS_COLOR', '#66bb6a'),
        'warning' => env('THEME_WARNING_COLOR', '#ffc107'),
        'danger' => env('THEME_DANGER_COLOR', '#ef5350'),
        'info' => env('THEME_INFO_COLOR', '#29b6f6'),
        
        // Neutral Colors
        'light' => env('THEME_LIGHT_COLOR', '#e0e0e0'),
        'dark' => env('THEME_DARK_COLOR', '#1a1a1a'),
        'muted' => env('THEME_MUTED_COLOR', '#a0a0a0'),
    ],
    
    // Dark Mode Specific
    'dark' => [
        'background' => env('DARK_BG_COLOR', '#1a1a1a'),
        'surface' => env('DARK_SURFACE_COLOR', '#282828'),
        'border' => env('DARK_BORDER_COLOR', '#444444'),
        'text' => env('DARK_TEXT_COLOR', '#e0e0e0'),
        'muted' => env('DARK_MUTED_COLOR', '#a0a0a0'),
    ],
    
    // Light Mode Specific
    'light' => [
        'background' => env('LIGHT_BG_COLOR', '#f5f5f5'),
        'surface' => env('LIGHT_SURFACE_COLOR', '#ffffff'),
        'border' => env('LIGHT_BORDER_COLOR', '#e0e0e0'),
        'text' => env('LIGHT_TEXT_COLOR', '#333333'),
        'muted' => env('LIGHT_MUTED_COLOR', '#666666'),
    ],
    
    // Typography
    'typography' => [
        'font_family' => env('THEME_FONT_FAMILY', 'Inter, sans-serif'),
        'base_size' => env('THEME_BASE_SIZE', '12px'),
        'line_height' => env('THEME_LINE_HEIGHT', '1.5'),
    ],
    
    // Layout
    'layout' => [
        'sidebar_width' => env('SIDEBAR_WIDTH', '180px'),
        'header_height' => env('HEADER_HEIGHT', '50px'),
        'container_padding' => env('CONTAINER_PADDING', '15px'),
    ],
    
    // Allow User Customization
    'user_can_change' => env('USER_CAN_CHANGE_THEME', true),
];
