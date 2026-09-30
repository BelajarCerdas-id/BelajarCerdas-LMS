const editorInstances = []; // 1. DEKLARASI GLOBAL untuk semua instance CKEditor
const previousImageUrlsMap = {};
let currentActiveEditor = null; // Menyimpan instance CKEditor yang sedang aktif

/**
 * Konversi tag MathML (<math>...</math>) menjadi sintaks LaTeX inline \\( ... \\)
 * Ini mencegah CKEditor menghapus tag MathML dan membuat MathJax dapat me-render rumus secara sempurna.
 */
function convertMathMlToLatex(html) {
    if (!html || typeof html !== 'string' || !html.includes('<math')) {
        return html;
    }

    try {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const mathElements = doc.querySelectorAll('math');

        if (!mathElements.length) return html;

        function cleanText(t) {
            return (t || '').trim();
        }

        function mmlNodeToLatex(node) {
            if (!node) return '';
            if (node.nodeType === Node.TEXT_NODE) {
                return cleanText(node.nodeValue);
            }
            if (node.nodeType !== Node.ELEMENT_NODE) {
                return '';
            }

            const tag = node.tagName.toLowerCase();
            const childList = Array.from(node.childNodes).filter(n => {
                if (n.nodeType === Node.TEXT_NODE) return n.nodeValue.trim().length > 0;
                return n.nodeType === Node.ELEMENT_NODE;
            });

            const childrenToLatex = () => childList.map(mmlNodeToLatex).filter(Boolean).join(' ');

            switch (tag) {
                case 'math': {
                    const content = childrenToLatex();
                    const isDisplay = node.getAttribute('display') === 'block';
                    return isDisplay ? `\\[ ${content} \\]` : `\\( ${content} \\)`;
                }
                case 'mrow':
                case 'mstyle':
                case 'mpadded':
                case 'mphantom':
                    return childrenToLatex();

                case 'mfrac': {
                    const num = childList[0] ? mmlNodeToLatex(childList[0]) : '';
                    const den = childList[1] ? mmlNodeToLatex(childList[1]) : '';
                    return `\\frac{${num}}{${den}}`;
                }
                case 'msqrt':
                    return `\\sqrt{${childrenToLatex()}}`;

                case 'mroot': {
                    const base = childList[0] ? mmlNodeToLatex(childList[0]) : '';
                    const index = childList[1] ? mmlNodeToLatex(childList[1]) : '';
                    return `\\sqrt[${index}]{${base}}`;
                }
                case 'msup': {
                    const base = childList[0] ? mmlNodeToLatex(childList[0]) : '';
                    const sup = childList[1] ? mmlNodeToLatex(childList[1]) : '';
                    return `{${base}}^{${sup}}`;
                }
                case 'msub': {
                    const base = childList[0] ? mmlNodeToLatex(childList[0]) : '';
                    const sub = childList[1] ? mmlNodeToLatex(childList[1]) : '';
                    return `{${base}}_{${sub}}`;
                }
                case 'msubsup': {
                    const base = childList[0] ? mmlNodeToLatex(childList[0]) : '';
                    const sub = childList[1] ? mmlNodeToLatex(childList[1]) : '';
                    const sup = childList[2] ? mmlNodeToLatex(childList[2]) : '';
                    return `{${base}}_{${sub}}^{${sup}}`;
                }
                case 'munder': {
                    const base = childList[0] ? mmlNodeToLatex(childList[0]) : '';
                    const under = childList[1] ? mmlNodeToLatex(childList[1]) : '';
                    return `\\underset{${under}}{${base}}`;
                }
                case 'mover': {
                    const base = childList[0] ? mmlNodeToLatex(childList[0]) : '';
                    const over = childList[1] ? mmlNodeToLatex(childList[1]) : '';
                    return `\\overset{${over}}{${base}}`;
                }
                case 'munderover': {
                    const base = childList[0] ? mmlNodeToLatex(childList[0]) : '';
                    const under = childList[1] ? mmlNodeToLatex(childList[1]) : '';
                    const over = childList[2] ? mmlNodeToLatex(childList[2]) : '';
                    return `{${base}}_{${under}}^{${over}}`;
                }
                case 'mo': {
                    const op = cleanText(node.textContent);
                    const opMap = {
                        '×': '\\times',
                        '÷': '\\div',
                        '±': '\\pm',
                        '∓': '\\mp',
                        '≤': '\\le',
                        '≥': '\\ge',
                        '≠': '\\neq',
                        '≈': '\\approx',
                        '≡': '\\equiv',
                        '·': '\\cdot',
                        '∑': '\\sum',
                        '∏': '\\prod',
                        '∫': '\\int',
                        '∞': '\\infty',
                        '√': '\\sqrt',
                        '→': '\\rightarrow',
                        '←': '\\leftarrow',
                        '↔': '\\leftrightarrow',
                        '⇒': '\\Rightarrow',
                        '⇐': '\\Leftarrow',
                        '⇔': '\\Leftrightarrow',
                        '∈': '\\in',
                        '∉': '\\notin',
                        '⊂': '\\subset',
                        '⊆': '\\subseteq',
                        '∩': '\\cap',
                        '∪': '\\cup',
                        '∠': '\\angle',
                        '⊥': '\\perp',
                        '°': '^\\circ'
                    };
                    return opMap[op] ? `${opMap[op]} ` : op;
                }
                case 'mi': {
                    const id = cleanText(node.textContent);
                    const greekMap = {
                        'α': '\\alpha', 'β': '\\beta', 'γ': '\\gamma', 'δ': '\\delta',
                        'ε': '\\epsilon', 'θ': '\\theta', 'λ': '\\lambda', 'μ': '\\mu',
                        'π': '\\pi', 'ρ': '\\rho', 'σ': '\\sigma', 'τ': '\\tau',
                        'φ': '\\phi', 'ω': '\\omega', 'Δ': '\\Delta', 'Ω': '\\Omega',
                        'sin': '\\sin', 'cos': '\\cos', 'tan': '\\tan',
                        'log': '\\log', 'ln': '\\ln', 'lim': '\\lim'
                    };
                    return greekMap[id] ? `${greekMap[id]} ` : id;
                }
                case 'mn':
                    return cleanText(node.textContent);

                case 'mtext':
                    return `\\text{${node.textContent}}`;

                case 'mspace':
                    return ' ';

                case 'mtable': {
                    const rows = childList.map(mmlNodeToLatex).filter(Boolean);
                    return `\\begin{matrix} ${rows.join(' \\\\ ')} \\end{matrix}`;
                }
                case 'mtr': {
                    const cells = childList.map(mmlNodeToLatex).filter(Boolean);
                    return cells.join(' & ');
                }
                case 'mtd':
                    return childrenToLatex();

                default:
                    return childrenToLatex();
            }
        }

        mathElements.forEach(mathEl => {
            const latex = mmlNodeToLatex(mathEl);
            const textNode = doc.createTextNode(latex);
            mathEl.parentNode.replaceChild(textNode, mathEl);
        });

        return doc.body.innerHTML;
    } catch (e) {
        console.error('Error converting MathML to LaTeX:', e);
        return html;
    }
}

