
$.fn.datepicker.dates['fr'] = {
    days: ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"],
    daysShort: ["Dim", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"],
    daysMin: ["D", "L", "Ma", "Me", "J", "V", "S", "D"],
    months: ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"],
    monthsShort: ["Jan", "Fev", "Mar", "Avr", "Mai", "Jui", "Jul", "Aou", "Sep", "Oct", "Nov", "Dec"]
};

$(document).ready(function() {

    // Keep the original textarea/name: Symfony receives the edited HTML on submit.
    $('.ckeditor').each(function() {
        var $field = $(this);
        var $count = $('<small class="help-block editor-count" aria-live="polite"></small>');
        $field.trumbowyg({
            lang: 'fr',
            svgPath: '/build/admin/vendor/trumbowyg/dist/ui/icons.svg',
            autogrow: true,
            semantic: false,
            removeformatPasted: true,
            btns: [
                ['viewHTML'], ['formatting'], ['strong', 'em', 'underline'],
                ['link'], ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'], ['removeformat'], ['undo', 'redo']
            ]
        });
        $field.closest('.trumbowyg-box').after($count);
        var editor = $field.data('trumbowyg');
        var updateCount = function() {
            editor.syncCode();
            var text = $('<div>').html($field.trumbowyg('html')).text().trim();
            $count.text((text ? text.split(/\s+/u).length : 0) + ' mots · ' + Array.from(text).length + ' caractères');
        };
        $field.on('tbwchange tbwblur', updateCount);
        editor.$ed.on('input', updateCount);
        $field.closest('form').on('submit', function() {
            editor.syncCode();
            $field.val($field.trumbowyg('html'));
        });
        updateCount();
    });

    $('.meditor').each(function(e) {
        $(this).attr('rows', 10);
    });

    $(".switch-button").bootstrapSwitch({
        onColor: 'success',
        offColor: 'danger',
        onText: 'Oui',
        offText: 'Non',
        size: 'small'
    });

    $(".switch-button-active").bootstrapSwitch({
        onColor: 'success',
        offColor: 'danger',
        onText: 'On',
        offText: 'Off',
        size: 'mini'
    });

    $(".switch-button-site").bootstrapSwitch({
        onColor: 'success',
        offColor: 'danger',
        onText: 'Oui',
        offText: 'Non',
        size: 'mini'
    });
    $('.selectpicker').selectpicker('render');

    $('.date-change-input').prop('type', 'text');
    $(".date").datepicker({
        todayHighlight: true,
        format: 'dd/mm/yyyy',
        language: 'fr'
    });

    $('.multiselect').multiselect({
        enableCaseInsensitiveFiltering: true,
        buttonWidth: '100%',
        buttonClass: 'btn btn-primary',
        selectAllText: 'Tout cocher',
        onDropdownShow: function(event) {
            $('.panel-body').addClass('overflowVisi');
        },
        onDropdownHide: function(event) {
            $('.panel-body').removeClass('overflowVisi');
        }
    });

    $('.bootstrap-switch-label').click(function(e){
        if($(this).parent('.bootstrap-switch-container').find('input').hasClass('switch-button-active') == true){
            e.stopPropagation();
        }
    });

    $('.collection-list').on('click', '.collection-remove', function() {
        var $me = $(this);
        $me.closest('.collection-item').remove();

        return false;
    });

    $('.collection-add').click(function() {
        var $me = $(this);
        var $collectionList = $($me.attr('href'));
        var prototype = $collectionList.data('prototype');
        prototype = prototype.replace(/__name__/g, $collectionList.children().length);
        var $el = $(prototype);
        $collectionList.append($el);
        $collectionList.trigger('collection-added', $el);

        return false;
    });


    var easter_egg = new Konami();
    easter_egg.code = function() {
        new Audio('/bundles/admin/js/nrj.mp3').play();
    };
    easter_egg.load();
});
