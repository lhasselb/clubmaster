<?php
/**
 * Text input field with validation for numeric (telephon) values.
 * Supports validating the numeric value.
 *
 * @package clubmaster
 * @subpackage forms/fields
 */
class TelephoneNumberField extends TextField {

    public function Type() {
        return 'Telephone number';
    }

    public function validate($validator)
    {

        if(!$this->value)
        {
            return true;
        }

        if(!verify_phone($this->value))
        {
            $validator->validationError(
                $this->name,
                _t(
                    'PhoneNumberField.VALIDATIONPHONE',
                    "'{value}' is not valid Phone numer, special charcters cannot be accepted for this field",
                    array('value' => $this->value)
                ),
                "validation",
                false
            );
            return false;
        }
    }

}


function verify_phone($phone) {
    if (preg_match("/^[0-9 \+\-]+$/", $phone)) {
        return true;
    } else {
        return false;
    }
}
