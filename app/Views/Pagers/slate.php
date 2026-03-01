<?php $pager->setSurroundCount(2); ?>

<nav aria-label="Page navigation" class="flex items-center gap-1">

    <?php if ($pager->hasPrevious()) : ?>
        <a href="<?= $pager->getFirst() ?>" class="btn btn-outline !w-8 !h-8 !p-0 no-underline" title="<?= lang('Pager.first') ?>">
            <i class="fas fa-angles-left text-[10px]"></i>
        </a>
        <a href="<?= $pager->getPrevious() ?>" class="btn btn-outline !w-8 !h-8 !p-0 no-underline" title="<?= lang('Pager.previous') ?>">
            <i class="fas fa-angle-left text-[10px]"></i>
        </a>
    <?php endif ?>

    <?php foreach ($pager->links() as $link) : ?>
        <?php if ($link['active']) : ?>
            <span class="btn btn-solid min-w-[32px] !h-8 !px-2 cursor-default">
                <?= $link['title'] ?>
            </span>
        <?php else : ?>
            <a href="<?= $link['uri'] ?>" class="btn btn-outline min-w-[32px] !h-8 !px-2 no-underline text-xs font-medium">
                <?= $link['title'] ?>
            </a>
        <?php endif ?>
    <?php endforeach ?>

    <?php if ($pager->hasNext()) : ?>
        <a href="<?= $pager->getNext() ?>" class="btn btn-outline !w-8 !h-8 !p-0 no-underline" title="<?= lang('Pager.next') ?>">
            <i class="fas fa-angle-right text-[10px]"></i>
        </a>
        <a href="<?= $pager->getLast() ?>" class="btn btn-outline !w-8 !h-8 !p-0 no-underline" title="<?= lang('Pager.last') ?>">
            <i class="fas fa-angles-right text-[10px]"></i>
        </a>
    <?php endif ?>

</nav>
