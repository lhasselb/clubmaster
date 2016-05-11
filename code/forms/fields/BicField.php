<?php
/**
 * Allows input of BIC number via form field,
 * including generic validation of its value.
 * https://de.wikipedia.org/wiki/ISO_9362
 *
 * @package clubmaster
 * @subpackage forms/fields
 */
class BicField extends TextField {

    public function Type() {
        return 'BIC text';
    }

    /**
     * Add default attributes for use on all inputs.
     *
     * @return array List of attributes
     */

    public function getAttributes() {
        return array_merge(
            parent::getAttributes(),
            array(
                //'autocomplete' => 'off',
                'maxlength' => 11,
                'size' => 11
            )
        );
    }

    public function validate($validator)
    {

        /* Valid number PBNKDEFF
         * Simple validator rule ^([a-zA-Z]{4}[a-zA-Z]{2}[a-zA-Z0-9]{2}([a-zA-Z0-9]{3})?)$
         */
        if(!$this->value)
        {
            return true;
        }

        if(!verify_bic($this->value))
        {
            $validator->validationError(
                $this->name,
                _t(
                    "BicField.VALIDATIONBICNUMBER",
                    "Please ensure you have entered the {number} BIC number correctly",
                    array('number' => $this->value)
                ),
                "validation",
                false
            );
            return false;
        }
    }

}

function verify_bic($bic)
{
    if (preg_match("/^([a-zA-Z]{4}[a-zA-Z]{2}[a-zA-Z0-9]{2}([a-zA-Z0-9]{3})?)$/", $bic))
    {
        return true;
    } else {
        return false;
    }

}
