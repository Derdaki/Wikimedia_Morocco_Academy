<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $rules
     * @return array<string, string>
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            if (isset($data[$field]) && !is_scalar($data[$field])) {
                $errors[$field] = 'Valeur invalide.';
                continue;
            }
            $value = trim((string) ($data[$field] ?? ''));
            $rulesForField = explode('|', $ruleString);

            foreach ($rulesForField as $rule) {
                if ($rule === 'required' && $value === '') {
                    $errors[$field] = 'Ce champ est obligatoire.';
                }

                if ($rule === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Veuillez fournir une adresse email valide.';
                }

                if (str_starts_with($rule, 'max:') && mb_strlen($value) > (int) substr($rule, 4)) {
                    $errors[$field] = 'Ce champ est trop long.';
                }

                if ($rule === 'url' && $value !== '' &&
                    (!filter_var($value, FILTER_VALIDATE_URL) || !safe_url($value))) {
                    $errors[$field] = 'Veuillez fournir une URL valide.';
                }
                if ($rule === 'asset' && !safe_url($value)) $errors[$field] = 'Veuillez fournir une URL HTTP(S) ou un chemin local valide.';
                if ($rule === 'integer' && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) $errors[$field] = 'Veuillez fournir un nombre entier.';
                if (str_starts_with($rule, 'in:') && $value !== '' && !in_array($value, explode(',', substr($rule, 3)), true)) $errors[$field] = 'Choix invalide.';
                if ($rule === 'date' && $value !== '') {
                    $normalized = str_replace('T', ' ', $value);
                    $format = strlen($normalized) === 16 ? 'Y-m-d H:i' : 'Y-m-d H:i:s';
                    $date = \DateTimeImmutable::createFromFormat('!' . $format, $normalized);
                    if (!$date || $date->format($format) !== $normalized || (int) $date->format('Y') < 1000) $errors[$field] = 'Date invalide.';
                }
            }
        }

        return $errors;
    }
}
