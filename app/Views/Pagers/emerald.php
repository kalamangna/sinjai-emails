<?php $pager->setSurroundCount(2); ?>

<nav aria-label="Page navigation" class="flex items-center gap-1">
    <?php if ($pager->hasPrevious()) : ?>
        <a href="<?= $pager->getFirst() ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 transition-all shadow-sm no-underline" title="<?= lang('Pager.first') ?>">
            <i class="fas fa-angles-left text-[10px]"></i>
        </a>
        <a href="<?= $pager->getPrevious() ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 transition-all shadow-sm no-underline" title="<?= lang('Pager.previous') ?>">
            <i class="fas fa-angle-left text-[10px]"></i>
        </a>
    <?php endif ?>

    <?php foreach ($pager->links() as $link) : ?>
        <?php if ($link['active']) : ?>
            <span class="inline-flex items-center justify-center min-w-[32px] h-8 rounded-lg bg-gray-900 border border-gray-900 text-white text-xs font-bold shadow-sm px-2 cursor-default">
                <?= $link['title'] ?>
            </span>
        <?php else : ?>
            <a href="<?= $link['uri'] ?>" class="inline-flex items-center justify-center min-w-[32px] h-8 rounded-lg bg-white border border-gray-300 text-gray-700 text-xs font-medium hover:bg-gray-50 transition-all shadow-sm no-underline px-2">
                <?= $link['title'] ?>
            </a>
        <?php endif ?>
    <?php endforeach ?>

    <?php if ($pager->hasNext()) : ?>
        <a href="<?= $pager->getPrevious() ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 transition-all shadow-sm no-underline" title="<?= lang('Pager.next') ?>">
            <i class="fas fa-angle-right text-[10px]"></i>
        </a>
        <a href="<?= $pager->getLast() ?>" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-white border border-gray-300 text-gray-500 hover:bg-gray-50 transition-all shadow-sm no-underline" title="<?= lang('Pager.last') ?>">
            <i class="fas fa-angles-right text-[10px]"></i>
        </a>
    <?php endif ?>
</nav>