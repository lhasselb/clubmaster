<?php

namespace Sybeha\Clubmaster\Forms\Fields;

use SilverStripe\Forms\TextField;

/**
 * Text input field with validation for numeric values. 
 * Supports validating the numeric value.
 *
 * Class EUNameTextField
 *
 * @package Sybeha\Clubmaster\Forms\Fields
 */
class EUNameTextField extends TextField
{
    public function Type()
    {
        return 'Numeric text';
    }

    public function validate($validator)
    {

        if (!$this->value) {
            return true;
        }

        if (!verify_name($this->value)) {
            $validator->validationError(
                $this->name,
                _t(
                    'Sybeha\Clubmaster\Forms\Fields\EUNameTextField.VALIDATION_NAME',
                    '{value} is not valid, special charcters cannot be accepted for this field',
                    ['value' => $this->value]
                ),
                'validation error',
                false
            );
            return false;
        }
    }
}

/**
 * ^[a-zA-Z0-9\-'àÀâÂäÄáÁéÉèÈêÊëËìÌîÎïÏòóÒôÔöÖùúÙûÛüÜçÇ’ñß]$
 */

function verify_name($name)
{
    if (preg_match("/^[a-zA-Z0-9\-'àÀâÂäÄáÁéÉèÈêÊëËìÌîÎïÏòóÒôÔöÖùúÙûÛüÜçÇ’ñß\. ]+$/", $name)) {
        return true;
    } else {
        return false;
    }
}
