<?php

namespace Sybeha\Clubmaster\Forms\Fields;

use SilverStripe\Forms\TextField;

/**
 * Allows input of IBAN number via form field,
 * including generic validation of its value.
 * https://de.wikipedia.org/wiki/IBAN
 *
 * Class IbanField
 *
 * @package Sybeha\Clubmaster\Forms\Fields
 */
class IbanField extends TextField
{

    public function Type()
    {
        return 'IBAN text';
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
                'maxlength' => 34,
                'size' => 34
            ]
        );
    }

    public function validate($validator)
    {
        /* Include https://github.com/globalcitizen/php-iban
         * Valid number DE12500105170648489890
         * Simple validator rule [a-zA-Z]{2}[0-9]{2}[a-zA-Z0-9]{4}[0-9]{7}([a-zA-Z0-9]?){0,16}
         */
        include_once BASE_PATH . '/vendor/globalcitizen/php-iban/php-iban.php';

        if (!$this->value) {
            return true;
        }

        if (!verify_iban($this->value, $machine_format_only = false)) {
            $validator->validationError(
                $this->name,
                _t(
                    'Sybeha\Clubmaster\Forms\Fields\IbanField.VALIDATION_IBANNUMBER',
                    'Please ensure you have entered the {number} IBAN number correctly',
                    ['number' => $this->value]
                ),
                'validation error',
                false
            );
            return false;
        }
    }
}
