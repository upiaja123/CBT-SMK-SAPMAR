{{-- KaTeX CSS & JS --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.9/katex.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.9/katex.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/KaTeX/0.16.9/contrib/auto-render.min.js"></script>

<style>
    .math-render-area {
        word-break: break-word;
    }
    .math-render-area .katex {
        font-size: 1.05em;
        line-height: 1.2;
    }
    /* Mencegah display mode merusak layout baris pada tabel atau kartu ringkas */
    .line-clamp-1 .katex-display,
    .line-clamp-2 .katex-display,
    .line-clamp-3 .katex-display {
        display: inline-block !important;
        margin: 0 0.25rem !important;
        vertical-align: middle;
    }
    .math-render-area .katex-display {
        margin: 0.5rem 0;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 2px 0;
    }
</style>

<script>
(function() {
    // Regex mendeteksi perintah LaTeX umum yang ditulis langsung tanpa tanda dollar ($)
    const latexPattern = /(?<!\$)\\(?:frac|dfrac|tfrac|sqrt|sum|prod|int|lim|times|div|pm|mp|le|ge|leq|geq|neq|approx|equiv|alpha|beta|gamma|delta|theta|lambda|mu|pi|sigma|phi|omega|Delta|Omega|angle|circ|infty|sin|cos|tan|log|ln|partial|nabla|to|rightarrow|leftarrow)(?:\[[^\]]*\])?(?:\{(?:[^{}]|\{[^{}]*\})*\})*(?:[\^_](?:\{[^{}]*\}|[0-9a-zA-Z]))*(?!\$)/g;

    function cleanNestedMath(text) {
        // Membersihkan tanda $ bertingkat misal: $(3x)^{$\frac{2}{5}$}$ -> $(3x)^{\frac{2}{5}}$
        return text.replace(/(\^|_|\{|\()\s*\$([^\$]+)\$\s*(\}|\))/g, '$1$2$3');
    }

    function wrapBareLatexInNode(node) {
        if (!node || node.nodeType !== Node.TEXT_NODE) return;
        let text = node.nodeValue;
        if (!text) return;

        const parent = node.parentElement;
        if (!parent || parent.closest('script, style, textarea, pre, code, .katex, .katex-rendered, .ql-formula, .math-editor, .math-editor-opt, .mq-editable-field')) {
            return;
        }

        if (text.indexOf('$') !== -1) {
            text = cleanNestedMath(text);
        }

        if (text.indexOf('\\') !== -1) {
            text = text.replace(latexPattern, match => '$' + match + '$');
        }

        if (text !== node.nodeValue) {
            node.nodeValue = text;
        }
    }

    function processTextNodes(element) {
        try {
            const walker = document.createTreeWalker(
                element,
                NodeFilter.SHOW_TEXT,
                {
                    acceptNode: function(node) {
                        const p = node.parentElement;
                        if (p && p.closest('script, style, textarea, pre, code, .katex, .katex-rendered, .ql-formula, .math-editor, .math-editor-opt, .mq-editable-field')) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        return NodeFilter.FILTER_ACCEPT;
                    }
                }
            );
            const nodes = [];
            while (walker.nextNode()) {
                nodes.push(walker.currentNode);
            }
            nodes.forEach(wrapBareLatexInNode);
        } catch(e) {
            console.warn('Math text walker error:', e);
        }
    }

    window.renderAllMath = function(target) {
        if (typeof katex === 'undefined' || typeof renderMathInElement === 'undefined') {
            setTimeout(() => {
                if (typeof katex !== 'undefined' && typeof renderMathInElement !== 'undefined') {
                    window.renderAllMath(target);
                }
            }, 100);
            return;
        }

        const container = target ? ((typeof target === 'string') ? document.querySelector(target) : target) : document.body;
        if (!container) return;

        // 1. Render elemen formula khusus (.katex-rendered, [data-formula], .ql-formula[data-value])
        const formulaEls = container.querySelectorAll('.katex-rendered, [data-formula], .ql-formula');
        formulaEls.forEach(el => {
            if (el.closest('.mq-editable-field')) return;
            const formula = el.getAttribute('data-formula') || el.getAttribute('data-value');
            if (formula) {
                if (!el.querySelector('.katex-html')) {
                    try {
                        katex.render(formula, el, {
                            throwOnError: false,
                            displayMode: el.classList.contains('display-mode') || el.classList.contains('katex-display')
                        });
                    } catch (err) {
                        console.error('KaTeX formula render error:', err);
                    }
                }
            }
        });

        // 2. Wrap bare LaTeX dan perbaiki dollar bertingkat pada text nodes
        processTextNodes(container);

        // 3. Render formula matematika dengan delimiter KaTeX
        try {
            renderMathInElement(container, {
                delimiters: [
                    {left: '$$', right: '$$', display: true},
                    {left: '$', right: '$', display: false},
                    {left: '\\[', right: '\\]', display: true},
                    {left: '\\(', right: '\\)', display: false}
                ],
                ignoredTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code', 'option'],
                ignoredClasses: ['math-editor', 'math-editor-opt', 'mq-editable-field'],
                throwOnError: false
            });
        } catch (err) {
            console.error('KaTeX renderMathInElement error:', err);
        }
    };

    // Jalankan saat DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            window.renderAllMath();
        });
    } else {
        window.renderAllMath();
    }

    // Jalankan setelah Alpine.js inisialisasi
    document.addEventListener('alpine:initialized', function() {
        setTimeout(() => window.renderAllMath(), 100);
    });

    // Jalankan kembali saat halaman selesai loading sepenuhnya
    window.addEventListener('load', function() {
        setTimeout(() => window.renderAllMath(), 50);
    });
})();
</script>
