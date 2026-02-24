<?php $pager->setSurroundCount(2); ?>

<nav aria-label="Page navigation" class="flex items-center gap-2">
    <?php if ($pager->hasPrevious()) : ?>
        <a href="<?= $pager->getFirst() ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm no-underline" title="<?= lang('Pager.first') ?>">
            <i class="fas fa-angles-left text-[10px]"></i>
        </a>
        <a href="<?= $pager->getPrevious() ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm no-underline" title="<?= lang('Pager.previous') ?>">
            <i class="fas fa-angle-left text-[10px]"></i>
        </a>
    <?php endif ?>

    <?php foreach ($pager->links() as $link) : ?>
        <?php if ($link['active']) : ?>
            <span class="inline-flex items-center justify-center min-w-[36px] h-9 rounded-xl bg-emerald-600 border border-emerald-600 text-white text-xs font-black shadow-lg shadow-emerald-100 px-2 cursor-default">
                <?= $link['title'] ?>
            </span>
        <?php else : ?>
            <a href="<?= $link['uri'] ?>" class="inline-flex items-center justify-center min-w-[36px] h-9 rounded-xl bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm no-underline px-2">
                <?= $link['title'] ?>
            </a>
        <?php endif ?>
    <?php endforeach ?>

    <?php if ($pager->hasNext()) : ?>
        <a href="<?= $pager->getNext() ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm no-underline" title="<?= lang('Pager.next') ?>">
            <i class="fas fa-angle-right text-[10px]"></i>
        </a>
        <a href="<?= $pager->getLast() ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm no-underline" title="<?= lang('Pager.last') ?>">
            <i class="fas fa-angles-right text-[10px]"></i>
        </a>
    <?php endif ?>
</nav>