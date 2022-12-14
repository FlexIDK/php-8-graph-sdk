<?php

namespace One23\GraphSdk;

trait MapTypeTrait {

    protected static function mapType(mixed $value, string $type, mixed $default = null): mixed {
        if ($value instanceof \Closure) {
            $value = $value();
        }

        switch ($type) {
            case 'url':
                return is_string($value) && filter_var($value, FILTER_VALIDATE_URL)
                    ? $value
                    : null;

            case 'email':
                return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)
                    ? mb_strtolower($value)
                    : null;

            case 'intGte0':
                $value = is_numeric($value) ? (int)$value : null;
                return $value > 0 || $value === 0 ? $value : $default;

            case 'intGt0':
                $value = is_numeric($value) ? (int)$value : null;
                return $value > 0 ? $value : $default;

            case 'integer':
            case 'int':
                return is_numeric($value)
                    ? (int)$value
                    : $default;

            case 'float':
                return is_numeric($value)
                    ? (float)$value
                    : $default;

            case 'string':
            case 'str':
                return $value
                    ? (string)$value
                    : $default;

            case 'bool':
                return !!$value;

            case 'boolOrNull':
                return !is_null($value) ? !!$value : null;

            case 'arr':
            case 'array':
                return is_array($value)
                    ? $value
                    : $default;

            case 'arrOrBlank':
                return is_array($value)
                    ? $value
                    : [];

            case \DateTime::class:
                return $value instanceof \DateTime
                    ? $value
                    : $default;
        }

        if (class_exists($type)) {
            return $value instanceof $type
                ? $value
                : $default;
        }

        return $value;
    }

}
