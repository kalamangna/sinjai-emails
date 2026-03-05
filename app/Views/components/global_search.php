<div class="relative flex-1 max-w-lg group" id="global-search-container">
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-slate-400 text-xs transition-colors group-focus-within:text-slate-800"></i>
        </div>
        <input 
            type="text" 
            id="global-search-input"
            class="block w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-slate-800/10 focus:border-slate-800 transition-all shadow-sm"
            placeholder="Cari email, nama, NIP, atau NIK..."
            autocomplete="off"
        >
        <div id="global-search-spinner" class="absolute inset-y-0 right-0 pr-3 flex items-center hidden">
            <i class="fas fa-circle-notch fa-spin text-slate-400 text-xs"></i>
        </div>
    </div>

    <!-- Search Results Dropdown -->
    <div 
        id="global-search-results" 
        class="absolute top-full left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden hidden z-[100] transform transition-all duration-200 origin-top"
    >
        <div id="results-list" class="max-h-[400px] overflow-y-auto custom-scrollbar divide-y divide-slate-50">
            <!-- Results will be injected here -->
        </div>
        <div id="no-results" class="p-8 text-center hidden">
            <div class="w-10 h-10 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-2 text-slate-300">
                <i class="fas fa-search text-xs"></i>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Tidak ditemukan hasil</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('global-search-input');
    const results = document.getElementById('global-search-results');
    const list = document.getElementById('results-list');
    const noResults = document.getElementById('no-results');
    const spinner = document.getElementById('global-search-spinner');
    let debounceTimer;

    const hideResults = () => {
        results.classList.add('hidden');
        results.style.opacity = '0';
        results.style.transform = 'translateY(-10px)';
    };

    const showResults = () => {
        results.classList.remove('hidden');
        setTimeout(() => {
            results.style.opacity = '1';
            results.style.transform = 'translateY(0)';
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
                            <a href="<?= site_url('email/detail/') ?>${item.user}" class="block p-4 hover:bg-slate-50 transition-colors no-underline group/item">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-slate-500 group-hover/item:bg-slate-800 group-hover/item:text-white transition-all">
                                        <i class="fas fa-user text-[10px]"></i>
                                    </div>
                                    <div class="flex-grow overflow-hidden">
                                        <p class="text-xs font-bold text-slate-800 uppercase tracking-tight truncate">${item.name}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[10px] font-medium text-slate-500 truncate">${item.email}</span>
                                            <span class="text-[9px] font-bold text-slate-300 uppercase tracking-widest font-mono">/ ${item.nip || item.nik || '-'}</span>
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right text-[8px] text-slate-300 group-hover/item:text-slate-800 transition-colors"></i>
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
        if (!document.getElementById('global-search-container').contains(e.target)) {
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