const EQUATION_PALETTE = {
    basic: [
        { label: '\\frac{a}{b}', code: '\\frac{a}{b}', title: 'Pecahan' },
        { label: 'x^{2}', code: 'x^{2}', title: 'Pangkat' },
        { label: 'x_{1}', code: 'x_{1}', title: 'Indeks / Subskrip' },
        { label: 'x_{1}^{2}', code: 'x_{1}^{2}', title: 'Subskrip & Pangkat' },
        { label: '\\sqrt{x}', code: '\\sqrt{x}', title: 'Akar Kuadrat' },
        { label: '\\sqrt[n]{x}', code: '\\sqrt[n]{x}', title: 'Akar Pangkat n' },
        { label: '\\left( \\frac{a}{b} \\right)', code: '\\left( \\frac{a}{b} \\right)', title: 'Kurung Dinamis' },
        { label: '|x|', code: '|x|', title: 'Nilai Mutlak' }
    ],
    operators: [
        { label: '\\times', code: '\\times ', title: 'Kali' },
        { label: '\\div', code: '\\div ', title: 'Bagi' },
        { label: '\\pm', code: '\\pm ', title: 'Plus-Minus' },
        { label: '\\mp', code: '\\mp ', title: 'Minus-Plus' },
        { label: '\\cdot', code: '\\cdot ', title: 'Titik Perkalian' },
        { label: '\\neq', code: '\\neq ', title: 'Tidak Sama Dengan' },
        { label: '\\le', code: '\\le ', title: 'Kurang Dari atau Sama Dengan' },
        { label: '\\ge', code: '\\ge ', title: 'Lebih Dari atau Sama Dengan' },
        { label: '\\approx', code: '\\approx ', title: 'Mendekati / Kira-kira' },
        { label: '\\equiv', code: '\\equiv ', title: 'Ekuivalen' },
        { label: '\\infty', code: '\\infty ', title: 'Tak Terhingga' },
        { label: '^\\circ', code: '^\\circ', title: 'Derajat' }
    ],
    greek: [
        { label: '\\alpha', code: '\\alpha ', title: 'Alpha' },
        { label: '\\beta', code: '\\beta ', title: 'Beta' },
        { label: '\\gamma', code: '\\gamma ', title: 'Gamma' },
        { label: '\\theta', code: '\\theta ', title: 'Theta' },
        { label: '\\pi', code: '\\pi ', title: 'Pi' },
        { label: '\\lambda', code: '\\lambda ', title: 'Lambda' },
        { label: '\\mu', code: '\\mu ', title: 'Mu' },
        { label: '\\sigma', code: '\\sigma ', title: 'Sigma' },
        { label: '\\omega', code: '\\omega ', title: 'Omega' },
        { label: '\\phi', code: '\\phi ', title: 'Phi' },
        { label: '\\Delta', code: '\\Delta ', title: 'Delta' },
        { label: '\\Omega', code: '\\Omega ', title: 'Omega' }
    ],
    advanced: [
        { label: '\\int_{a}^{b} f(x) dx', code: '\\int_{a}^{b} f(x) \\, dx', title: 'Integral Tertentu' },
        { label: '\\int f(x) dx', code: '\\int f(x) \\, dx', title: 'Integral Tak Tentu' },
        { label: '\\sum_{i=1}^{n} x_i', code: '\\sum_{i=1}^{n} x_i', title: 'Sigma / Deret' },
        { label: '\\lim_{x \\to 0}', code: '\\lim_{x \\to 0}', title: 'Limit' },
        { label: '\\sin(x)', code: '\\sin(x)', title: 'Sinus' },
        { label: '\\cos(x)', code: '\\cos(x)', title: 'Cosinus' },
        { label: '\\tan(x)', code: '\\tan(x)', title: 'Tangen' },
        { label: '\\log(x)', code: '\\log(x)', title: 'Logaritma' },
        { label: '\\ln(x)', code: '\\ln(x)', title: 'Logaritma Natural' },
        { label: '\\vec{v}', code: '\\vec{v}', title: 'Vektor' },
        { label: '\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}', code: '\\begin{pmatrix} a & b \\\\ c & d \\end{pmatrix}', title: 'Matriks 2x2' }
    ]
};

