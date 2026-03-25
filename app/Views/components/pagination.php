<?php
/**
 * Reusable Pagination Component
 * 
 * @var \CodeIgniter\Pager\Pager $pager
 * @var array $items The items on the current page
 * @var string|null $label The label for the data (e.g. "Pegawai", "Akun")
 */
?>

<?php if (!empty($items) && isset($pager)): ?>
    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
            <?php
            $start = ($pager->getCurrentPage() - 1) * $pager->getPerPage() + 1;
            $end = $start + count($items) - 1;
            $total = $pager->getTotal();
            ?>
            Menampilkan <span class="text-slate-800"><?= number_format($start, 0, ',', '.') ?> - <?= number_format($end, 0, ',', '.') ?></span> dari <span class="text-slate-800"><?= number_format($total, 0, ',', '.') ?></span> <?= $label ?? 'Data' ?>
        </div>
        <?php if ($pager->getPageCount() > 1): ?>
            <div class="pagination-container">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
