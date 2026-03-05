<?php
$id_suffix = $id_suffix ?? ''; // Default to empty string if not provided
?>
<div class="relative flex-1 max-w-lg group" id="global-search-container<?= $id_suffix ?>">
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-slate-400 text-[10px] transition-colors group-focus-within:text-slate-800"></i>
        </div>
        <input 
            type="text" 
            id="global-search-input<?= $id_suffix ?>"
            class="block w-full pl-9 pr-4 py-2 bg-slate-100/50 border border-slate-200 rounded-lg text-[13px] font-bold text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-4 focus:ring-slate-100 focus:border-slate-400 transition-all uppercase tracking-tight"
            placeholder="Cari nama, NIP, atau NIK..."
            autocomplete="off"
        >
        <div id="global-search-spinner<?= $id_suffix ?>" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
            <i class="fas fa-circle-notch fa-spin text-slate-400 text-[10px]"></i>
        </div>
    </div>

    <!-- Search Results Dropdown -->
    <div 
        id="global-search-results<?= $id_suffix ?>" 
        class="absolute top-full left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden hidden z-[100] transform transition-all duration-200 origin-top scale-95 opacity-0"
    >
        <div id="results-list<?= $id_suffix ?>" class="max-h-[450px] overflow-y-auto custom-scrollbar divide-y divide-slate-100">
            <!-- Results will be injected here -->
        </div>
        <div id="no-results<?= $id_suffix ?>" class="p-10 text-center hidden">
            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-2 text-slate-300">
                <i class="fas fa-search text-sm"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">Data tidak ditemukan</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const suffix = '<?= $id_suffix ?>';
    const input = document.getElementById('global-search-input' + suffix);
    const results = document.getElementById('global-search-results' + suffix);
    const list = document.getElementById('results-list' + suffix);
    const noResults = document.getElementById('no-results' + suffix);
    const spinner = document.getElementById('global-search-spinner' + suffix);
    let debounceTimer;

    const hideResults = () => {
        results.classList.add('scale-95', 'opacity-0');
        results.classList.remove('scale-100', 'opacity-100');
        setTimeout(() => {
            results.classList.add('hidden');
        }, 200);
    };

    const showResults = () => {
        results.classList.remove('hidden');
        setTimeout(() => {
            results.classList.add('scale-100', 'opacity-100');
            results.classList.remove('scale-95', 'opacity-0');
        }, 10);
    };

    input.addEventListener('input', (e) => {
        const q = e.target.value.trim();
        clearTimeout(debounceTimer);

        if (q.length < 2) {
            hideResults();
            return;
        }

        spinner.classList.remove('hidden');

        debounceTimer = setTimeout(() => {
            fetch(`<?= site_url('email/search') ?>?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                spinner.classList.add('hidden');
                list.innerHTML = '';
                
                if (data.length > 0) {
                    noResults.classList.add('hidden');
                    data.forEach(item => {
                        const row = `
                            <a href="<?= site_url('email/detail/') ?>${item.user}" class="block p-4 hover:bg-slate-50 transition-colors no-underline group/item border-b border-slate-50 last:border-0">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-500 group-hover/item:bg-slate-800 group-hover/item:text-white transition-all shrink-0">
                                        <i class="fas fa-user text-[10px]"></i>
                                    </div>
                                    <div class="flex-grow overflow-hidden">
                                        <p class="text-sm font-black text-slate-800 uppercase tracking-tight truncate leading-none mb-1.5">${item.name}</p>
                                        <div class="flex items-center gap-2 mb-1.5 text-slate-500">
                                            <i class="fas fa-envelope text-[9px] opacity-50"></i>
                                            <span class="text-[10px] font-bold truncate lowercase">${item.email}</span>
                                        </div>
                                        <div class="flex flex-wrap gap-x-4 gap-y-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">NIP:</span>
                                                <span class="text-[10px] font-bold text-slate-700 font-mono tracking-tighter">${item.nip || '-'}</span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">NIK:</span>
                                                <span class="text-[10px] font-bold text-slate-700 font-mono tracking-tighter">${item.nik || '-'}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="h-10 flex items-center shrink-0">
                                        <i class="fas fa-chevron-right text-[8px] text-slate-300 group-hover/item:text-slate-800 transition-colors"></i>
                                    </div>
                                </div>
                            </a>
                        `;
                        list.insertAdjacentHTML('beforeend', row);
                    });
                    showResults();
                } else {
                    list.innerHTML = '';
                    noResults.classList.remove('hidden');
                    showResults();
                }
            })
            .catch(() => {
                spinner.classList.add('hidden');
                hideResults();
            });
        }, 300);
    });

    // Close on click outside
    document.addEventListener('click', (e) => {
        if (!document.getElementById('global-search-container' + suffix).contains(e.target)) {
            hideResults();
        }
    });

    // Handle ESC key
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            hideResults();
            input.blur();
        }
    });
});
</script>