function renderPaletteButtons(category) {
    const symbols = EQUATION_PALETTE[category] || EQUATION_PALETTE.basic;
    const container = document.getElementById('eq-palette-buttons');
    if (!container) return;

    container.innerHTML = symbols.map(item => `
        <button type="button" class="eq-symbol-btn flex items-center justify-center p-2 bg-white hover:bg-blue-50 border border-gray-300 hover:border-blue-400 rounded-lg shadow-2xs transition text-gray-800 font-medium cursor-pointer"
            data-code="${item.code.replace(/"/g, '&quot;')}" title="${item.title}">
            \\( ${item.label} \\)
        </button>
    `).join('');

    if (window.MathJax && window.MathJax.typesetPromise) {
        window.MathJax.typesetPromise([container]).catch(() => {});
    }
}

function updateEquationPreview(latex) {
    const previewEl = document.getElementById('equation-preview-content');
    if (!previewEl) return;
    const clean = (latex || '').trim();
    if (!clean) {
        previewEl.innerHTML = '<span class="text-gray-400 italic text-sm">Rumus akan muncul di sini...</span>';
        return;
    }
    previewEl.innerHTML = `\\[ ${clean} \\]`;
    if (window.MathJax && window.MathJax.typesetPromise) {
        window.MathJax.typesetPromise([previewEl]).catch(() => {});
    }
}

