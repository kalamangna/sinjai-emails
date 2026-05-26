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
     class="fixed inset-0 z-[1000] hidden transition-all duration-300 <?= $isFullscreen ? 'm-0' : 'flex items-center justify-center px-4' ?>"
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="<?= $id ?>-title">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeModal('<?= $id ?>')"></div>

    <!-- Modal Content -->
    <div class="relative bg-white shadow-2xl overflow-hidden flex flex-col transition-all duration-300 w-full
                <?= $modalSize ?> 
                <?= $isFullscreen ? 'h-full' : 'rounded-2xl border border-slate-200 my-8 max-h-[90vh]' ?>">
        
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
     * Modal Helper Functions
     * Will only be defined once if multiple modals use this component.
     */
    if (typeof openModal === 'undefined') {
        window.openModal = function(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Focus first input if exists
            const firstInput = modal.querySelector('input, select, textarea, button:not([aria-label="Tutup"])');
            if (firstInput) setTimeout(() => firstInput.focus(), 100);
        };

        window.closeModal = function(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            
            modal.classList.add('hidden');
            
            // Check if any other modal is still open
            const openModals = document.querySelectorAll('[role="dialog"]:not(.hidden)');
            if (openModals.length === 0) {
                document.body.style.overflow = '';
            }
        };

        // Close on click outside (backdrop)
        document.addEventListener('click', (e) => {
            if (e.target.hasAttribute('role') && e.target.getAttribute('role') === 'dialog') {
                closeModal(e.target.id);
            }
        });

        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('[role="dialog"]:not(.hidden)');
                if (openModal) closeModal(openModal.id);
            }
        });
    }
</script>
