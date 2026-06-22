<?php

namespace App\Infrastructure\Authentication;

/**
 * Password Validator
 * 
 * Enforces security settings for passwords.
 */
class PasswordValidator
{
    /**
     * Validate a password against the security configuration
     * 
     * @param string $password The password to validate
     * @return array Array of error messages. Empty if valid.
     */
    public static function validate(string $password): array
    {
        $errors = [];
        $minLength = config('security.password.min_length', 8);
        $requireUppercase = config('security.password.require_uppercase', true);
        $requireNumbers = config('security.password.require_numbers', true);
        $requireSpecial = config('security.password.require_special', true);

        if (strlen($password) < $minLength) {
            $errors[] = "Password must be at least {$minLength} characters long.";
        }

        if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter.";
        }

        if ($requireNumbers && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number.";
        }

        if ($requireSpecial && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character.";
        }

        return $errors;
    }
}
