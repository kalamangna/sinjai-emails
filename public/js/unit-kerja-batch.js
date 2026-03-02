document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('spreadsheet_file');
    const resultsTableBody = document.querySelector('#results_table tbody');
    const submitBtn = document.getElementById('submit_btn');
    let importedData = [];

    if (fileInput) {
        fileInput.addEventListener('change', async function(event) {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('spreadsheet_file', file);
            
            // Define the expected headers for Unit Kerja
            const expectedHeaders = 'nama_unit_kerja,parent_id';
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

                importedData = result.data;
                renderPreview(importedData);
                submitBtn.disabled = false;

            } catch (error) {
                alert(`Error: ${error.message}`);
            } finally {
                showGlobalLoading(false);
                event.target.value = ''; // Reset file input
            }
        });
    }

    function renderPreview(data) {
        resultsTableBody.innerHTML = '';
        if (data.length === 0) {
            resultsTableBody.innerHTML = '<tr><td colspan="3" class="px-6 py-10 text-center italic text-slate-400 font-medium uppercase tracking-widest text-[10px]">Data tidak ditemukan</td></tr>';
            return;
        }

        data.forEach((row, index) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 transition-colors';
            tr.innerHTML = `
                <td class="px-6 py-4 font-mono text-slate-700 text-[10px]">#${index + 1}</td>
                <td class="px-6 py-4 font-bold text-slate-800 uppercase tracking-tight">${row.nama_unit_kerja || '-'}</td>
                <td class="px-6 py-4 text-slate-600">${row.parent_id || '<span class="text-[10px] font-bold text-slate-400 tracking-widest uppercase">Root</span>'}</td>
            `;
            resultsTableBody.appendChild(tr);
        });
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', async function() {
            if (importedData.length === 0) return;

            if (!confirm(`Simpan ${importedData.length} data Unit Kerja?`)) return;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';

            try {
                const response = await fetch('/unit_kerja/process_batch_create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(importedData)
                });

                const result = await response.json();

                if (result.success) {
                    alert(`Berhasil: ${result.summary.success}, Gagal: ${result.summary.fail}`);
                    window.location.href = '/unit_kerja/manage';
                } else {
                    alert('Gagal memproses batch: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                alert('Error: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save mr-2 text-white/80"></i> Simpan Batch';
            }
        });
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
