<?php

namespace Sybeha\Clubmaster\Forms\Fields;

use SilverStripe\Forms\TextField;
use SilverStripe\ORM\ValidationResult;

/**
 * Text input field with validation for numeric (zip) values.
 * Supports validating the numeric value.
 *
 * Class ZipField
 *
 * @package Sybeha\Clubmaster\Forms\Fields
 */
class ZipField extends TextField
{

    public function Type()
    {
        return 'ZIP text';
    }

    /**
     * Add default attributes for use on all inputs.
     *
     * @return array List of attributes
     */

    public function getAttributes()
    {
        return array_merge(
            parent::getAttributes(),
            [
                //'autocomplete' => 'off',
                'maxlength' => 5,
                'size' => 5
            ]
        );
    }

    /**
     * @param Validator $validator
     * @return bool
     */
    public function validate($validator)
    {

        if (empty($this->value)) {
            return true;
        }

        if (!verify_zip($this->value)) {
            $validator->validationError(
                $this->name,
                _t(
                    'Sybeha\Clubmaster\Forms\Fields\ZipField.VALIDATION_ZIP',
                    '{value} is not a zip number, only zip numbers can be accepted for this field',
                    ['value' => $this->value]
                ),
                ValidationResult::TYPE_ERROR, //'validation error'
                ValidationResult::CAST_HTML //false
            );
            return false;
        }
    }
}

function verify_zip($zip)
{
    if (preg_match("/^[0-9]{5}$/", $zip)) {
        return true;
    } else {
        return false;
    }
}
