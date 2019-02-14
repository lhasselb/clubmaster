import jQuery from 'jquery';
// import 'jquery-validation'; Moved to CDN on page controller


const bindGroups = () => {
    // First copy values
    jQuery('#Form_EnrollForm_AccountHolderFirstName').val(jQuery('#Form_EnrollForm_FirstName').val());
    jQuery('#Form_EnrollForm_AccountHolderLastName').val(jQuery('#Form_EnrollForm_LastName').val());
    jQuery('#Form_EnrollForm_AccountHolderStreet').val(jQuery('#Form_EnrollForm_Street').val());
    jQuery('#Form_EnrollForm_AccountHolderStreetNumber').val(jQuery('#Form_EnrollForm_StreetNumber').val());
    jQuery('#Form_EnrollForm_AccountHolderZip').val(jQuery('#Form_EnrollForm_Zip').val());
    jQuery('#Form_EnrollForm_AccountHolderCity').val(jQuery('#Form_EnrollForm_City').val());
    // Then bind fields
    jQuery('#Form_EnrollForm_FirstName').bind('change keyup', () => {
        jQuery('#Form_EnrollForm_AccountHolderFirstName').val(jQuery(this).val());
    });
    jQuery('#Form_EnrollForm_LastName').bind('change keyup', () => {
        jQuery('#Form_EnrollForm_AccountHolderLastName').val(jQuery(this).val());
    });
    jQuery('#Form_EnrollForm_Street').bind('change keyup', () => {
        jQuery('#Form_EnrollForm_AccountHolderStreet').val(jQuery(this).val());
    });
    jQuery('#Form_EnrollForm_StreetNumber').bind('change keyup', () => {
        jQuery('#Form_EnrollForm_AccountHolderStreetNumber').val(jQuery(this).val());
    });
    jQuery('#Form_EnrollForm_Zip').bind('change keyup', () => {
        jQuery('#Form_EnrollForm_AccountHolderZip').val(jQuery(this).val());
    });
    jQuery('#Form_EnrollForm_City').bind('change keyup', () => {
        jQuery('#Form_EnrollForm_AccountHolderCity').val(jQuery(this).val());
    });
};
const unbindGroups = () => {
    jQuery('#Form_EnrollForm_FirstName').unbind('change keyup');
    jQuery('#Form_EnrollForm_LastName').unbind('change keyup');
    jQuery('#Form_EnrollForm_Street').unbind('change keyup');
    jQuery('#Form_EnrollForm_StreetNumber').unbind('change keyup');
    jQuery('#Form_EnrollForm_Zip').unbind('change keyup');
    jQuery('#Form_EnrollForm_City').unbind('change keyup');
};

if (jQuery('input[name="EqualAddress"]:checked').length > 0) {
    bindGroups();
    // Hide fields on init
    jQuery('div[id^="Form_EnrollForm_AccountHolder"]').hide();
}

jQuery('input[name="EqualAddress"]').change(() => {
    if (jQuery('input[name="EqualAddress"]:checked').length > 0) {
        bindGroups();
        jQuery('div[id^="Form_EnrollForm_AccountHolder"]').hide();
    } else {
        unbindGroups();
        jQuery('div[id^="Form_EnrollForm_AccountHolder"]').show();
    }
});

/* Add date validation */
jQuery.validator.addMethod('maxDate', function md(value, element) {
    const now = new Date();
    const givenDate = new Date(value);
    return this.optional(element) || givenDate <= now;
});

/* Front-end validation using jquery-validate */
jQuery('#Form_EnrollForm').validate({
    // TODO: Make this dynamic
    lang: 'de',
    // ignore: '.date',
    rules: {
        Salutation: {
            required: true
        },
        FirstName: {
            required: true
        },
        LastName: {
            required: true
        },
        Birthday: {
            required: true,
            date: true,
            maxDate: true
        },
        Nationality: {
            required: true
        },
        Street: {
            required: true
        },
        StreetNumber: {
            required: true
        },
        Zip: {
            required: true
        },
        City: {
            required: true
        },
        Email: {
            required: true
        },
        Mobil: {
            required: '#Form_EnrollForm_Phone:blank'
        },
        Phone: {
            required: '#Form_EnrollForm_Mobil:blank'
        },
        TypeID: {
            required: true
        },
        Since: {
            required: true
        },
        AccountHolderFirstName: {
            required: true
        },
        AccountHolderLastName: {
            required: true
        },
        AccountHolderStreet: {
            required: true
        },
        AccountHolderStreetNumber: {
            required: true
        },
        AccountHolderZip: {
            required: true
        },
        AccountHolderCity: {
            required: true
        },
        Iban: {
            required: true,
            iban: true
        },
        Bic: {
            required: true,
            bic: true
        },
    }
});
