(function () {
    'use strict';

    document.querySelectorAll('[data-ssz-copy-target]').forEach(function (button) {
        button.addEventListener('click', async function () {
            var report = document.getElementById(button.dataset.sszCopyTarget);
            var status = button.parentElement.querySelector('[role="status"]');
            if (!report || !status) return;

            report.focus();
            report.select();
            report.setSelectionRange(0, report.value.length);
            var copied = false;
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(report.value);
                    copied = true;
                }
            } catch (error) {
                // Local WordPress may use HTTP or deny Clipboard API access.
            }
            if (!copied) {
                try {
                    copied = document.execCommand('copy');
                } catch (error) {
                    copied = false;
                }
            }
            status.textContent = copied
                ? 'Copied the complete report.'
                : 'Report selected. Press Ctrl+C (Windows) or Command+C (Mac) to copy.';
        });
    });
}());
