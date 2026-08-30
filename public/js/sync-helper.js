/**
 * Sync Helper Functions
 * Centralized JS for TTE Status and Pegawai API Synchronizations.
 */

if (typeof window.syncAllBsreStatus === 'undefined') {

    /**
     * Helper to render the BSrE status badge
     */
    window.renderBsreStatus = function(status, containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const label = (status && status.toLowerCase() !== 'not_synced') ? status : 'NOT_SYNCED';
        const colorClass = (typeof window.getJsStatusColor === 'function') 
            ? window.getJsStatusColor(label) 
            : 'bg-slate-100 text-slate-700 border-slate-200';

        container.innerHTML = `<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border ${colorClass}">${label}</span>`;
    };

    /**
     * Sync single BSrE Status
     */
    window.syncSingleBsreStatus = async function(email, containerId, btn = null) {
        const container = document.getElementById(containerId);
        if (!container) return false;

        const originalBtnContent = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>';
        }
        
        container.innerHTML = '<span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-slate-50 text-slate-400 border-slate-200 animate-pulse"><i class="fas fa-spinner fa-spin mr-1"></i> SYNCING</span>';

        try {
            const response = await fetch(window.BASE_URL + '/bsre/sync-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'email=' + encodeURIComponent(email)
            });

            const data = await response.json();
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalBtnContent;
            }

            if (data.status === 'success') {
                window.renderBsreStatus(data.bsre_status, containerId);
                return { success: true, status: data.bsre_status };
            } else {
                const errorMsg = data.message || 'Gagal';
                container.innerHTML = `<button onclick="if(typeof window.showGlobalError==='function') { window.showGlobalError('Gagal Sinkronisasi', '${errorMsg.replace(/'/g, "\\'")}'); } else { alert('${errorMsg.replace(/'/g, "\\'")}'); }" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-red-50 text-red-600 border-red-200 hover:bg-red-100 transition-colors">ERROR</button>`;
                return { success: false };
            }
        } catch (error) {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalBtnContent;
            }
            const errorMsg = 'Masalah Koneksi Jaringan';
            container.innerHTML = `<button onclick="if(typeof window.showGlobalError==='function') { window.showGlobalError('Kesalahan Jaringan', '${errorMsg}'); } else { alert('${errorMsg}'); }" class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border bg-red-50 text-red-600 border-red-200 hover:bg-red-100 transition-colors">ERROR</button>`;
            return { success: false };
        }
    };

    /**
     * Sync all BSrE status on page sequentially
     */
    window.syncAllBsreStatus = async function(btnId = 'syncAllTteBtn', confirmText = 'Sinkronkan status TTE?') {
        const containers = document.querySelectorAll('[id^="bsre-status-"]');
        if (!containers.length) return;

        if (!confirm(confirmText)) {
            return;
        }

        const syncBtn = document.getElementById(btnId);
        if (!syncBtn) return;
        const originalBtnContent = syncBtn.innerHTML;

        // 1. Scroll to table smoothly
        const tableContainer = document.getElementById('email-table-container') || document.querySelector('table');
        if (tableContainer) {
            tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // 2. Disable button
        syncBtn.disabled = true;
        syncBtn.classList.add('opacity-75', 'cursor-not-allowed');
        syncBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Syncing...';

        let processed = 0;
        let success = 0;
        let failed = 0;

        for (const container of containers) {
            const email = container.getAttribute('data-email');
            if (!email) continue;
            
            // Scroll to row
            container.scrollIntoView({ behavior: 'smooth', block: 'center' });

            syncBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Syncing ${processed}/${containers.length}...`;
            
            const result = await window.syncSingleBsreStatus(email, container.id);
            if (result && result.success) {
                success++;
            } else {
                failed++;
            }
            processed++;
        }

        // 4. Restore button
        syncBtn.disabled = false;
        syncBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        syncBtn.innerHTML = originalBtnContent;

        if (typeof window.showSyncResult === 'function') {
            window.showSyncResult(processed, success, failed);
        } else {
            alert(`Sinkronisasi Selesai!\nTotal: ${processed}\nBerhasil: ${success}\nGagal: ${failed}`);
        }
    };

    /**
     * Sync single Pegawai data
     */
    window.syncSinglePegawai = async function(nip, btn, elements = {}) {
        const originalBtnContent = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> SYNCING';
        
        Object.values(elements).forEach(el => {
            if (el) el.classList.add('animate-pulse', 'text-slate-400');
        });

        try {
            const response = await fetch(window.BASE_URL + '/email/sync_pegawai', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'nip=' + encodeURIComponent(nip)
            });

            const data = await response.json();
            btn.disabled = false;
            btn.innerHTML = originalBtnContent;
            
            Object.values(elements).forEach(el => {
                if (el) el.classList.remove('animate-pulse', 'text-slate-400');
            });

            if (data.success) {
                if (data.no_data) {
                    const errorMsg = `Pegawai dengan NIP ${nip} tidak terdaftar di SIMPEG.`;
                    if (typeof window.showGlobalError === 'function') {
                        window.showGlobalError('Data Tidak Ditemukan', errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                    return true;
                }
                if (data.data.jabatan && elements.jabatan) {
                    elements.jabatan.textContent = data.data.jabatan;
                    if (elements.jabatan.classList.contains('text-slate-400')) {
                        elements.jabatan.classList.remove('text-slate-400');
                    }
                }
                if (data.data.pangkat_nama && elements.pangkat) {
                    elements.pangkat.textContent = data.data.pangkat_nama;
                }
                if (data.data.pangkat_golruang && elements.golru) {
                    elements.golru.textContent = data.data.pangkat_golruang;
                }
                return true;
            } else {
                if (typeof window.showGlobalError === 'function') {
                    window.showGlobalError('Gagal Sinkronisasi Pegawai', data.message || 'Gagal mengambil data dari API');
                } else {
                    alert(data.message || 'Gagal mengambil data dari API');
                }
                return false;
            }
        } catch (error) {
            btn.disabled = false;
            btn.innerHTML = originalBtnContent;
            Object.values(elements).forEach(el => {
                if (el) el.classList.remove('animate-pulse', 'text-slate-400');
            });
            if (typeof window.showGlobalError === 'function') {
                window.showGlobalError('Kesalahan Jaringan', 'Gagal menghubungi server API.');
            } else {
                alert('Gagal menghubungi server API.');
            }
            return false;
        }
    };

    /**
     * Sync all Pegawai data on page sequentially
     */
    window.syncAllPegawai = async function(btnId = 'batchSyncPegawaiBtn', confirmText = 'Sinkronkan data pegawai?') {
        const containers = document.querySelectorAll('[id^="pegawai-container-"]');
        const validContainers = Array.from(containers).filter(c => c.getAttribute('data-nip') && c.getAttribute('data-nip').trim() !== '');

        if (!validContainers.length) {
            alert('Tidak ada data NIP yang dapat disinkronkan.');
            return;
        }

        if (!confirm(confirmText)) {
            return;
        }

        const btn = document.getElementById(btnId);
        if (!btn) return;
        const originalBtnContent = btn.innerHTML;

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');

        let processed = 0;
        let success = 0;
        let failed = 0;

        for (const container of validContainers) {
            const nip = container.getAttribute('data-nip');
            const row = container.closest('tr');
            const jabatanTarget = row.querySelector('.jabatan-sync-target') || row.querySelector('.jabatan-text');
            const unitTarget = row.querySelector('.unit-kerja-sync-target');
            let originalJabatan = '';
            let originalUnit = '';

            container.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            
            // Skeleton state untuk Jabatan
            if (jabatanTarget) {
                originalJabatan = jabatanTarget.getAttribute('data-original') || jabatanTarget.innerText.trim();
                jabatanTarget.setAttribute('data-original', originalJabatan);
                jabatanTarget.innerHTML = '<div class="h-3.5 bg-slate-200 rounded animate-pulse w-36 my-0.5"></div>';
            }

            // Skeleton state untuk Unit Kerja
            if (unitTarget) {
                originalUnit = unitTarget.getAttribute('data-original') || unitTarget.innerHTML;
                unitTarget.setAttribute('data-original', originalUnit);
                unitTarget.innerHTML = '<div class="space-y-1.5 py-0.5"><div class="h-2.5 bg-slate-200 rounded animate-pulse w-20"></div><div class="h-3.5 bg-slate-200 rounded animate-pulse w-32"></div></div>';
            }

            const emailAttr = row ? (row.getAttribute('data-email') || '') : '';

            try {
                const response = await fetch(window.BASE_URL + '/email/sync_pegawai', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'nip=' + encodeURIComponent(nip || '') + '&email=' + encodeURIComponent(emailAttr || '')
                });

                const data = await response.json();
                if (data.success) {
                    if (jabatanTarget) {
                        if (data.no_data) {
                            jabatanTarget.innerHTML = `<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border bg-amber-50 text-amber-600 border-amber-200" title="Data tidak ditemukan di API">NO DATA API</span>`;
                        } else {
                            const newJabatan = data.data?.jabatan || originalJabatan;
                            jabatanTarget.innerHTML = `<span class="text-emerald-600 font-bold">${newJabatan}</span>`;
                            jabatanTarget.setAttribute('data-original', newJabatan);
                        }
                    }

                    if (unitTarget && data.data) {
                        const parentName = data.data.parent_unit_kerja_name || '';
                        const unitName = data.data.unit_kerja_name || '';
                        if (unitName) {
                            let unitHtml = '';
                            if (parentName) {
                                unitHtml = `<span class="text-[10px] font-bold text-slate-700 uppercase leading-none">${parentName}</span><span class="text-xs font-bold text-slate-800 uppercase tracking-tight mt-1">${unitName}</span>`;
                            } else {
                                unitHtml = `<span class="text-xs font-bold text-slate-800 uppercase tracking-tight">${unitName}</span>`;
                            }
                            unitTarget.innerHTML = unitHtml;
                            unitTarget.setAttribute('data-original', unitHtml);
                        } else if (originalUnit) {
                            unitTarget.innerHTML = originalUnit;
                        }
                    }
                    success++;
                } else {
                    if (jabatanTarget) {
                        jabatanTarget.innerHTML = `${originalJabatan} <span class="ml-1 px-1.5 py-0.5 rounded text-[8px] font-bold uppercase border bg-red-50 text-red-600 border-red-200" title="${data.message || 'Sinkronisasi Gagal'}">FAILED</span>`;
                    }
                    if (unitTarget && originalUnit) {
                        unitTarget.innerHTML = originalUnit;
                    }
                    failed++;
                }
            } catch (error) {
                if (jabatanTarget) {
                    jabatanTarget.innerHTML = `${originalJabatan} <span class="ml-1 px-1.5 py-0.5 rounded text-[8px] font-bold uppercase border bg-red-50 text-red-600 border-red-200">ERROR</span>`;
                }
                if (unitTarget && originalUnit) {
                    unitTarget.innerHTML = originalUnit;
                }
                failed++;
            }

            processed++;
            btn.innerHTML = `<i class="fas fa-sync-alt animate-spin mr-2"></i> Sinkronisasi ${processed}/${validContainers.length}...`;
        }

        btn.innerHTML = originalBtnContent;
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-not-allowed');
        alert(`Sinkronisasi Data Pegawai Selesai!\nTotal: ${processed}\nBerhasil: ${success}\nGagal: ${failed}`);
    };
}
