document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('spreadsheet_file');

    if (!fileInput) return;

    fileInput.addEventListener('change', async function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const form = document.getElementById('spreadsheet_import_form');
        const formData = new FormData(form || undefined);
        formData.set('spreadsheet_file', file);
        
        const expectedHeaders = 'identifier,nomor,gaji_nominal,gaji_terbilang,tanggal_kontrak_awal,tanggal_kontrak_akhir';
        formData.set('expected_headers', expectedHeaders);

        const csrfToken = (typeof window.getCsrfToken === 'function') ? window.getCsrfToken() : (window.CSRF_TOKEN || '');
        const csrfHeader = window.CSRF_HEADER || 'X-CSRF-TOKEN';
        if (csrfToken) {
            formData.set('csrf_test_name', csrfToken);
        }

        const headers = {
            "X-Requested-With": "XMLHttpRequest",
        };
        if (csrfToken) {
            headers[csrfHeader] = csrfToken;
        }

        showGlobalLoading(true);

        try {
            const targetUrl = (form && form.getAttribute('data-import-url')) ? form.getAttribute('data-import-url') : (window.BASE_URL + '/batch/import_generic_spreadsheet');
            const response = await fetch(targetUrl, {
                method: 'POST',
                body: formData,
                headers: headers
            });

            let result;
            try {
                result = await response.json();
            } catch (jsonErr) {
                throw new Error('Respon server tidak valid atau sesi telah berakhir.');
            }

            if (result && result.csrf_token) {
                window.CSRF_TOKEN = result.csrf_token;
            }

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal mengimpor file.');
            }

            populateTextareas(result.data, expectedHeaders.split(','));

        } catch (error) {
            if (typeof window.showGlobalError === 'function') {
                window.showGlobalError('Gagal Impor File', error.message);
            }
        } finally {
            showGlobalLoading(false);
            event.target.value = ''; // Reset file input
        }
    });

    function populateTextareas(data, headers) {
        if (!Array.isArray(data)) return;

        const columns = {};
        headers.forEach(header => {
            columns[header] = [];
        });

        data.forEach(row => {
            headers.forEach(header => {
                columns[header].push(row[header] || '');
            });
        });

        headers.forEach(header => {
            const textarea = document.getElementById(`${header}_input`);
            if (textarea) {
                textarea.value = columns[header].join('\n');
            }
        });

        if (typeof window.showGlobalAlert === 'function') {
            window.showGlobalAlert('Impor Berhasil', `${data.length} baris berhasil diimpor.`, 'success');
        }
    }

    function showGlobalLoading(show = true) {
        const overlay = document.getElementById('global-loading');
        if (!overlay) return;
        if (show) {
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
});
