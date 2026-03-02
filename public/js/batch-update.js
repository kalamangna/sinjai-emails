document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('spreadsheet_file');

    if (!fileInput) return;

    fileInput.addEventListener('change', async function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('spreadsheet_file', file);
        
        // Define the expected headers for this specific import
        const expectedHeaders = 'identifier,name,nik,nip,jabatan,golongan,pendidikan,gelar_depan,gelar_belakang,tempat_lahir,tanggal_lahir,unit_kerja_id';
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

        // Create an object to hold the column data
        const columns = {};
        headers.forEach(header => {
            columns[header] = [];
        });

        // Populate the columns object
        data.forEach(row => {
            headers.forEach(header => {
                columns[header].push(row[header] || '');
            });
        });

        // Populate the textareas
        headers.forEach(header => {
            const textarea = document.getElementById(`${header}_input`);
            if (textarea) {
                textarea.value = columns[header].join('\n');
            }
        });

        alert(`${data.length} baris berhasil diimpor dari file. Silakan periksa data sebelum melanjutkan.`);
    }

    // This function needs to be available globally or defined in this file
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
