<?php
use CodeIgniter\HTTP\Header;
use CodeIgniter\CodeIgniter;

$errorId = uniqid('error', true);
?>
<!doctype html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <title><?= esc($title) ?> | Sistem Identitas Digital</title>

    <!-- Tailwind CSS (Local Build) -->
    <link href="/css/output.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .sf-dump-str, .sf-dump-key {
            font-family: 'Fira Code', monospace !important;
        }

        .source-code {
            font-family: 'Fira Code', monospace;
            @apply bg-slate-800 text-sm p-4 rounded-lg overflow-x-auto;
        }
        .source-code .line {
            @apply block -mx-4 px-4;
        }
        .source-code .line.highlight {
            @apply bg-red-900/50;
        }
        .source-code .line-number {
            @apply inline-block w-10 text-right text-slate-500 mr-4 select-none;
        }
        .source-code .line-number.highlight {
            @apply text-red-400;
        }
        .source-code .default, .source-code .keyword, .source-code .string, .source-code .html, .source-code .comment {
            font-family: 'Fira Code', monospace;
        }
        .source-code .default { color: #E0E2E4; }
        .source-code .comment { color: #7d899e; }
        .source-code .string { color: #A5D6FF; }
        .source-code .keyword { color: #FF7B72; }
        .source-code .html { color: #89DDFF; }

        .tabs {
            @apply flex items-center gap-1 border-b border-slate-200 mt-12 mb-6;
        }
        .tabs a {
            @apply px-4 py-2 text-sm font-bold text-slate-700 uppercase tracking-tight -mb-px border-b-2 border-transparent hover:border-slate-800 hover:text-slate-800 transition-all no-underline;
        }
        .tabs a.active {
            @apply border-slate-800 text-slate-800;
        }

        .tab-content .content {
            @apply hidden;
        }
        .tab-content .content.active {
            @apply block;
        }

        .trace li {
            @apply bg-white border border-slate-200 rounded-lg p-4 mb-4;
        }
        .trace .trace-file {
            @apply text-sm font-bold text-slate-800;
        }
        .trace .trace-class, .trace .trace-type, .trace .trace-function {
            @apply text-slate-600;
        }
        .trace .trace-class { @apply font-medium; }
        .trace .args-btn {
            @apply text-xs text-slate-500 hover:text-slate-800 transition-colors cursor-pointer;
        }
        .trace .args {
            @apply hidden mt-4 pt-4 border-t border-slate-200;
        }
        .trace .args pre {
            @apply bg-slate-50 p-2 rounded-md text-xs;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-inter">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="bg-white border border-red-200 rounded-2xl p-8 shadow-2xl">
            <div class="flex items-start gap-6">
                <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                    <i class="fas fa-bug text-3xl"></i>
                </div>
                <div class="flex-grow">
                    <h1 class="text-2xl font-bold text-slate-800 uppercase tracking-tight leading-tight">
                        <?= esc($title), esc($exception->getCode() ? ' #' . $exception->getCode() : '') ?>
                    </h1>
                    <p class="text-slate-600 mt-2 text-lg">
                        <?= nl2br(esc($exception->getMessage())) ?>
                        <a href="https://www.duckduckgo.com/?q=<?= urlencode($title . ' ' . preg_replace('#\'.*\'|".*"#Us', '', $exception->getMessage())) ?>"
                           rel="noreferrer" target="_blank" class="text-slate-800 hover:text-red-600 font-bold no-underline transition-colors">
                           <i class="fas fa-search text-xs ml-2"></i> Cari
                        </a>
                    </p>
                </div>
            </div>
            
            <div class="mt-6 pt-6 border-t border-red-200">
                <p class="text-sm font-bold text-slate-800 mb-2">
                    <i class="fas fa-map-marker-alt mr-2 text-slate-700"></i>
                    <?= esc(clean_path($file)) ?> <span class="text-slate-500">at line</span> <?= esc($line) ?>
                </p>

                <?php if (is_file($file)) : ?>
                    <div class="source-code">
                        <?= static::highlightFile($file, $line, 15); ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php
            $last = $exception;
            while ($prevException = $last->getPrevious()) :
                $last = $prevException;
            ?>
            <div class="mt-4 pt-4 border-t border-red-100">
                <p class="text-sm text-slate-600">
                    <strong class="text-slate-800">Caused by:</strong>
                    <?= esc($prevException::class), esc($prevException->getCode() ? ' #' . $prevException->getCode() : '') ?>
                </p>
                <p class="text-slate-600">
                    <?= nl2br(esc($prevException->getMessage())) ?>
                    <a href="https://www.duckduckgo.com/?q=<?= urlencode($prevException::class . ' ' . preg_replace('#\'.*\'|".*"#Us', '', $prevException->getMessage())) ?>"
                       rel="noreferrer" target="_blank" class="text-slate-800 hover:text-red-600 font-bold no-underline transition-colors">
                       <i class="fas fa-search text-xs ml-2"></i> Cari
                    </a>
                </p>
                <p class="text-xs text-slate-500 mt-1"><?= esc(clean_path($prevException->getFile()) . ':' . $prevException->getLine()) ?></p>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE) : ?>
        <div class="mt-8">
            <ul class="tabs" id="tabs">
                <li><a href="#backtrace" class="active">Backtrace</a></li>
                <li><a href="#server">Server</a></li>
                <li><a href="#request">Request</a></li>
                <li><a href="#response">Response</a></li>
                <li><a href="#files">Files</a></li>
                <li><a href="#memory">Memory</a></li>
            </ul>

            <div class="tab-content">
                <!-- Backtrace -->
                <div class="content active" id="backtrace">
                    <ol class="trace">
                    <?php foreach ($trace as $index => $row) : ?>
                        <li>
                            <p class="trace-file">
                                <?php if (isset($row['file']) && is_file($row['file'])) : ?>
                                    <?php
                                    if (isset($row['function']) && in_array($row['function'], ['include', 'include_once', 'require', 'require_once'], true)) {
                                        echo esc($row['function'] . ' ' . clean_path($row['file']));
                                    } else {
                                        echo esc(clean_path($row['file']) . ' : ' . $row['line']);
                                    }
                                    ?>
                                <?php else: ?>
                                    {PHP internal code}
                                <?php endif; ?>
                            </p>

                            <p class="mt-1">
                                <?php if (isset($row['class'])) : ?>
                                    <span class="trace-class"><?= esc($row['class']) ?></span><span class="trace-type"><?= esc($row['type']) ?></span><span class="trace-function"><?= esc($row['function']) ?></span>
                                    <?php if (! empty($row['args'])) : ?>
                                        <?php $argsId = $errorId . 'args' . $index ?>
                                        ( <span onclick="return toggle('<?= esc($argsId, 'attr') ?>');" class="args-btn">arguments</span> )
                                        <div class="args" id="<?= esc($argsId, 'attr') ?>">
                                            <?php
                                            // Use Symfony VarDumper to display arguments
                                            foreach ($row['args'] as $key => $value) {
                                                echo '<div class="font-mono text-xs mt-2">';
                                                echo '<strong class="text-slate-500">Argument ' . ($key+1) . ':</strong>';
                                                dump($value);
                                                echo '</div>';
                                            }
                                            ?>
                                        </div>
                                    <?php else : ?>
                                        ()
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if (! isset($row['class']) && isset($row['function'])) : ?>
                                    <span class="trace-function"><?= esc($row['function']) ?>()</span>
                                <?php endif; ?>
                            </p>

                            <?php if (isset($row['file']) && is_file($row['file']) && isset($row['class'])) : ?>
                                <div class="mt-4 pt-4 border-t border-slate-200">
                                    <div class="source-code">
                                        <?= static::highlightFile($row['file'], $row['line']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    </ol>
                </div>

                <!-- Server -->
                <div class="content" id="server">
                    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden">
                    <?php foreach (['_SERVER', '_SESSION'] as $var) : ?>
                        <?php if (empty($GLOBALS[$var]) || ! is_array($GLOBALS[$var])) continue; ?>
                        <div class="p-4 border-b border-slate-200">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight mb-2">$<?= esc($var) ?></h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead><tr class="bg-slate-50"><th class="p-2 text-left font-bold">Key</th><th class="p-2 text-left font-bold">Value</th></tr></thead>
                                    <tbody class="divide-y divide-slate-100">
                                    <?php foreach ($GLOBALS[$var] as $key => $value) : ?>
                                        <tr>
                                            <td class="p-2 align-top font-mono text-xs w-1/4"><?= esc($key) ?></td>
                                            <td class="p-2 align-top"><?php dump($value); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endforeach ?>
                    </div>
                </div>

                <!-- Request -->
                <div class="content" id="request">
                    <!-- ... simplified ... -->
                </div>
                <!-- Response -->
                <div class="content" id="response">
                    <!-- ... simplified ... -->
                </div>
                <!-- Files -->
                <div class="content" id="files">
                    <div class="bg-white border border-slate-200 rounded-lg shadow-sm p-4">
                        <ol class="list-decimal list-inside text-sm font-mono">
                        <?php foreach (get_included_files() as $file) :?>
                            <li><?= esc(clean_path($file)) ?></li>
                        <?php endforeach ?>
                        </ol>
                    </div>
                </div>

                <!-- Memory -->
                <div class="content" id="memory">
                    <!-- ... simplified ... -->
                </div>
            </div>
        </div>
        <?php endif; ?>

        <footer class="py-6 px-6 text-center mt-12">
            <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                Displayed at <?= esc(\CodeIgniter\I18n\Time::now('Asia/Makassar')->format('H:i:s')) ?> &mdash; PHP: <?= esc(PHP_VERSION) ?> &mdash; CI: <?= esc(CodeIgniter::CI_VERSION) ?> &mdash; ENV: <?= ENVIRONMENT ?>
            </p>
        </footer>
    </div>

    <script>
        function init() {
            var tabs = document.querySelectorAll('.tabs a');
            var contents = document.querySelectorAll('.tab-content .content');

            tabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();

                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    contents.forEach(c => c.classList.remove('active'));
                    document.querySelector(this.getAttribute('href')).classList.add('active');
                });
            });
        }
        
        function toggle(id) {
            var el = document.getElementById(id);
            el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
            return false;
        }

        document.addEventListener('DOMContentLoaded', init);
    </script>
</body>
</html>
