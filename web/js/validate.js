(function ($, Parsley, ParsleyUI, document) {
    'use strict';

    function nlToBr(str, isXhtml) {
        var breakTag = '<br>',
            input = String(str);

        if (isXhtml || isXhtml === undefined) {
            breakTag = '<br />';
        }

        return input.replace(
            /([^>\r\n]?)(\r\n|\n\r|\r|\n)/g,
            '$1' + breakTag + '$2'
        );
    }

    function clearErrors(field) {
        var errorsWrapper = field._ui.$errorsWrapper,
            errorsWrapperChildren = errorsWrapper.children();

        errorsWrapper.removeClass('filled');
        errorsWrapperChildren.remove();

        field._ui.$errorClassHandler.removeClass(field.options.errorClass);
        field._ui.lastValidationResult = [];
        field._ui.validationInformationVisible = false;
    }

    function updateData(dataField, field, val) {
        if (dataField.$element.val() === val) {
            return;
        }

        clearErrors(dataField);

        if (dataField.$element.attr('type') === 'checkbox') {
            dataField.$element.prop('checked', val);
            dataField.$element.trigger('change');
            return;
        }

        dataField.$element.nextAll('i').remove();
        dataField.$element.nextAll('.help-block').remove();

        dataField.$element.val(val);
        dataField.$element.trigger('change');
    }

    function updateErrors(field, errors) {
        clearErrors(field);

        //noinspection JSLint
        $.each(errors, function (key, error) {
            var cleanedError = nlToBr(error);
            ParsleyUI.addError(field, 'remote', cleanedError);
        });

        ParsleyUI.manageFailingFieldTrigger(field);
    }

    function setupNextAndPrev(form) {
        form.find('.btn-next').on('click', function () {
            $('.nav-tabs .active').next().find('a').tab('show');
            return false;
        });

        form.find('.btn-prev').on('click', function () {
            $('.nav-tabs .active').prev().find('a').tab('show');
            return false;
        });
    }

    function setupHelpPopovers(links, inputs) {
        links.popover({
            container: 'body',
            trigger: 'click',
            html: true
        });

        links.on('click', function () {
            return false;
        });

        inputs.on('focusin', function () {
            $(this).closest('.row').find('.popover-link').popover('show');
        });

        links.on('show.bs.popover', function () {
            links.not(this).popover('hide');
        });

        $('.nav-tabs a').on('hide.bs.tab', function () {
            links.popover('hide');
        });
    }

    /**
     * @param {Object} field
     *
     * @todo: clean, use recursive method
     */
    function updateDataAndErrors(field, xhr) {
        var json;

        clearErrors(field);

        if (xhr === undefined || xhr.responseText === undefined) {
            return;
        }

        json = $.parseJSON(xhr.responseText);

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
            if ($.isArray(val)) {
                var errorField = $('#subscription_' + key).parsley();
                updateErrors(errorField, val);
            } else {
                $.each(val, function (key2, val2) {
                    var errorFieldNested = $('#subscription_' + key + '_' + key2).parsley();
                    updateErrors(errorFieldNested, val2);
                });
            }
        });
    }

    function setupActiveTabHistory() {
        if (location.hash.substr(0, 2) === '#!') {
            $('a[href="#' + location.hash.substr(2) + '"]').tab('show');
        }

        // Remember the hash in the URL without jumping
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            var hash = $(e.target).attr('href');
            if (hash.substr(0, 1) === '#') {
                location.replace('#!' + hash.substr(1));
            }
        });
    }

    function setupLocking(form, inputs) {
        //noinspection MagicNumberJS
        setInterval(
            function () {
                var lockReq = $.get(form.data('lock'));

                lockReq.done(function () {
                    inputs.not('.disabled').prop('disabled', false);
                    form.find('button').prop('disabled', false);
                    $('.lock-warning').hide();
                });

                lockReq.fail(function () {
                    inputs.prop('disabled', true);
                    form.find('button').prop('disabled', true);
                    $('.lock-warning').show();
                });
            },
            10000
        );
    }

    function setupAttributeCheckboxes() {
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
        });
    }

    function showDoneSaving() {
        $('#status-progress').addClass('hidden');
        $('#status-done').removeClass('hidden');
    }

    function showBusySaving() {
        $('#status-done').addClass('hidden');
        $('#status-progress').removeClass('hidden');
    }

    //noinspection JSLint
    function save(form, formData) {
        /*jshint validthis:true */
        var self = this;

        $.ajax({
            url: form.data('save'),
            data: formData,
            type: 'POST',
            beforeSend: showBusySaving,
            complete: function () {
                showDoneSaving();

                self.next('save');
            }
        });

        return false;
    }

    function setupAutoSaving(form) {
        if (!form.hasClass('autosave')) {
            return;
        }

        form.autosave({
            callbacks: {
                trigger: ['modify', 'change'],
                scope: 'all',
                save: $.debounce(500, function() {
                    save.bind(this)(form, arguments[1]);
                })
            }
        });
    }

    // A custom validator to check whether the adm. and tech. contact are not the same
    function setupUniqueContacts() {
        var validatorPriority = 32,
            validator = Parsley.addValidator(
                'contactunique',
                function () {
                    var adminEl = $('#subscription_administrativeContact_email'),
                        techEl = $('#subscription_technicalContact_email');

                    adminEl.nextAll('ul.help-block').remove();
                    techEl.nextAll('ul.help-block').remove();

                    return adminEl.val().trim() !== techEl.val().trim();
                },
                validatorPriority
            );
        validator.addMessage('en', 'contactunique', 'The technical contact should be different from the administrative contact.')
        validator.addMessage('nl', 'contactunique', 'Het technisch contactpersoon moet verschillen van het administratief contactpersoon.');
    }

    function preventFormOnEnterSubmit(form) {
        form.find('input, select').on('keypress', function (event) {
            //noinspection MagicNumberJS
            if (event.which !== 13) {
                return;
            }

            event.preventDefault();
        });
    }

    /**
     *
     * @param form
     * @todo double, help text
     */
    function setupValidation(form) {
        Parsley.on('field:init', function (field) {
            field.$element.closest('.form-group').addClass('has-feedback');
        });
        Parsley.on('form:validate', function () {
            $('#status-validating').removeClass('hidden');
        });
        Parsley.on('form:validated', function (form) {
            var tabId;
            if (form.validationResult !== true) {
                tabId = form.$element.find('.has-error').first().closest('.tab-pane').attr('id');
                $('.nav-tabs a[href="#' + tabId + '"]').tab('show');
            }
        });
        /** TODO (BaZo)
          * ugly fix: the Parsley form:validated thingy in setupValidation() is never called when publishing
          */
        var reqState=$('#subscription_requestedState').val();
        if (reqState=='published' || reqState=='finished') {
            var errorElements = form.find('.has-error');
            if (errorElements.length>0)
            {
                var tabId = errorElements.first().closest('.tab-pane').attr('id');
                $('.nav-tabs a[href="#' + tabId + '"]').tab('show');
                location.replace('#!' + tabId);
            }
        }
        /* end ugliness */


        Parsley.on('field:validate', function (field) {
            field.reset();
            field.$element.nextAll('.help-block').remove();

            window.setTimeout(function() {
                field.$element.nextAll('i').remove();
                field.$element.after('<i class="form-control-feedback fa fa-cog fa-spin"></i>');
            }, 50);
        });
        Parsley.on('field:success', function (field) {
            field.$element.nextAll('.help-block').remove();
            if (field.validationResult === true) {
                window.setTimeout(function() {
                    field.$element.nextAll('i').remove();
                    field.$element.after('<i class="form-control-feedback fa fa-check"></i>');
                }, 50);
            }
        });
        Parsley.on('field:error', function (field) {
            field.$element.nextAll('.help-block').remove();

            window.setTimeout(function(){
                field.$element.nextAll('i').remove();
                field.$element.after('<i class="form-control-feedback fa fa-remove"></i>');
            }, 50);
        });

        form.parsley({
            trigger: 'input',
            errorClass: 'has-error',
            successClass: 'has-success',
            classHandler: function (field) {
                return field.$element.closest('.form-group');
            },
            errorsWrapper: '<ul class="help-block"></ul>'
        });
    }

    function preventMetadataUrlCaching() {
        var metadataUrlParsley = $('#subscription_metadataUrl').parsley();
        metadataUrlParsley.on('parsley:field:validate', function (field) {
            field.$element.attr('data-parsley-remote-options', '{ "type": "POST", "ts": ' + Date.now() + ' }');
            field.actualizeOptions();
        });
        metadataUrlParsley.on('field:ajaxoptions', function (field, options) {
            var name = 'subscription[requestedState]';
            options.data[name] = $('input[name="' + name + '"]').val();
        });
    }

    function showExternalErrorMessages(form) {
        Parsley.addAsyncValidator(
            'default',
            function (xhr) {
                updateDataAndErrors(this, xhr);

                return !this._ui.$errorsWrapper.hasClass('filled');
            },
            $(this).data('parsley-remote')
        );
    }

    function hideSpinnerOnAjaxComplete() {
        $(document).ajaxComplete(function () {
            $('#status-validating,.fa-spin').addClass('hidden');
        });
    }

    function setupFillRequestedState() {
        $('button[type=submit]').on('click', function () {
            $('#subscription_requestedState').val(
                $(this).attr('data-requestedState')
            );
        });
    }

    function autoUpdateLogo() {
        setInterval(function() {
            var logoUrlEl = $('#subscription_logoUrl'),
                previewEl = $('#subscription_logoUrl_preview');

            if (logoUrlEl.val().trim() === '') {
                previewEl.hide();
                return;
            }

            if (previewEl.attr("src") === logoUrlEl.val()) {
                return;
            }

            previewEl.attr("src", logoUrlEl.val());
            previewEl.show();
        }, 500);
    }

    function setupUrlValidation() {
        $('input.url-validated').on('change', function() {
            var inputEl = this, formEl = $('form');

            $.ajax({
                type: "POST",
                url: formEl.data('validate-url'),
                data: {
                    "url": $(this).val(),
                    "subscription": {
                        "token": $('#subscription__token').val()
                    }
                },
                error: function(jqXHR) {
                    updateErrors(
                        $(inputEl).parsley(),
                        [jqXHR.responseText]
                    );
                },
                success: function (data) {
                    if (!data.validation) {
                        return;
                    }

                    updateErrors(
                        $(inputEl).parsley(),
                        data
                    );
                },
                dataType: 'json'
            });
        });
    }

    $(function () {
        var form = $('#form'),
            inputs = form.find('input, select, textarea'),
            links = form.find('.popover-link');

        setupValidation(form);
        setupFillRequestedState(form);
        showExternalErrorMessages(form);
        setupUniqueContacts();
        setupUrlValidation();

        preventFormOnEnterSubmit(form);
        preventMetadataUrlCaching();
        hideSpinnerOnAjaxComplete();

        setupNextAndPrev(form);
        setupActiveTabHistory();
        setupHelpPopovers(links, inputs);
        setupAttributeCheckboxes();

        setupAutoSaving(form);
        setupLocking(form, inputs);

        autoUpdateLogo();
    });
}(window.jQuery, window.Parsley, window.ParsleyUI, window.document));