function ensureEquationEditorModal() {
    if (document.getElementById('equation-editor-modal')) return;

    const modalHtml = `
        <div id="equation-editor-modal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/60 backdrop-blur-2xs p-4" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden border border-gray-200">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#0071BC] text-white flex items-center justify-center font-bold text-base shadow-xs">
                            ∑
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Editor Rumus Matematika (LaTeX)</h3>
                            <p class="text-xs text-gray-500">Pilih simbol matematika atau ketik kode LaTeX langsung.</p>
                        </div>
                    </div>
                    <button type="button" id="btn-close-equation-modal" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-lg transition cursor-pointer">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 overflow-y-auto space-y-4 text-sm flex-1">
                    <!-- Palette Tabs -->
                    <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-2">
                        <button type="button" class="eq-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#0071BC] text-white transition cursor-pointer" data-category="basic">Dasar & Pecahan</button>
                        <button type="button" class="eq-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition cursor-pointer" data-category="operators">Operator</button>
                        <button type="button" class="eq-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition cursor-pointer" data-category="greek">Yunani & Simbol</button>
                        <button type="button" class="eq-tab-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition cursor-pointer" data-category="advanced">Kalkulus & Matriks</button>
                    </div>

                    <!-- Palette Buttons Container -->
                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-200 min-h-[90px]">
                        <div id="eq-palette-buttons" class="grid grid-cols-4 sm:grid-cols-6 gap-2"></div>
                    </div>

                    <!-- LaTeX Input Area -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Kode LaTeX:</label>
                            <button type="button" id="btn-clear-latex-input" class="text-xs text-red-500 hover:text-red-700 font-medium cursor-pointer">Bersihkan</button>
                        </div>
                        <textarea id="equation-latex-input" rows="3" class="w-full p-3 font-mono text-sm bg-white border border-gray-300 rounded-lg shadow-inner focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Contoh: \\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}"></textarea>
                    </div>

                    <!-- Live Preview Box -->
                    <div>
                        <label class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-1.5">Pratinjau Rumus (Live Preview):</label>
                        <div id="equation-preview-box" class="w-full min-h-[75px] p-4 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-center overflow-x-auto text-base text-gray-800 shadow-inner">
                            <div id="equation-preview-content" class="text-center">
                                <span class="text-gray-400 italic text-sm">Rumus akan muncul di sini...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end items-center gap-3">
                    <button type="button" id="btn-cancel-equation-modal" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 transition shadow-2xs cursor-pointer">
                        Batal
                    </button>
                    <button type="button" id="btn-insert-equation-action" class="px-5 py-2 text-sm font-bold text-white bg-[#0071BC] hover:bg-blue-700 rounded-lg transition shadow-md flex items-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-check"></i>
                        Sisipkan ke Editor
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Event listener tabs
    document.querySelectorAll('.eq-tab-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.eq-tab-btn').forEach(b => {
                b.classList.remove('bg-[#0071BC]', 'text-white');
                b.classList.add('bg-gray-100', 'text-gray-700');
            });
            this.classList.remove('bg-gray-100', 'text-gray-700');
            this.classList.add('bg-[#0071BC]', 'text-white');
            renderPaletteButtons(this.dataset.category);
        });
    });

    // Event listener symbol clicks
    $(document).on('click', '.eq-symbol-btn', function () {
        const code = $(this).attr('data-code');
        const input = document.getElementById('equation-latex-input');
        if (!input) return;

        const start = input.selectionStart || input.value.length;
        const end = input.selectionEnd || input.value.length;
        const current = input.value;

        input.value = current.substring(0, start) + code + current.substring(end);
        input.focus();
        input.setSelectionRange(start + code.length, start + code.length);

        updateEquationPreview(input.value);
    });

    // Real-time typing update
    let previewDebounce = null;
    const input = document.getElementById('equation-latex-input');
    if (input) {
        input.addEventListener('input', function () {
            clearTimeout(previewDebounce);
            previewDebounce = setTimeout(() => {
                updateEquationPreview(this.value);
            }, 150);
        });
    }

    // Clear button
    const clearBtn = document.getElementById('btn-clear-latex-input');
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            if (input) {
                input.value = '';
                input.focus();
                updateEquationPreview('');
            }
        });
    }

    // Close / Cancel modal
    const closeModal = () => {
        const modal = document.getElementById('equation-editor-modal');
        if (modal) modal.style.display = 'none';
    };

    document.getElementById('btn-close-equation-modal')?.addEventListener('click', closeModal);
    document.getElementById('btn-cancel-equation-modal')?.addEventListener('click', closeModal);

    // Insert equation into active CKEditor
    document.getElementById('btn-insert-equation-action')?.addEventListener('click', function () {
        const latex = input ? input.value.trim() : '';
        if (!latex) {
            closeModal();
            return;
        }

        const snippet = `\\( ${latex} \\)`;

        if (currentActiveEditor) {
            try {
                currentActiveEditor.model.change(writer => {
                    const selection = currentActiveEditor.model.document.selection;
                    const insertPosition = selection.getFirstPosition();
                    if (insertPosition) {
                        currentActiveEditor.model.insertContent(writer.createText(snippet), insertPosition);
                    } else {
                        const endPosition = writer.createPositionAt(currentActiveEditor.model.document.getRoot(), 'end');
                        currentActiveEditor.model.insertContent(writer.createText(snippet), endPosition);
                    }
                });
                currentActiveEditor.editing.view.focus();
            } catch (err) {
                console.warn('CKEditor insertContent fallback:', err);
                const currentData = currentActiveEditor.getData();
                currentActiveEditor.setData(currentData + ' ' + snippet);
            }
        }

        closeModal();
    });
}

function openEquationModal() {
    ensureEquationEditorModal();
    const modal = document.getElementById('equation-editor-modal');
    if (!modal) return;

    modal.style.display = 'flex';
    renderPaletteButtons('basic');

    const input = document.getElementById('equation-latex-input');
    if (input) {
        input.value = '';
        input.focus();
        updateEquationPreview('');
    }
}

// Buka modal saat tombol "Sisipkan Rumus" diklik
$(document).on('click', '.btn-open-equation-modal', function (e) {
    e.preventDefault();
    const btn = $(this);
    const targetId = btn.data('target-id');
    const targetName = btn.data('target-name');

    let matched = null;
    if (targetId) {
        matched = editorInstances.find(item => item.element.id === targetId);
    } else if (targetName) {
        matched = editorInstances.find(item => item.element.getAttribute('name') === targetName);
    }

    if (!matched) {
        const parent = btn.closest('div.border, div.leading-10, div.flex-col');
        const textarea = parent.find('textarea.editor')[0];
        if (textarea) {
            matched = editorInstances.find(item => item.element === textarea);
        }
    }

    if (matched) {
        currentActiveEditor = matched.instance;
    } else if (editorInstances.length > 0) {
        currentActiveEditor = editorInstances[0].instance;
    }

    openEquationModal();
});

function formQuestionBankEdit() {
    const container = document.getElementById('editor-container');
    if (!container) return;

    const source = container.dataset.source;
    const questionType = container.dataset.questionType;
    const subBabId = container.dataset.subBabId;
    const questionId = container.dataset.questionId;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!source) return;
    if (!questionId) return;
    if (!questionType) return;

    $.ajax({
        url: schoolId
            ? `/lms/school-subscription/question-bank-management/bank-soal/form/source/${source}/review/question-type/${questionType}/${questionId}/${schoolName}/${schoolId}/edit/${subBabId}`
            : `/lms/question-bank-management/bank-soal/form/source/${source}/review/question-type/${questionType}/${questionId}/edit/${subBabId}`,
        method: 'GET',
        success: function (response) {
            const question = response.editQuestion;
            const questionTypeNormalized = (questionType || '').toUpperCase();

            function renderOptionsByType(type) {
                switch (type) {
                    case 'MCQ':
                        return renderMCQ();
                    case 'MCMA':
                        return renderMCMA();
                    case 'MATCHING':
                        return renderMatching();
                    case 'PG_KOMPLEKS':
                        return renderPGKompleks();
                    default:
                        return '';
                }
            }

            function renderMCQ() {
                const options = response.options;

                // options value
                const optionEditors = `
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        ${options.map(opt => `
                            <div class="flex flex-col gap-2 p-2 border border-gray-300 rounded">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium">
                                        ${opt.options_key}
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <button type="button" class="btn-open-equation-modal px-2.5 py-0.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100 transition shadow-2xs flex items-center gap-1 cursor-pointer" data-target-name="options[${opt.id}]">
                                        <span class="font-bold">∑</span> Rumus
                                    </button>
                                </div>
                                <textarea class="editor w-full" name="options[${opt.id}]">${convertMathMlToLatex(opt.options_value)}</textarea>
                            </div>
                        `).join('')}
                    </div>
                `;

                // Answer Key dropdown tetap dibawah grid opsi
                const answerSelect = `
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 my-6">
                        <div>
                            <label class="mb-2 text-sm">
                                Answer Key
                                <sup class="text-red-500">&#42;</sup>
                            </label>
                            <select name="answer_key"
                                class="w-full bg-white shadow-lg h-12 text-sm border border-gray-300 rounded px-2 cursor-pointer">
                                ${options.map(opt => `
                                    <option value="${opt.options_key}" ${opt.is_correct ? 'selected' : ''}>
                                        ${opt.options_key}
                                    </option>
                                `).join('')}
                            </select>
                        </div>
                    </div>
                `;

                return optionEditors + answerSelect;
            }

            function renderMCMA() {
                const options = response.options;

                // options value & answer key
                const optionEditors = `
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        ${options.map(opt => `
                            <div class="flex flex-col gap-2 p-2 border border-gray-300 rounded">
                                <div class="flex justify-between items-center">
                                    <label class="text-sm font-medium">
                                        ${opt.options_key}
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="btn-open-equation-modal px-2.5 py-0.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100 transition shadow-2xs flex items-center gap-1 cursor-pointer" data-target-name="options[${opt.id}]">
                                            <span class="font-bold">∑</span> Rumus
                                        </button>
                                        <input
                                            type="checkbox"
                                            name="answer_key[]"
                                            value="${opt.options_key}"
                                            ${opt.is_correct ? 'checked' : ''}
                                            class="mcma-checkbox cursor-pointer"
                                        >
                                    </div>
                                </div>

                                <textarea class="editor w-full" name="options[${opt.id}]">${convertMathMlToLatex(opt.options_value)}</textarea>

                                <span id="error-options-${opt.id}" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                        `).join('')}
                    </div>

                    <!-- GLOBAL ERROR MCMA -->
                    <div class="lg:col-span-2">
                        <span id="error-answer_key" class="text-red-500 font-bold text-xs pt-2"></span>
                    </div>
                `;

                return optionEditors;
            }

            function renderMatching() {
                const left = response.options.filter(o => o.extra_data?.side === 'left');
                const right = response.options.filter(o => o.extra_data?.side === 'right');

                return `
                    <div class="matching-editor grid grid-cols-1 lg:grid-cols-2 gap-6">

                        <!-- LEFT Column -->
                        <div>
                            <h4 class="font-bold mb-2">LEFT</h4>
                            <div class="space-y-4">

                                ${left.map(l => {

                                    const selected = response.matching?.[l.options_key] ?? '';
                                    const isUsed = response.isUsed;

                                    return `
                                        <div class="flex flex-col gap-3 p-3 border border-gray-300 rounded-lg">

                                            <!-- LABEL -->
                                            <div class="flex items-center justify-between">
                                                <label class="text-sm font-semibold">
                                                    ${l.options_key}
                                                </label>
                                                <button type="button" class="btn-open-equation-modal px-2.5 py-0.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100 transition shadow-2xs flex items-center gap-1 cursor-pointer" data-target-name="left[${l.id}]">
                                                    <span class="font-bold">∑</span> Rumus
                                                </button>
                                            </div>

                                            <!-- EDITOR LEFT -->
                                            <textarea class="editor w-full" name="left[${l.id}]">${convertMathMlToLatex(l.options_value ?? '')}</textarea>

                                            <!-- SELECT PASANGAN -->
                                            <div class="flex justify-end">
                                                <select name="pair_with[${l.id}]" class="border border-gray-300 rounded px-3 py-1 text-sm cursor-pointer w-full lg:w-auto outline-none 
                                                " >

                                                    <option value="" class="hidden">
                                                        Pilih pasangan
                                                    </option>

                                                    ${right.map(r => `
                                                        <option value="${r.options_key}" ${selected === r.options_key ? 'selected' : ''}>
                                                            ${r.options_key}
                                                        </option>
                                                    `).join('')}

                                                </select>
                                            </div>

                                            <!-- ERROR -->
                                            <span id="error-pair_with-${l.id}" class="text-red-500 font-bold text-xs"></span>

                                        </div>
                                    `;
                                }).join('')}

                            </div>
                        </div>

                        <!-- RIGHT Column -->
                        <div>
                            <h4 class="font-bold mb-2">RIGHT</h4>
                            ${right.map(r => `
                                <div class="mb-4 p-2 border border-gray-300 rounded">
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="text-sm font-semibold">${r.options_key}</label>
                                        <button type="button" class="btn-open-equation-modal px-2.5 py-0.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100 transition shadow-2xs flex items-center gap-1 cursor-pointer" data-target-name="right[${r.id}]">
                                            <span class="font-bold">∑</span> Rumus
                                        </button>
                                    </div>
                                    <textarea class="editor w-full" name="right[${r.id}]">${convertMathMlToLatex(r.options_value)}</textarea>
                                    <span id="error-right-${r.id}" class="text-red-500 font-bold text-xs pt-2"></span>
                                </div>
                            `).join('')}
                        </div>

                    </div>
                `;
            }

            function renderPGKompleks() {

                const options = response.options;

                const categories = options.filter(o => o.extra_data?.side === 'category');
                const items = options.filter(o => o.extra_data?.side === 'item');

                return `
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-6">

                        <!-- LEFT: ITEMS -->
                        <div>
                            <h4 class="font-bold mb-3">Item</h4>

                            <div class="mb-4">
                                <label class="block text-sm mb-1">
                                    Header Item
                                    <sup class="text-red-500">*</sup>
                                </label>
                                <input type="text" name="header_item" value="${question.header_item ?? ''}" placeholder="Contoh: Pernyataan / Gambar / Kasus"
                                    class="w-full bg-white shadow-lg h-12 text-sm  border border-gray-300 outline-none rounded-md px-2"/>
                                <span id="error-header_item" class="text-red-500 font-bold text-xs pt-2"></span>
                            </div>

                            <div class="space-y-3">
                                ${items.map(item => {
                                const answer = item.extra_data?.answer;

                                return `
                                        <div class="flex flex-col gap-2 p-3 border border-gray-300 rounded-lg">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-medium text-gray-500">Item Soal</span>
                                                <button type="button" class="btn-open-equation-modal px-2.5 py-0.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100 transition shadow-2xs flex items-center gap-1 cursor-pointer" data-target-name="item[${item.id}]">
                                                    <span class="font-bold">∑</span> Rumus
                                                </button>
                                            </div>

                                            <!-- ITEM EDITOR -->
                                            <textarea class="editor w-full" name="item[${item.id}]">${convertMathMlToLatex(item.options_value)}</textarea>
                                            <span id="error-item-${item.id}" class="text-red-500 font-bold text-xs pt-2"></span>

                                            <!-- SELECT CATEGORY -->
                                            <div class="flex justify-end">
                                                <select name="answer[${item.id}]" class="border border-gray-300 rounded px-3 py-1 text-sm cursor-pointer outline-none">

                                                        <option value="" class="hidden">Pilih Kategori</option>

                                                        ${categories.map(cat => `
                                                            <option value="${cat.options_key}" 
                                                                ${answer === cat.options_key ? 'selected' : ''}>
                                                                ${cat.options_value}
                                                            </option>
                                                        `).join('')}

                                                </select>
                                                <span id="error-answer-${item.id}" class="text-red-500 font-bold text-xs pt-2"></span>
                                            </div>

                                        </div>
                                    `;
                            }).join('')}
                            </div>
                        </div>

                        <!-- RIGHT: CATEGORIES -->
                        <div>
                            <h4 class="font-bold mb-3">Kategori</h4>

                            <div class="space-y-3">
                                ${categories.map(cat => `
                                    <div class="w-full p-3 border border-gray-300 rounded-lg flex flex-col gap-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-medium text-gray-500">Kategori</span>
                                            <button type="button" class="btn-open-equation-modal px-2.5 py-0.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded hover:bg-blue-100 transition shadow-2xs flex items-center gap-1 cursor-pointer" data-target-name="category[${cat.id}]">
                                                <span class="font-bold">∑</span> Rumus
                                            </button>
                                        </div>
                                        <textarea class="editor w-full" name="category[${cat.id}]">${convertMathMlToLatex(cat.options_value)}</textarea>
                                        <span id="error-category-${cat.id}" class="text-red-500 font-bold text-xs pt-2"></span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>

                    </div>
                `;
            }

            // options value select
            const optionsValue = renderOptionsByType(
                questionTypeNormalized,
            )
            
            const formHtml = `
                <form id="bank-soal-edit-question-form" data-source="${source}" data-sub-bab-id="${subBabId}" data-question-id="${questionId}" 
                    data-school-name="${schoolName}" data-school-id="${schoolId}" enctype="multipart/form-data" autocomplete="off">

                    <input type="hidden" name="question_type" value="${questionTypeNormalized}">

                    <!-- Question -->
                    <div class="leading-10 mb-6 w-full">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-sm">Question<sup class="text-red-500 pl-1">*</sup></span>
                            <button type="button" class="btn-open-equation-modal inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition shadow-2xs cursor-pointer" data-target-id="questions">
                                <span class="font-bold">∑</span> Sisipkan Rumus (LaTeX)
                            </button>
                        </div>
                        <textarea name="questions" id="questions" class="editor">${convertMathMlToLatex(question.questions)}</textarea>
                        <span id="error-questions" class="text-red-500 font-bold text-xs pt-2"></span>
                    </div>

                    <div>
                        ${optionsValue}
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 my-6">
                        <div class="flex flex-col">
                            <label class="mb-2 text-sm">
                                Difficulty
                                <sup class="text-red-500">&#42;</sup>
                            </label>
                            <select name="difficulty" id="difficulty" value="{{ old('difficulty') }}"
                                class="bg-white shadow-lg h-12 text-sm  border border-gray-300 outline-none rounded-md px-2 cursor-pointer">
                                    <option value="${question.difficulty}" class="hidden">
                                        ${question.difficulty}
                                    <option value="Mudah">Mudah</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Sukar">Sukar</option>
                            </select>
                            <span id="error-difficulty" class="text-red-500 font-bold text-xs pt-2"></span>
                        </div>

                        <div class="flex flex-col">
                            <label class="mb-2 text-sm">
                                Bloom
                                <sup class="text-red-500">&#42;</sup>
                            </label>
                                <input type="text" id="bloom" name="bloom" class="bg-white shadow-lg h-12 text-sm  border border-gray-300 outline-none rounded-md px-2" value="${question.bloom}"
                                placeholder="Masukkan bloom">
                            <span id="error-bloom" class="text-red-500 font-bold text-xs pt-2"></span>
                        </div>
                    </div>

                    <div class="leading-10 w-full my-6">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-sm">
                                Explanation
                                <sup class="text-red-500">&#42;</sup>
                            </span>
                            <button type="button" class="btn-open-equation-modal inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition shadow-2xs cursor-pointer" data-target-id="explanation">
                                <span class="font-bold">∑</span> Sisipkan Rumus (LaTeX)
                            </button>
                        </div>
                        <textarea name="explanation" id="explanation" class="editor">${convertMathMlToLatex(question.explanation)}</textarea>
                        <span id="error-explanation" class="text-red-500 font-bold text-xs pt-2"></span>
                    </div>

                    <div class="flex justify-end mt-20 lg:mt-8">
                        <button id="submit-button" type="button" data-question-id="${questionId}"
                            class="bg-[#0071BC] text-white font-bold py-2 px-6 rounded-lg shadow-md cursor-pointer default:cursor-default">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            `;

            container.innerHTML = formHtml;

            // Inisialisasi CKEditor jika ada
            const editorContainer = document.getElementById('editor-container');
            const uploadUrl = editorContainer.getAttribute('data-upload-url');
            const deleteUrl = editorContainer.getAttribute('data-delete-url');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            editorInstances.length = 0; // Bersihkan instance lama agar tidak menumpuk saat re-render
            const editors = container.querySelectorAll('.editor');
            editors.forEach((textarea, index) => {
                ClassicEditor.create(textarea, {
                    ckfinder: {
                        uploadUrl: uploadUrl
                    },
                    toolbar: {
                        shouldNotGroupWhenFull: true
                    },  
                })
                    .then(editor => {
                        previousImageUrlsMap[index] = [];
                        editorInstances.push({ element: textarea, instance: editor }); // SIMPAN INSTANCE

                        // Track active editor on focus
                        editor.ui.focusTracker.on('change:isFocused', (evt, name, isFocused) => {
                            if (isFocused) {
                                currentActiveEditor = editor;
                            }
                        });

                        editor.model.document.on('change:data', () => {
                            const currentContent = editor.getData();

                            const imageUrls = Array.from(currentContent.matchAll(/<img[^>]+src="([^">]+)"/g))
                                .map(match => match[1]);

                            const removedImages = previousImageUrlsMap[index].filter(url => !imageUrls.includes(url));

                            removedImages.forEach(url => {
                                fetch(deleteUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    body: JSON.stringify({ imageUrl: url })
                                })
                                    .then(response => response.json())
                                    .then(data => console.log('Gambar berhasil dihapus:', data))
                                    .catch(error => console.error('Error saat menghapus gambar:', error));
                            });

                            previousImageUrlsMap[index] = imageUrls;
                        });

                        // Hapus border merah & text error ketika konten CKEditor berubah
                        editor.model.document.on('change:data', () => {
                            const textarea = editor.sourceElement;

                            textarea.classList.remove('border-red-400', 'border-2');

                            const errorSpan = textarea
                                .closest('div')
                                ?.querySelector('[id^="error-"]');

                            if (errorSpan) {
                                errorSpan.textContent = '';
                            }
                        });
                    })
                    .catch(error => console.error('Error CKEditor:', error));
            });

            ensureEquationEditorModal();

            if (window.MathJax && window.MathJax.typesetPromise) {
                window.MathJax.typesetPromise();
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    formQuestionBankEdit();
});

