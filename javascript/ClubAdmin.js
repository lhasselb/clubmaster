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

        $('#Form_ImportForm_EmptyBeforeImport').entwine({
            onmatch: function() {
                this.hide();
            }
        });

        $('.col-Sex').entwine({
            onmatch: function() {
                //console.log(' sex FOUND = ' + this.text().toLowerCase());
                var sex =  this.text().toLowerCase();
                //console.log($.trim(this.text().toLowerCase()));
                if (sex == 'm') this.addClass('male');
                else if (sex == 'w') this.addClass('female');
            }
        });

        /**
         * ID: Form_ItemEditForm_EqualAddress
         *
         * Fill hide fields
         */
        $('#Form_ItemEditForm_EqualAddress').entwine({
            onmatch: function() {
                //console.log('FOUND');
                //console.log($.trim(this.text().toLowerCase()));
                var equalAddress = $.trim(this.text().toLowerCase());
                if (equalAddress == 'ja' || equalAddress == 'yes') {
                    $("#Form_ItemEditForm_AccountHolderFirstName_Holder").hide();
                    $("#Form_ItemEditForm_AccountHolderLastName_Holder").hide();
                    $("#Form_ItemEditForm_AccountHolderStreet_Holder").hide();
                    $("#Form_ItemEditForm_AccountHolderStreetNumber_Holder").hide();
                    $("#Form_ItemEditForm_AccountHolderZip_Holder").hide();
                    $("#Form_ItemEditForm_AccountHolderCity_Holder").hide();
                }
            }
        });

        /**
         * ID: Form_ItemEditForm_EqualAddress_Holder input
         *
         * Fill fields with given value show/hide if required
         */
        $('#Form_ItemEditForm_EqualAddress_Holder input').entwine({
            onmatch: function() {
                if (this.is(':checked')) {
                    // First copy values
                    $("input[name='AccountHolderFirstName']").val($("input[name='FirstName']").val());
                    $("input[name='AccountHolderLastName']").val($("input[name='LastName']").val());
                    $("input[name='AccountHolderStreet']").val($("input[name='Street']").val());
                    $("input[name='AccountHolderStreetNumber']").val($("input[name='StreetNumber']").val());
                    $("input[name='AccountHolderCity']").val($("input[name='City']").val());
                    $("input[name='AccountHolderZip']").val($("input[name='Zip']").val());
                    // Then bind fields
                    $("input[name='FirstName']").bind("change keyup", function() {
                        $("input[name='AccountHolderFirstName']").val($("input[name='FirstName']").val());
                    });
                    $("input[name='LastName']").bind("change keyup", function() {
                        $("input[name='AccountHolderLastName']").val($("input[name='LastName']").val());
                    });
                    $("input[name='Street']").bind("change keyup", function() {
                        $("input[name='AccountHolderStreet']").val($("input[name='Street']").val());
                    });
                    $("input[name='StreetNumber']").bind("change keyup", function() {
                        $("input[name='AccountHolderStreetNumber']").val($("input[name='StreetNumber']").val());
                    });
                    $("input[name='City']").bind("change keyup", function() {
                        $("input[name='AccountHolderCity']").val($("input[name='City']").val());
                    });
                    $("input[name='Zip']").bind("change keyup", function() {
                        $("input[name='AccountHolderZip']").val($("input[name='Zip']").val());
                    });
                    // Hide
                    $("#Form_ItemEditForm_AccountHolderFirstName_Holder").hide();
                    $("#Form_ItemEditForm_AccountHolderLastName_Holder").hide();
                    $("#Form_ItemEditForm_AccountHolderStreet_Holder").hide();
                    $("#Form_ItemEditForm_AccountHolderStreetNumber_Holder").hide();
                    $("#Form_ItemEditForm_AccountHolderZip_Holder").hide();
                    $("#Form_ItemEditForm_AccountHolderCity_Holder").hide();

                }
            },
            onchange: function() {
                if (this.is(':checked')) {
                    /*console.log('Checked');*/
                    $("input[name='AccountHolderFirstName']").val($("input[name='FirstName']").val());
                    $("#Form_ItemEditForm_AccountHolderFirstName_Holder").hide();
                    $("input[name='AccountHolderLastName']").val($("input[name='LastName']").val());
                    $("#Form_ItemEditForm_AccountHolderLastName_Holder").hide();
                    $("input[name='AccountHolderStreet']").val($("input[name='Street']").val());
                    $("#Form_ItemEditForm_AccountHolderStreet_Holder").hide();
                    $("input[name='AccountHolderStreetNumber']").val($("input[name='StreetNumber']").val());
                    $("#Form_ItemEditForm_AccountHolderStreetNumber_Holder").hide();
                    $("input[name='AccountHolderCity']").val($("input[name='City']").val());
                    $("#Form_ItemEditForm_AccountHolderZip_Holder").hide();
                    $("input[name='AccountHolderZip']").val($("input[name='Zip']").val());
                    $("#Form_ItemEditForm_AccountHolderCity_Holder").hide();
                } else {
                    /*console.log('UN-Checked');*/
                    $("input[name='AccountHolderFirstName']").val('');
                    $("#Form_ItemEditForm_AccountHolderFirstName_Holder").show();
                    $("input[name='AccountHolderLastName']").val('');
                    $("#Form_ItemEditForm_AccountHolderLastName_Holder").show();
                    $("input[name='AccountHolderStreet']").val('');
                    $("#Form_ItemEditForm_AccountHolderStreet_Holder").show();
                    $("input[name='AccountHolderStreetNumber']").val('');
                    $("#Form_ItemEditForm_AccountHolderStreetNumber_Holder").show();
                    $("input[name='AccountHolderZip']").val('');
                    $("#Form_ItemEditForm_AccountHolderZip_Holder").show();
                    $("input[name='AccountHolderCity']").val('');
                    $("#Form_ItemEditForm_AccountHolderCity_Holder").show();
                }
                this._super();
            }
        });

    });
}(jQuery));
