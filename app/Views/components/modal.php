<?php
/**
 * @var string $id      ID unik modal (required)
 * @var string $title   Judul modal
 * @var string $size    Ukuran: 'sm', 'md', 'lg', 'xl', 'max', 'full' (default: 'md')
 * @var bool   $showClose Menampilkan tombol tutup di header (default: true)
 * @var string $footer  Konten footer (optional)
 * @var string $content Konten utama modal (bisa dikirim via variabel atau section)
 */

$sizeClasses = [
    'sm'   => 'max-w-md',
    'md'   => 'max-w-2xl',
    'lg'   => 'max-w-4xl',
    'xl'   => 'max-w-6xl',
    'max'  => 'max-w-[95vw]',
    'full' => 'w-full h-full m-0'
];

$modalSize = $sizeClasses[$size ?? 'md'] ?? $sizeClasses['md'];
$isFullscreen = ($size ?? '') === 'full';
?>

<div id="<?= $id ?>" 
     tabindex="-1"
     aria-hidden="true"
     class="fixed top-0 left-0 right-0 z-[1000] hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full flex items-center justify-center"
     role="dialog">
    
    <!-- Modal Content -->
    <div class="relative bg-white shadow-2xl overflow-hidden flex flex-col transition-all duration-300 w-full z-50
                <?= $modalSize ?> 
                <?= $isFullscreen ? 'h-full border-0' : 'rounded-2xl border border-slate-200 my-8 max-h-[90vh]' ?>">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
            <h3 id="<?= $id ?>-title" class="text-xs font-bold text-slate-800 uppercase tracking-tight">
                <?= $title ?? '' ?>
            </h3>
            <?php if ($showClose ?? true): ?>
                <button type="button" 
                        onclick="closeModal('<?= $id ?>')" 
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1"
                        aria-label="Tutup">
                    <i class="fas fa-times text-sm"></i>
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Body -->
        <div class="p-6 overflow-y-auto custom-scrollbar flex-grow">
            <?= $content ?? '' ?>
        </div>

        <!-- Footer -->
        <?php if (isset($footer)): ?>
            <div class="bg-slate-50 p-4 border-t border-slate-100 flex justify-end shrink-0 gap-3">
                <?= $footer ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    /**
     * Modal Helper Functions (Flowbite Native Modal API Wrapper)
     */
    if (typeof openModal === 'undefined') {
        window.flowbiteModals = window.flowbiteModals || {};

        window.openModal = function(id) {
            const modalElement = document.getElementById(id);
            if (!modalElement) return;
            
            if (!window.flowbiteModals[id]) {
                const options = {
                    placement: 'center',
                    backdrop: 'dynamic',
                    backdropClasses: 'bg-slate-900/60 backdrop-blur-sm fixed inset-0 z-[990]',
                    closable: true,
                    onHide: () => {
                        modalElement.classList.add('hidden');
                        modalElement.classList.remove('flex');
                    },
                    onShow: () => {
                        modalElement.classList.remove('hidden');
                        modalElement.classList.add('flex');
                        
                        // Focus first input if exists
                        const firstInput = modalElement.querySelector('input, select, textarea, button:not([aria-label="Tutup"])');
                        if (firstInput) setTimeout(() => firstInput.focus(), 100);
                    }
                };
                window.flowbiteModals[id] = new Modal(modalElement, options);
            }
            window.flowbiteModals[id].show();
        };

        window.closeModal = function(id) {
            if (window.flowbiteModals[id]) {
                window.flowbiteModals[id].hide();
            }
        };
    }
</script>
