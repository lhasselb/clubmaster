(function($) {
  $(document).ready(function(){
    const bindGroups = () => {
      // First copy values
      $('#Form_EnrollForm_AccountHolderFirstName').val($('#Form_EnrollForm_FirstName').val());
      $('#Form_EnrollForm_AccountHolderLastName').val($('#Form_EnrollForm_LastName').val());
      $('#Form_EnrollForm_AccountHolderStreet').val($('#Form_EnrollForm_Street').val());
      $('#Form_EnrollForm_AccountHolderStreetNumber').val($('#Form_EnrollForm_StreetNumber').val());
      $('#Form_EnrollForm_AccountHolderZip').val($('#Form_EnrollForm_Zip').val());
      $('#Form_EnrollForm_AccountHolderCity').val($('#Form_EnrollForm_City').val());
      // Then bind fields
      $('#Form_EnrollForm_FirstName').bind('change keyup', () => {
        if ($('#Form_EnrollForm_AccountHolderFirstName').val()) {
          $('#Form_EnrollForm_AccountHolderFirstName').val($(this).val());
        }
      });
      $('#Form_EnrollForm_LastName').bind('change keyup', () => {
        if ($('#Form_EnrollForm_AccountHolderLastName').val()) {
          $('#Form_EnrollForm_AccountHolderLastName').val($(this).val());
        }
      });
      $('#Form_EnrollForm_Street').bind('change keyup', () => {
        if ($('#Form_EnrollForm_AccountHolderStreet').val()) {
          $('#Form_EnrollForm_AccountHolderStreet').val($(this).val());
        }
      });
      $('#Form_EnrollForm_StreetNumber').bind('change keyup', () => {
        if ($('#Form_EnrollForm_AccountHolderStreetNumber').val()) {
          $('#Form_EnrollForm_AccountHolderStreetNumber').val($(this).val());
        }
      });
      $('#Form_EnrollForm_Zip').bind('change keyup', () => {
        if ($('#Form_EnrollForm_AccountHolderZip').val()) {
          $('#Form_EnrollForm_AccountHolderZip').val($(this).val());
        }
      });
      $('#Form_EnrollForm_City').bind('change keyup', () => {
        if ($('#Form_EnrollForm_AccountHolderCity').val()) {
          $('#Form_EnrollForm_AccountHolderCity').val($(this).val());
        }
      });
    };
    const unbindGroups = () => {
      $('#Form_EnrollForm_FirstName').unbind('change keyup');
      $('#Form_EnrollForm_LastName').unbind('change keyup');
      $('#Form_EnrollForm_Street').unbind('change keyup');
      $('#Form_EnrollForm_StreetNumber').unbind('change keyup');
      $('#Form_EnrollForm_Zip').unbind('change keyup');
      $('#Form_EnrollForm_City').unbind('change keyup');
    };

    if ($('input[name="EqualAddress"]:checked').length > 0) {
      bindGroups();
      // Hide fields on init
      $('div[id^="Form_EnrollForm_AccountHolder"]').hide();
    }

    $('input[name="EqualAddress"]').change(() => {
      if ($('input[name="EqualAddress"]:checked').length > 0) {
        bindGroups();
        $('div[id^="Form_EnrollForm_AccountHolder"]').hide();
      } else {
        unbindGroups();
        $('div[id^="Form_EnrollForm_AccountHolder"]').show();
      }
    });

    $('input[name="Mobil"]').change(() => {
      if ($('#Form_EnrollForm_Mobil').valid()) {
        $('input[name="Phone"]').removeAttr('required');
      }
    });

    $('input[name="Phone"]').change(() => {
      if ($('#Form_EnrollForm_Phone').valid()) {
        $('input[name="Mobil"]').removeAttr('required');
      }
    });

    /* Front-end validation using jquery-validate */
    $('#Form_EnrollForm').validate({
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
  })
})(jQuery);

/* Add date validation */
jQuery.validator.addMethod('maxDate', function md(value, element) {
  const now = new Date();
  const givenDate = new Date(value);
  return this.optional(element) || givenDate <= now;
});
