$(function () {
    if (typeof $.nette === 'undefined') {
        return;
    }

    if (window.__oazaNetteAjaxInitialized === true) {
        return;
    }

    $.nette.init();
    window.__oazaNetteAjaxInitialized = true;
});
