(function ($) {
    "use strict";

    $(function () {
        var $form = $('#form'),
            $inputs = $form.find('input, select, textarea');

        $.listen('parsley:field:init', function (field) {
            field.$element.closest(".form-group").addClass('has-feedback');
        });

        // Setup validation
        // @todo double, help text
        // @todo specific error messages
        $form.parsley({
            trigger: 'keyup',
            errorClass: 'has-error',
            successClass: 'has-success',
            classHandler: function (field) {
                return field.$element.closest(".form-group");
            },
            errorsWrapper: '<ul class="help-block"></ul>'
        }).subscribe('parsley:form:validated', function (form) {
            if (true !== form.validationResult) {
                var tabId = form.$element.find('.has-error').first().closest('.tab-pane').attr('id');
                $('.nav-tabs a[href="#' + tabId + '"]').tab('show');
            }
        }).subscribe('parsley:field:validate', function (field) {
            field.$element.next('i').remove();
            field.$element.after('<i class="form-control-feedback fa fa-cog fa-spin"></i>');
        }).subscribe('parsley:field:success', function (field) {
            field.$element.next('i').remove();
            field.$element.after('<i class="form-control-feedback fa fa-check"></i>');
        }).subscribe('parsley:field:error', function (field) {
            field.$element.next('i').remove();
            field.$element.after('<i class="form-control-feedback fa fa-remove"></i>');
        });

        // Setup autosave
        // @todo: use AJAX queue
        $form.autosave({
            callbacks: {
                trigger: 'modify',
                scope: 'all',
                save: {
                    method: 'ajax',
                    options: {
                        url: $form.data('save'),
                        type: 'POST',
                        beforeSend: function () {
                            $('#status').html('<i class="fa fa-cog fa-spin"></i> Saving');
                        },
                        complete: function () {
                            $('#status').html('<i class="fa fa-check"></i> Saved');
                        }
                    }
                }
            }
        });

        // Setup locking
        setInterval(
            function () {
                var lockReq = $.get($form.data('lock'));

                lockReq.done(function () {
                    $inputs.prop('disabled', false);
                });

                lockReq.fail(function () {
                    $inputs.prop('disabled', true);
                });
            },
            10000
        );

        // Setup next/prev tab buttons
        $form.find('.btn-next').on('click', function () {
            $('.nav-tabs .active').next().find('a').tab('show');
            return false;
        });

        $form.find('.btn-prev').on('click', function () {
            $('.nav-tabs .active').prev().find('a').tab('show');
            return false;
        });

        // Setup help popovers
        var $links = $form.find('.popover-link');

        $links.popover({
            container: 'body',
            trigger: 'hover'
        });

        $links.on('click', function() {
            return false;
        });

        $inputs.on('focusin', function () {
            $(this).closest('.row').find('.popover-link').popover('show');
        });

        $inputs.on('focusout', function () {
            $(this).closest('.row').find('.popover-link').popover('hide');
        })
    });
})(jQuery);
