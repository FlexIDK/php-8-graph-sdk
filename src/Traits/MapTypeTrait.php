<?php

namespace One23\GraphSdk;

trait MapTypeTrait {

    protected static function mapType(mixed $value, string $type, mixed $default = null): mixed {

        switch ($type) {
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
