<?php $pager->setSurroundCount(2); ?>
<nav aria-label="Page navigation">
    <div class="inline-flex -space-x-px rounded-xl shadow-2xl border border-slate-800 overflow-hidden">
        <?php if ($pager->hasPrevious()) : ?>
            <a href="<?= $pager->getFirst() ?>" class="relative inline-flex items-center bg-slate-900 px-3 py-2 text-slate-500 hover:bg-slate-800 hover:text-slate-200 transition-all no-underline border-r border-slate-800" title="<?= lang('Pager.first') ?>">
                <i class="fas fa-angles-left text-xs"></i>
            </a>
            <a href="<?= $pager->getPrevious() ?>" class="relative inline-flex items-center bg-slate-900 px-3 py-2 text-slate-500 hover:bg-slate-800 hover:text-slate-200 transition-all no-underline border-r border-slate-800" title="<?= lang('Pager.previous') ?>">
                <i class="fas fa-angle-left text-xs"></i>
            </a>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <a href="<?= $link['uri'] ?>" class="relative inline-flex items-center px-4 py-2 text-xs font-black <?= $link['active'] ? 'z-10 bg-blue-600 text-white shadow-lg shadow-blue-900/40' : 'bg-slate-900 text-slate-400 hover:bg-slate-800 hover:text-slate-200' ?> transition-all no-underline border-r border-slate-800 last:border-r-0">
                <?= $link['title'] ?>
            </a>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <a href="<?= $pager->getNext() ?>" class="relative inline-flex items-center bg-slate-900 px-3 py-2 text-slate-500 hover:bg-slate-800 hover:text-slate-200 transition-all no-underline border-l border-slate-800" title="<?= lang('Pager.next') ?>">
                <i class="fas fa-angle-right text-xs"></i>
            </a>
            <a href="<?= $pager->getLast() ?>" class="relative inline-flex items-center bg-slate-900 px-3 py-2 text-slate-500 hover:bg-slate-800 hover:text-slate-200 transition-all no-underline border-l border-slate-800" title="<?= lang('Pager.last') ?>">
                <i class="fas fa-angles-right text-xs"></i>
            </a>
        <?php endif ?>
    </div>
</nav>