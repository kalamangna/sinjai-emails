/**
 * Sync Helper Functions
 * Centralized JS for TTE Status and Pegawai API Synchronizations.
 */

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
     * Fetch API helper with automatic Rate Limit (429) backoff retry
     */
    async function fetchWithRateLimitRetry(url, options, maxRetries = 2, onWait = null) {
        let attempts = 0;
        let delay = 2000; // 2s initial cool-down

        while (attempts <= maxRetries) {
            try {
                const response = await fetch(url, options);
                const is429 = response.status === 429;
                
                let data = null;
                try {
                    data = await response.json();
                } catch (e) {
                    data = null;
                }

                const isRateLimited = is429 || (data && (data.code === 429 || data.is_rate_limit || (data.message && /rate\s*limit|terlalu\s*banyak/i.test(data.message))));

                if (isRateLimited && attempts < maxRetries) {
                    attempts++;
                    if (typeof onWait === 'function') {
                        onWait(attempts, maxRetries, delay);
                    }
                    await new Promise(resolve => setTimeout(resolve, delay));
                    delay *= 2; // exponential backoff (2s -> 4s)
                    continue;
                }

                return {
                    ok: response.ok,
                    status: response.status,
                    isRateLimited: isRateLimited,
                    data: data || { success: false, message: 'Respon tidak valid dari server' }
                };
            } catch (networkError) {
                if (attempts < maxRetries) {
                    attempts++;
                    if (typeof onWait === 'function') {
                        onWait(attempts, maxRetries, delay);
                    }
                    await new Promise(resolve => setTimeout(resolve, delay));
                    delay *= 2;
                    continue;
                }
                return {
                    ok: false,
                    status: 0,
                    isRateLimited: false,
                    data: { success: false, message: 'Masalah koneksi jaringan' }
                };
            }
        }
    }

    /**
     * Sync single Pegawai data
     */
    window.syncSinglePegawai = async function(nip, btn, elements = {}, email = '') {
        const originalBtnContent = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> SYNCING';
        
        const originalContents = {
            jabatan: elements.jabatan ? elements.jabatan.innerHTML : '',
            pangkat: elements.pangkat ? elements.pangkat.innerHTML : '',
            golru: elements.golru ? elements.golru.innerHTML : '',
            unit: elements.unit ? elements.unit.innerHTML : '',
            eselon: elements.eselon ? elements.eselon.textContent.trim() : ''
        };
        const defaultEselonClass = 'px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-bold uppercase border border-slate-200';

        // Render Tailwind skeleton pulse placeholders
        if (elements.jabatan) {
            elements.jabatan.innerHTML = '<div class="h-4 bg-slate-200 rounded-md animate-pulse w-48 my-0.5"></div>';
        }
        if (elements.pangkat) {
            elements.pangkat.innerHTML = '<div class="h-4 bg-slate-200 rounded-md animate-pulse w-32 my-0.5"></div>';
        }
        if (elements.golru) {
            elements.golru.innerHTML = '<div class="h-4 bg-slate-200 rounded-md animate-pulse w-16 my-0.5"></div>';
        }
        if (elements.unit) {
            elements.unit.innerHTML = '<div class="space-y-1.5 py-0.5"><div class="h-2.5 bg-slate-200 rounded animate-pulse w-24"></div><div class="h-3.5 bg-slate-200 rounded animate-pulse w-44"></div></div>';
        }
        if (elements.eselon && elements.eselonWrapper && !elements.eselonWrapper.classList.contains('hidden')) {
            elements.eselon.className = 'inline-block h-4 w-12 bg-slate-200 rounded animate-pulse align-middle';
            elements.eselon.textContent = '';
        }

        try {
            const fetchResult = await fetchWithRateLimitRetry(
                window.BASE_URL + '/email/sync_pegawai',
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'nip=' + encodeURIComponent(nip || '') + '&email=' + encodeURIComponent(email || '')
                },
                2,
                (attempt, max, waitMs) => {
                    btn.innerHTML = `<i class="fas fa-hourglass-half animate-spin mr-1"></i> COOLDOWN (${waitMs / 1000}s)`;
                }
            );

            const data = fetchResult.data;
            btn.disabled = false;
            btn.innerHTML = originalBtnContent;

            if (data.success) {
                if (data.no_data) {
                    if (elements.jabatan) elements.jabatan.innerHTML = originalContents.jabatan;
                    if (elements.pangkat) elements.pangkat.innerHTML = originalContents.pangkat;
                    if (elements.golru) elements.golru.innerHTML = originalContents.golru;
                    if (elements.unit) elements.unit.innerHTML = originalContents.unit;
                    if (elements.eselon) {
                        elements.eselon.className = defaultEselonClass;
                        elements.eselon.textContent = originalContents.eselon;
                    }
                    if (elements.eselonWrapper) {
                        if (originalContents.eselon) elements.eselonWrapper.classList.remove('hidden');
                        else elements.eselonWrapper.classList.add('hidden');
                    }

                    const errorMsg = `Pegawai dengan NIP ${nip || email} tidak terdaftar di SIMPEG.`;
                    if (typeof window.showGlobalError === 'function') {
                        window.showGlobalError('Data Tidak Ditemukan', errorMsg);
                    } else {
                        alert(errorMsg);
                    }
                    return true;
                }

                if (data.data.jabatan && elements.jabatan) {
                    elements.jabatan.textContent = data.data.jabatan;
                } else if (elements.jabatan) {
                    elements.jabatan.innerHTML = originalContents.jabatan;
                }

                if (data.data.pangkat_nama && elements.pangkat) {
                    elements.pangkat.textContent = data.data.pangkat_nama;
                } else if (elements.pangkat) {
                    elements.pangkat.innerHTML = originalContents.pangkat;
                }

                if (data.data.pangkat_golruang && elements.golru) {
                    elements.golru.textContent = data.data.pangkat_golruang;
                } else if (elements.golru) {
                    elements.golru.innerHTML = originalContents.golru;
                }

                if (elements.unit && data.data) {
                    const parentName = data.data.parent_unit_kerja_name || '';
                    const unitName = data.data.unit_kerja_name || '';
                    const unitId = data.data.unit_kerja_id;
                    const parentId = data.data.parent_unit_kerja_id;
                    if (unitName) {
                        let unitHtml = '';
                        if (parentName) {
                            unitHtml += `<a href="${window.BASE_URL}/email/unit_kerja/${parentId || unitId}" class="block no-underline group/parent">
                                <p class="text-[10px] font-bold text-slate-700 uppercase group-hover/parent:text-slate-800 transition-colors leading-none">${parentName}</p>
                            </a>`;
                        }
                        unitHtml += `<a href="${window.BASE_URL}/email/unit_kerja/${unitId}" class="block no-underline group/child">
                            <p class="text-xs font-bold text-slate-800 uppercase leading-tight group-hover/child:text-black transition-colors">${unitName}</p>
                        </a>`;
                        elements.unit.innerHTML = unitHtml;
                    } else {
                        elements.unit.innerHTML = originalContents.unit;
                    }
                }

                if (elements.eselon && elements.eselonWrapper) {
                    elements.eselon.className = defaultEselonClass;
                    if (data.data.eselon_name) {
                        elements.eselon.textContent = data.data.eselon_name;
                        elements.eselonWrapper.classList.remove('hidden');
                    } else {
                        elements.eselon.textContent = '';
                        elements.eselonWrapper.classList.add('hidden');
                    }
                }
                return true;
            } else {
                if (elements.jabatan) elements.jabatan.innerHTML = originalContents.jabatan;
                if (elements.pangkat) elements.pangkat.innerHTML = originalContents.pangkat;
                if (elements.golru) elements.golru.innerHTML = originalContents.golru;
                if (elements.unit) elements.unit.innerHTML = originalContents.unit;
                if (elements.eselon) {
                    elements.eselon.className = defaultEselonClass;
                    elements.eselon.textContent = originalContents.eselon;
                }
                if (elements.eselonWrapper) {
                    if (originalContents.eselon) elements.eselonWrapper.classList.remove('hidden');
                    else elements.eselonWrapper.classList.add('hidden');
                }

                const title = fetchResult.isRateLimited ? 'Rate Limit Terlampaui' : 'Gagal Sinkronisasi Pegawai';
                const msg = fetchResult.isRateLimited 
                    ? 'Server SIMPEG sedang membatasi frekuensi request (Rate Limit). Silakan tunggu beberapa saat sebelum mencoba kembali.'
                    : (data.message || 'Gagal mengambil data dari API');
                
                if (typeof window.showGlobalError === 'function') {
                    window.showGlobalError(title, msg);
                } else {
                    alert(msg);
                }
                return false;
            }
        } catch (error) {
            btn.disabled = false;
            btn.innerHTML = originalBtnContent;
            if (elements.jabatan) elements.jabatan.innerHTML = originalContents.jabatan;
            if (elements.pangkat) elements.pangkat.innerHTML = originalContents.pangkat;
            if (elements.golru) elements.golru.innerHTML = originalContents.golru;
            if (elements.unit) elements.unit.innerHTML = originalContents.unit;
            if (elements.eselon) {
                elements.eselon.className = defaultEselonClass;
                elements.eselon.textContent = originalContents.eselon;
            }
            if (elements.eselonWrapper) {
                if (originalContents.eselon) elements.eselonWrapper.classList.remove('hidden');
                else elements.eselonWrapper.classList.add('hidden');
            }

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
    window.syncAllPegawai = async function(btnId = 'syncAllPegawaiBtn', confirmText = 'Sinkronkan data pegawai PNS?') {
        const containers = document.querySelectorAll('[id^="pegawai-container-"]');
        const validContainers = Array.from(containers).filter(c => {
            const nip = (c.getAttribute('data-nip') || '').trim();
            const statusAsnId = (c.getAttribute('data-status-asn-id') || '').trim();
            const row = c.closest('tr');
            const rowText = (c.innerText + ' ' + (row ? row.innerText : '')).toUpperCase();
            
            const isNonPns = statusAsnId === '2' || statusAsnId === '3' || statusAsnId === '4' ||
                             rowText.includes('PPPK') || rowText.includes('NON-ASN') || rowText.includes('HONORER');

            // HANYA proses jika jelas berstatus PNS (status_asn_id === '1' dan bukan PPPK/Non-ASN)
            const isPns = statusAsnId === '1' && !isNonPns;
            return nip !== '' && isPns;
        });

        if (!validContainers.length) {
            const infoMsg = 'Hanya akun PNS yang dapat disinkronkan.';
            if (typeof window.showGlobalError === 'function') {
                window.showGlobalError('Info', infoMsg);
            } else {
                alert(infoMsg);
            }
            return;
        }

        if (!confirm(confirmText)) {
            return;
        }

        const btn = document.getElementById(btnId) || document.getElementById('batchSyncPegawaiBtn') || document.getElementById('mainSyncBtn');
        const originalBtnContent = btn ? btn.innerHTML : '';

        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-75', 'cursor-not-allowed');
        }

        let processed = 0;
        let success = 0;
        let failed = 0;

        for (const container of validContainers) {
            const statusAsnId = (container.getAttribute('data-status-asn-id') || '').trim();
            const row = container.closest('tr');
            const rowText = (container.innerText + ' ' + (row ? row.innerText : '')).toUpperCase();
            if (statusAsnId !== '1' || rowText.includes('PPPK') || rowText.includes('NON-ASN')) {
                continue;
            }

            const nip = container.getAttribute('data-nip');
            const jabatanTarget = row ? (row.querySelector('.jabatan-sync-target') || row.querySelector('.jabatan-text')) : null;
            const unitTarget = row ? row.querySelector('.unit-kerja-sync-target') : null;
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
                const fetchResult = await fetchWithRateLimitRetry(
                    window.BASE_URL + '/email/sync_pegawai',
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: 'nip=' + encodeURIComponent(nip || '') + '&email=' + encodeURIComponent(emailAttr || '')
                    },
                    2,
                    (attempt, max, waitMs) => {
                        btn.innerHTML = `<i class="fas fa-hourglass-half animate-spin mr-2"></i> Pedinginan Rate Limit (${waitMs / 1000}s)...`;
                        if (jabatanTarget) {
                            jabatanTarget.innerHTML = `<span class="text-amber-600 font-bold text-[10px] animate-pulse"><i class="fas fa-hourglass-half mr-1"></i> RATE LIMIT (${waitMs / 1000}s)</span>`;
                        }
                    }
                );

                const data = fetchResult.data;

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
                        if (fetchResult.isRateLimited) {
                            jabatanTarget.innerHTML = `${originalJabatan} <span class="ml-1 px-1.5 py-0.5 rounded text-[8px] font-bold uppercase border bg-amber-50 text-amber-700 border-amber-300" title="API Terkena Rate Limit (Silakan coba beberapa saat lagi)">RATE LIMIT</span>`;
                        } else {
                            jabatanTarget.innerHTML = `${originalJabatan} <span class="ml-1 px-1.5 py-0.5 rounded text-[8px] font-bold uppercase border bg-red-50 text-red-600 border-red-200" title="${data.message || 'Sinkronisasi Gagal'}">FAILED</span>`;
                        }
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

            // Micro-pacing delay (200ms) untuk mencegah penumpukan request ke server
            await new Promise(resolve => setTimeout(resolve, 200));
        }

        btn.innerHTML = originalBtnContent;
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-not-allowed');

        if (typeof window.showSyncResult === 'function') {
            window.showSyncResult(processed, success, failed, true);
        } else {
            alert(`Sinkronisasi Data Pegawai Selesai!\nTotal: ${processed}\nBerhasil: ${success}\nGagal: ${failed}`);
        }
    };

