$(document).ready(function() {

    function renderOutput(el) {
        var data = el.find(':input').serializeControls();
        el.find('.example-output code').html(JSON.stringify(data, null, 2));
    }
    
    var phoneCollection = $('#example-phones');

    // Handler to add phone masks and bindings for new rows
    phoneCollection.on('rowAdd.ufCollection', function (event, row) {
        renderOutput(phoneCollection);

        var phoneInput = $(row).find('.js-input-phone');
        phoneInput.inputmask();

        $(row).find(':input').on('change keyup', function () {
            renderOutput(phoneCollection);
        });
    });

    // Handler for deleting rows
    phoneCollection.on('rowDelete.ufCollection', function () {
        renderOutput(phoneCollection);
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



    // Set up example many-to-many
    var owls = [
        {
            id: 11,
            species: 'Bubo scandiacus',
            description: "Snowy owls are native to Arctic regions in North America and Eurasia. Males are almost all white, while females have more flecks of black plumage. Juvenile snowy owls have black feathers until they turn white. The snowy owl is a ground nester that predominantly hunts rodents."
        },
        {
            id: 8,
            species: 'Megascops asio',
            description: "This species is native to most wooded environments of its distribution and, more so than any other owl in its range, has adapted well to manmade development, although it frequently avoids detection due to its strictly nocturnal habits."
        }
    ];

    var owlCollection = $('#example-member-owls');

    // Handler to add bindings for new rows
    owlCollection.on('rowAdd.ufCollection', function (event, row) {
        renderOutput(owlCollection);

        $(row).find(':input').on('change keyup', function () {
            renderOutput(owlCollection);
        });
    });

    // Handler to re-render when deleting a row
    owlCollection.on('rowDelete.ufCollection', function () {
        renderOutput(owlCollection);
    });

    // Initialize owl collection
    owlCollection.ufCollection({
        dropdown: {
            ajax: null,
            data: owls
        },
        dropdownTemplate: $('#example-member-owls-select-option').html(),
        rowTemplate: $('#example-member-owls-row').html()
    });

    // Add an initial owl
    var owl1 = owls[0];
    owl1.text = owl1.species;
    owlCollection.ufCollection('addRow', owl1);
});
