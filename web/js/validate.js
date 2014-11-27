(function ($) {
    "use strict";

    var
        nl2br = function (str, is_xhtml) {
            var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
            return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
        },

        updateData = function (dataField, field, val) {
            window.ParsleyUI.removeError(dataField, 'remote');

            if (field.$element.attr('id') !== dataField.$element.attr('id')) {
                if (dataField.$element.attr('type') === 'checkbox') {
                    dataField.$element.prop('checked', val).trigger('change');
                } else {
                    dataField.$element.val(val);
                }
            }
        },

        // @todo: clean, use recursive method
        updateDataAndErrors = function (field) {
            var json;

            clearErrors(field);

            if (typeof field._xhr === "undefined" || typeof field._xhr.responseText === "undefined") {
                return;
            }

            json = $.parseJSON(field._xhr.responseText);

            $.each(json.data, function (key, val) {
                if (val === Object(val)) {
                    $.each(val, function (key2, val2) {
                        var dataField = $('#subscription_' + key + '_' + key2).parsley();

                        updateData(dataField, field, val2);
                    });
                } else {
                    var dataField = $('#subscription_' + key).parsley();

                    updateData(dataField, field, val);
                }
            });

            $.each(json.errors, function (key, val) {
                if (!$.isArray(val)) {
                    $.each(val, function (key2, val2) {
                        var field = $('#subscription_' + key + '_' + key2).parsley();

                        clearErrors(field);

                        $.each(val2, function (key, error) {
                            window.ParsleyUI.addError(field, 'remote', nl2br(error));
                        });
                    });
                } else {
                    var field = $('#subscription_' + key).parsley();

                    clearErrors(field);

                    $.each(val, function (key, error) {
                        window.ParsleyUI.addError(field, 'remote', nl2br(error));
                    });
                }
            });
        },

        clearErrors = function (field) {
            window.ParsleyUI.removeError(field, 'remote');
            window.ParsleyUI.removeError(field, 'required');
            window.ParsleyUI.removeError(field, 'type');
            window.ParsleyUI.removeError(field, 'contactunique');
        };

    $(function () {
        var $form = $('#form'),
            $inputs = $form.find('input, select, textarea');

        $.listen('parsley:field:init', function (field) {
            field.$element.closest(".form-group").addClass('has-feedback');
        });

        // Setup validation
        // @todo double, help text
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
            window.ParsleyUI.removeError(field, 'remote');
            field.$element.next('i').remove();
            field.$element.after('<i class="form-control-feedback fa fa-cog fa-spin"></i>');
        }).subscribe('parsley:field:success', function (field) {
            field.$element.next('i').remove();
            field.$element.after('<i class="form-control-feedback fa fa-check"></i>');
        }).subscribe('parsley:field:error', function (field) {
            field.$element.next('i').remove();
            field.$element.after('<i class="form-control-feedback fa fa-remove"></i>');
        });

        // Show external error messages
        $form.find(':input[data-parsley-remote]').each(function () {
            var field = $(this).parsley();

            $(this).attr('data-parsley-remote-options', '{ "type": "POST" }');
            $(this).attr('data-parsley-errors-messages-disabled', 1);
            field.actualizeOptions();

            field.subscribe('parsley:field:success', function (field) {
                updateDataAndErrors(field);
            }).subscribe('parsley:field:error', function (field) {
                updateDataAndErrors(field);
            });
        });

        // Prevent caching for the metadataUrl field because the response can be different based on earlier values
        $('#subscription_metadataUrl').parsley().subscribe('parsley:field:validate', function (field) {
            field.$element.attr('data-parsley-remote-options', '{ "type": "POST", "ts": ' + Date.now() + ' }');
            field.actualizeOptions();
        });

        // A custom validator to check whether the adm. and tech. contact are not the same
        window.ParsleyValidator
            .addValidator('contactunique', function () {
                var fname1 = $('#subscription_administrativeContact_firstName').val(),
                    fname2 = $('#subscription_technicalContact_firstName').val(),
                    lname1 = $('#subscription_administrativeContact_lastName').val(),
                    lname2 = $('#subscription_technicalContact_lastName').val(),
                    email1 = $('#subscription_administrativeContact_email').val(),
                    email2 = $('#subscription_technicalContact_email').val();

                if (!fname1 || !fname2 || !lname1 || !lname2 || !email1 || !email2) {
                    return true;
                }

                return !(fname1 == fname2 && lname1 == lname2 && email1 == email2);
            }, 32)
            .addMessage('en', 'contactunique', 'The technical contact should be different from the administrative contact.')
            .addMessage('nl', 'contactunique', 'Het technisch contactpersoon moet verschillen van het administratief contactpersoon.');

        // Setup autosave
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
                            $('#status-done').addClass('hidden');
                            $('#status-progress').removeClass('hidden');
                        },
                        complete: function () {
                            $('#status-progress').addClass('hidden');
                            $('#status-done').removeClass('hidden');
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
                    $inputs.not('.disabled').prop('disabled', false);
                    $form.find('button').prop('disabled', false);
                    $('.lock-warning').hide();
                });

                lockReq.fail(function () {
                    $inputs.prop('disabled', true);
                    $form.find('button').prop('disabled', true);
                    $('.lock-warning').show();
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

        $links.on('click', function () {
            return false;
        });

        $inputs.on('focusin', function () {
            $(this).closest('.row').find('.popover-link').popover('show');
        });

        $inputs.on('focusout', function () {
            $(this).closest('.row').find('.popover-link').popover('hide');
        });

        // Setup attribute checkboxes
        $('#attributes').find(':checkbox').on('change', function () {
            var checkbox = $(this),
                textarea = checkbox.closest('.form-group').find('textarea');

            if (checkbox.is(':checked')) {
                textarea.prop('disabled', false);
                textarea.prop('required', true);
            } else {
                textarea.prop('disabled', true);
                textarea.prop('required', false);
            }
        })
    });
})(jQuery);
