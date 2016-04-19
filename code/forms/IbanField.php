<?php
/**
 * Allows input of IBAN number via form field,
 * including generic validation of its value.
 * https://de.wikipedia.org/wiki/IBAN
 *
 * @package clubmaster
 * @subpackage forms
 */
class IbanField extends TextField {

    /**
     * Add default attributes for use on all inputs.
     *
     * @return array List of attributes
     */

    public function getAttributes() {
        return array_merge(
            parent::getAttributes(),
            array(
            /*
                'autocomplete' => 'off',
                'maxlength' => 34,
                'size' => 34
            */
            )
        );
    }

    public function validate($validator)
    {
        /* Include https://github.com/globalcitizen/php-iban
         * Valid number DE12500105170648489890
         * Simple validator rule [a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16}
         */
        require_once BASE_PATH . '/vendor/globalcitizen/php-iban/php-iban.php';

        if(!$this->value)
        {
            return true;
        }

        if(!verify_iban($this->value,$machine_format_only=false))
        {
            $validator->validationError(
                $this->name,
                _t(
                    "IbanField.VALIDATIONIBANNUMBER",
                    "Please ensure you have entered the {number} IBAN number correctly",
                    array('number' => $this->value)
                ),
                "validation",
                false
            );
            return false;
        }
    }

}
