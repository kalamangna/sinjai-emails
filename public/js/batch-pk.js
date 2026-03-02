document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('spreadsheet_file');

    if (!fileInput) return;

    fileInput.addEventListener('change', async function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('spreadsheet_file', file);
        
        const expectedHeaders = 'identifier,nomor,gaji_nominal,gaji_terbilang,tanggal_kontrak_awal,tanggal_kontrak_akhir';
        formData.append('expected_headers', expectedHeaders);

        showGlobalLoading(true);

        try {
            const response = await fetch('/batch/import_generic_spreadsheet', {
                method: 'POST',
                body: formData,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal mengimpor file.');
            }

            populateTextareas(result.data, expectedHeaders.split(','));

        } catch (error) {
            alert(`Error: ${error.message}`);
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

        alert(`${data.length} baris berhasil diimpor dari file. Silakan periksa data sebelum melanjutkan.`);
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