$(document).on('change', '.mcma-checkbox', function () {
    const checkedCount = $('.mcma-checkbox:checked').length;

    if (checkedCount > 0) {
        $('#error-answer_key').text('');
    }
});

// Hapus error ketika user mengetik di input/textarea biasa
document.addEventListener('input', function (e) {
    const target = e.target;

    // Cek apakah target adalah input atau textarea di form bank soal
    if ((target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.tagName === 'SELECT') && target.closest('#bank-soal-edit-question-form')) {
        // Hapus border merah
        target.classList.remove('border-red-400', 'border-2');

        // Hapus pesan error terkait
        const errorSpan = target.closest('div')?.querySelector('[id^="error-"]');
        if (errorSpan) {
            errorSpan.textContent = '';
        }
    }
});

let isProcessing = false;

// Form Action edit question
$(document).ready(function () {
    // form edit question
    $(document).on('click', '#submit-button', function (e) {
        e.preventDefault();

        // 2. Bersihkan konten CKEditor dari <p>&nbsp;</p>
        editorInstances.forEach(({ element, instance }) => {
            let content = instance.getData();
            content = content.replace(/<p>(&nbsp;|\s)*<\/p>/gi, ''); // Hapus paragraf kosong
            element.value = content; // Set ulang ke textarea
        });

        const form = $('#bank-soal-edit-question-form')[0]; // ambil DOM Form-nya
        const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol
        const questionId = $(this).data('question-id');

        if (isProcessing) return;
        isProcessing = true;

        const btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: `/lms/question-bank-management/${questionId}/edit`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                $('#alert-success-bank-soal-edit-question').html(
                    `
                    <div class=" w-full flex justify-center">
                        <div class="fixed z-9999">
                            <div id="alertSuccess"
                                class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                <span class="text-green-600 text-sm">${response.message}</span>
                                <i class="fas fa-times cursor-pointer text-green-600" id="btnClose"></i>
                            </div>
                        </div>
                    </div>
                    `
                );

                setTimeout(function () {
                    document.getElementById('alertSuccess').remove();
                }, 3000);

                document.getElementById('btnClose').addEventListener('click', function () {
                    document.getElementById('alertSuccess').remove();
                });

                isProcessing = false;
                btn.prop('disabled', false);
                
                formQuestionBankEdit(questionId);
            },
            error: function (xhr, status, error) {
                if (xhr.status === 422) {
                    const response = xhr.responseJSON;

                    if (response?.isUsed) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Submit Gagal',
                            text: response.message,
                            confirmButtonText: 'OK',
                        });

                        isProcessing = false;
                        btn.prop('disabled', false);
                        return;
                    }

                    const errors = response.errors;

                    if (errors) {
                        $.each(errors, function (field, messages) {
                            let inputName = field;
                            let errorId = `error-${field}`;

                            if (field.includes('.')) {
                                const [name, index] = field.split('.');
                                inputName = `${name}[${index}]`;
                                errorId = `error-${name}-${index}`;
                            }

                            $(`[name="${inputName}"]`).addClass('border-red-400 border');
                            $(`#${errorId}`).text(messages[0]);
                        });
                    }
                } else if (xhr.status === 419) {
                    alert('CSRF token mismatch. Coba refresh halaman.');
                } else {
                    alert('Terjadi kesalahan saat mengirim data.');
                }

                isProcessing = false;
                btn.prop('disabled', false);
            }
        });
    });
});
