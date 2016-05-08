jQuery.noConflict();

(function($){
    $(document).ready(function() {

        var bindGroups = function() {

            // First copy values
            $("#Form_EnrollForm_AccountHolderFirstName").val($("#Form_EnrollForm_FirstName").val());
            $("#Form_EnrollForm_AccountHolderLastName").val($("#Form_EnrollForm_LastName").val());
            $("#Form_EnrollForm_AccountHolderStreet").val($("#Form_EnrollForm_Street").val());
            $("#Form_EnrollForm_AccountHolderStreetNumber").val($("#Form_EnrollForm_StreetNumber").val());
            $("#Form_EnrollForm_AccountHolderZip").val($("#Form_EnrollForm_Zip").val());
            $("#Form_EnrollForm_AccountHolderCity").val($("#Form_EnrollForm_City").val());

            // Then bind fields
            $("#Form_EnrollForm_FirstName").bind("change keyup", function() {
                $("#Form_EnrollForm_AccountHolderFirstName").val($(this).val());
                console.log('FirstName');
            });
            $("#Form_EnrollForm_LastName").bind("change keyup", function() {
                $("#Form_EnrollForm_AccountHolderLastName").val($(this).val());
                console.log('LastName');
            });
            $("#Form_EnrollForm_Street").bind("change keyup", function() {
                $("#Form_EnrollForm_AccountHolderStreet").val($(this).val());
                console.log('Street');
            });
            $("#Form_EnrollForm_StreetNumber").bind("change keyup", function() {
                $("#Form_EnrollForm_AccountHolderStreetNumber").val($(this).val());
                console.log('StreetNumber');
            });
            $("#Form_EnrollForm_Zip").bind("change keyup", function() {
                $("#Form_EnrollForm_AccountHolderZip").val($(this).val());
                console.log('Zip');
            });
            $("#Form_EnrollForm_City").bind("change keyup", function() {
                $("#Form_EnrollForm_AccountHolderCity").val($(this).val());
                console.log('City');
            });
        };

        var unbindGroups = function() {
            $("#Form_EnrollForm_FirstName").unbind("change keyup");
            $("#Form_EnrollForm_LastName").unbind("change keyup");
            $("#Form_EnrollForm_Street").unbind("change keyup");
            $("#Form_EnrollForm_StreetNumber").unbind("change keyup");
            $("#Form_EnrollForm_Zip").unbind("change keyup");
            $("#Form_EnrollForm_City").unbind("change keyup");
        };

        if ($("input[name='EqualAddress']:checked").length > 0) {
            bindGroups();
            // Hide fields on init
            $("div[id^='Form_EnrollForm_AccountHolder']").hide();
        }

        $("input[name='EqualAddress']").change(function() {
            if ($("input[name='EqualAddress']:checked").length > 0) {
                bindGroups();
                $('div[id^="Form_EnrollForm_AccountHolder"]').hide();
            } else {
                unbindGroups();
                $('div[id^="Form_EnrollForm_AccountHolder"]').show();
            }
        });

    });

}(jQuery));

