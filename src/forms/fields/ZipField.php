<?php

namespace SYBEHA\Clubmaster\Forms\Fields;

use SilverStripe\Forms\TextField;

/**
 * Text input field with validation for numeric (zip) values.
 * Supports validating the numeric value.
 *
 * Class ZipField
 *
 * @package SYBEHA\Clubmaster\Forms\Fields
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
            array(
                //'autocomplete' => 'off',
                'maxlength' => 5,
                'size' => 5
            )
        );
    }

    public function validate($validator)
    {

        if (!$this->value) {
            return true;
        }

        if (!verify_zip($this->value)) {
            $validator->validationError(
                $this->name,
                _t(
                    'ZipField.VALIDATIONZIP',
                    "'{value}' is not a zip number, only zip numbers can be accepted for this field",
                    array('value' => $this->value)
                ),
                "validation",
                false
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
