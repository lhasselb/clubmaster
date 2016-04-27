/**
 * File: ClubAdmin.js
 * ========================================
 * Add features to cms backend
 *
 * @package clubmaster
 *
 * @author guggelimehl [at] gmail.com
 *
 */

(function($) {
    $.entwine('ss', function($) {

        $(document).ready(function() {
            $('form:first *:input[type!=hidden]:first').focus();
        });

        /**
         * Class: Form_ItemEditForm_EqualAddress_Holder input
         *
         * Fill fields with given value
         */
        $('#Form_ItemEditForm_EqualAddress_Holder input').entwine({
            onmatch: function() {
                console.log('FOUND');
            },
            onchange: function() {
                if (this.is(':checked')) {
                    console.log('Checked');
                    $("input[name='AccountHolderFirstName']").val($("input[name='FirstName']").val());
                    $("input[name='AccountHolderLastName']").val($("input[name='LastName']").val());
                    $("input[name='AccountHolderStreet']").val($("input[name='Street']").val());
                    $("input[name='AccountHolderStreetNumber']").val($("input[name='StreetNumber']").val());
                    $("input[name='AccountHolderCity']").val($("input[name='City']").val());
                    $("input[name='AccountHolderZip']").val($("input[name='Zip']").val());
                    //$('.settings').show();

                } else {
                    //$('.settings').hide();
                    console.log('UN-Checked');
                    $("input[name='AccountHolderFirstName']").val('');
                    $("input[name='AccountHolderLastName']").val('');
                    $("input[name='AccountHolderStreet']").val('');
                    $("input[name='AccountHolderStreetNumber']").val('');
                    $("input[name='AccountHolderZip']").val('');
                    $("input[name='AccountHolderCity']").val('');
                }
                this._super();
            }
        });

    });
}(jQuery));
