<?php

namespace Sybeha\Clubmaster\Forms\Fields;

use SilverStripe\Forms\TextField;

/**
 * Text input field with validation for numeric (telephon) values.
 * Supports validating the numeric value.
 *
 * Class TelephoneNumberField
 *
 * @package Sybeha\Clubmaster\Forms\Fields;
 */
class TelephoneNumberField extends TextField
{

    public function Type()
    {
        return 'Telephone number';
    }

    public function validate($validator)
    {

        if (!$this->value) {
            return true;
        }

        if (!verify_phone($this->value)) {
            $validator->validationError(
                $this->name,
                _t(
                    'Sybeha\Clubmaster\Forms\Fields\TelephoneNumberField.VALIDATION_PHONE',
                    '{value} is not valid Phone numer, special charcters cannot be accepted for this field',
                    ['value' => $this->value]
                ),
                'validation error',
                false
            );
            return false;
        }
    }
}


function verify_phone($phone)
{
    if (preg_match("/^[0-9 \+\-]+$/", $phone)) {
        return true;
    } else {
        return false;
    }
}
