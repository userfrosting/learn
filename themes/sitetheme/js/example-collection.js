$(document).ready(function() {

    function renderOutput() {
        var data = $('.js-form-phones :input').serializeControls();
        $('.js-form-phones-output').html(JSON.stringify(data, null, 2));
    }
    
    var phoneCollection = $("body").find('.js-form-phones');

    // Handler to add phone masks and bindings for new rows
    phoneCollection.on('rowAdd.ufCollection', function (event, row) {
        var phoneInput = $(row).find(".js-input-phone");
        phoneInput.inputmask();

        $(row).find(':input').on('change keyup', function () {
            renderOutput();
        });
        $(row).on('rowDelete.ufCollection', function () {
            renderOutput();
        });
    });

    // Initialize phone collection
    phoneCollection.ufCollection({
        useDropdown: false,
        rowTemplate : $('#collection-phones-row').html()
    });

    // Add an initial phone
    var phone1 = {
        id: 17,
        label: "primary",
        number: "5555551212"
    };
    phoneCollection.ufCollection('addRow', phone1);

    // Handler for deleting rows
    phoneCollection.on('rowDelete.ufCollection', function () {
        renderOutput();
    });

    // Handler to display content as received on the server side
    $('.js-form-phones :input').on('change keyup', function () {
        renderOutput();
    }).trigger('change');
});
