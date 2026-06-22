<?php

namespace App\Infrastructure\Logging;

/**
 * Theme Service
 * 
 * Centralized theme management.
 * Handles theme selection, CSS generation, and user preferences.
 * Supports dark and light modes with customizable color schemes.
 */
class ThemeService
{
    /**
     * Theme configuration
     * 
     * @var array
     */
    protected array $config;
    
    /**
     * Current theme
     * 
     * @var string
     */
    protected string $currentTheme;
    
    /**
     * Constructor
     * 
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->currentTheme = $this->detectTheme();
    }
    
    /**
     * Detect current theme from user preference or system default
     * 
     * @return string
     */
    protected function detectTheme(): string
    {
        // If users are not allowed to change the theme, force the default
        if (!($this->config['user_can_change'] ?? true)) {
            return $this->config['default'] ?? 'dark';
        }
        // Check user preference in session
        if (isset($_SESSION['theme'])) {
            return $_SESSION['theme'];
        }
        
        // Check cookie
        if (isset($_COOKIE['theme'])) {
            return $_COOKIE['theme'];
        }
        
        // Default theme
        return $this->config['default'] ?? 'dark';
    }
    
    /**
     * Set current theme
     * 
     * @param string $theme
     * @return void
     */
    public function setTheme(string $theme): void
    {
        if (!($this->config['user_can_change'] ?? true)) {
            throw new \Exception("Theme customization is disabled by the administrator.");
        }
        
        if (!isset($this->config['themes'][$theme])) {
            throw new \Exception("Theme not found: {$theme}");
        }
        
        $this->currentTheme = $theme;
        $_SESSION['theme'] = $theme;
        setcookie('theme', $theme, time() + (365 * 24 * 60 * 60), '/');
    }
    
    /**
     * Get current theme
     * 
     * @return string
     */
    public function getCurrentTheme(): string
    {
        return $this->currentTheme;
    }
    
    /**
     * Get available themes
     * 
     * @return array
     */
    public function getAvailableThemes(): array
    {
        return $this->config['themes'] ?? [];
    }
    
    /**
     * Get theme colors
     * 
     * @return array
     */
    public function getColors(): array
    {
        return $this->config['colors'] ?? [];
    }
    
    /**
     * Get theme-specific colors
     * 
     * @return array
     */
    public function getThemeColors(): array
    {
        if ($this->currentTheme === 'dark') {
            return $this->config['dark'] ?? [];
        }
        
        return $this->config['light'] ?? [];
    }
    
    /**
     * Generate CSS variables string
     * 
     * @return string
     */
    public function generateCSSVariables(): string
    {
        $colors = $this->getColors();
        
        $css = ':root {' . PHP_EOL;
        
        // Add base color variables
        foreach ($colors as $name => $value) {
            if (is_array($value)) continue;
            $varName = $this->convertToKebabCase($name);
            $css .= "    --{$varName}: {$value};" . PHP_EOL;
        }
        
        // Add typography variables
        $typography = $this->config['typography'] ?? [];
        foreach ($typography as $name => $value) {
            $varName = $this->convertToKebabCase($name);
            $css .= "    --{$varName}: {$value};" . PHP_EOL;
        }
        
        // Add layout variables
        $layout = $this->config['layout'] ?? [];
        foreach ($layout as $name => $value) {
            $varName = $this->convertToKebabCase($name);
            $css .= "    --{$varName}: {$value};" . PHP_EOL;
        }
        
        $css .= '}' . PHP_EOL;
        
        // Dark theme variables
        $css .= '[data-theme="dark"] {' . PHP_EOL;
        $darkColors = $this->config['dark'] ?? [];
        foreach ($darkColors as $name => $value) {
            if (is_array($value)) continue;
            $varName = $this->convertToKebabCase($name);
            $css .= "    --{$varName}: {$value};" . PHP_EOL;
        }
        $css .= '}' . PHP_EOL;
        
        // Light theme variables
        $css .= '[data-theme="light"] {' . PHP_EOL;
        $lightColors = $this->config['light'] ?? [];
        foreach ($lightColors as $name => $value) {
            if (is_array($value)) continue;
            $varName = $this->convertToKebabCase($name);
            $css .= "    --{$varName}: {$value};" . PHP_EOL;
        }
        $css .= '}' . PHP_EOL;
        
        return $css;
    }
    
    /**
     * Generate inline style tag with CSS variables
     * 
     * @return string
     */
    public function getStyleTag(): string
    {
        return '<style>' . $this->generateCSSVariables() . '</style>';
    }
    
    /**
     * Get theme CSS file path
     * 
     * @return string
     */
    public function getThemeCSSPath(): string
    {
        $themes = $this->config['themes'] ?? [];
        return $themes[$this->currentTheme]['file'] ?? '';
    }
    
    /**
     * Convert camelCase or snake_case to kebab-case
     * 
     * @param string $str
     * @return string
     */
    protected function convertToKebabCase(string $str): string
    {
        $str = preg_replace('/([a-z])([A-Z])/', '$1-$2', $str);
        $str = str_replace('_', '-', $str);
        return strtolower($str);
    }
}
