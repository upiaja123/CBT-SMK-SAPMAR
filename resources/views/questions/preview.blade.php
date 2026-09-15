<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-sapta-900 leading-tight">
                Preview Soal
            </h2>
            <div class="text-sm text-gray-500">
                Bank Soal: <span class="font-medium text-gray-900">{{ $question->questionBank->name }}</span>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <style>
                .prose img {
                    max-height: 20rem; /* max-h-80 */
                    width: auto;
                    object-fit: contain;
                    margin-left: auto;
                    margin-right: auto;
                }
            </style>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Simulasi Preview:</strong> Ini adalah tampilan perkiraan bagaimana siswa akan melihat soal ini pada saat ujian. Interaksi pada halaman ini tidak akan disimpan ke database.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Student View Simulation Box -->
            <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
                
                <!-- Exam Header Bar Simulation -->
                <div class="bg-gray-100 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div class="text-gray-700 font-bold">SOAL NO. 1</div>
                    <div class="text-gray-500 text-sm">
                        @if($question->currentVersion->type === 'multiple_choice')
                            Pilih satu jawaban yang benar.
                        @elseif($question->currentVersion->type === 'complex_multiple_choice')
                            Pilih semua jawaban yang benar.
                        @elseif($question->currentVersion->type === 'true_false')
                            Tentukan apakah pernyataan berikut benar atau salah.
                        @elseif($question->currentVersion->type === 'essay')
                            Tuliskan uraian jawaban Anda.
                        @endif
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <!-- Question Content -->
                    <div class="prose max-w-none text-gray-900 text-lg mb-4">
                        {!! nl2br(e($question->currentVersion->content)) !!}
                    </div>

                    <!-- Question Media -->
                    @if($question->currentVersion->media->count() > 0)
                        <div class="mb-8 space-y-4">
                            @foreach($question->currentVersion->media as $media)
                                @if(Str::startsWith($media->mime_type, 'image/'))
                                    <img src="{{ route('media.show', $media->id) }}" alt="Media" class="max-w-full max-h-80 w-auto object-contain mx-auto rounded-lg shadow-sm border border-gray-200">
                                @elseif(Str::startsWith($media->mime_type, 'audio/'))
                                    <audio controls class="w-full max-w-md mx-auto">
                                        <source src="{{ route('media.show', $media->id) }}" type="{{ $media->mime_type }}">
                                        Browser Anda tidak mendukung elemen audio.
                                    </audio>
                                @elseif(Str::startsWith($media->mime_type, 'video/'))
                                    <video controls class="max-w-full max-h-80 w-auto object-contain mx-auto rounded-lg shadow-sm border border-gray-200">
                                        <source src="{{ route('media.show', $media->id) }}" type="{{ $media->mime_type }}">
                                        Browser Anda tidak mendukung elemen video.
                                    </video>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <!-- Options / Inputs -->
                    <div class="mt-8">
                        @if(in_array($question->currentVersion->type, ['multiple_choice', 'true_false']))
                            <div class="space-y-4">
                                @foreach($question->currentVersion->options->sortBy('order') as $option)
                                    <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-blue-50 hover:border-sapta-300 transition-colors bg-white">
                                        <div class="flex items-center h-5">
                                            <input type="radio" name="preview_answer" class="focus:ring-sapta-500 h-5 w-5 text-sapta-600 border-gray-300">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <span class="text-gray-900 text-base">{!! nl2br(e($option->content)) !!}</span>
                                            
                                            <!-- Teacher Info: Show correct answer -->
                                            @if($option->is_correct)
                                                <div class="mt-1 text-xs text-green-600 font-semibold flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Jawaban Benar (Kunci)
                                                </div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                        @elseif($question->currentVersion->type === 'complex_multiple_choice')
                            <div class="space-y-4">
                                @foreach($question->currentVersion->options->sortBy('order') as $option)
                                    <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-blue-50 hover:border-sapta-300 transition-colors bg-white">
                                        <div class="flex items-center h-5">
                                            <input type="checkbox" name="preview_answer[]" class="focus:ring-sapta-500 h-5 w-5 text-sapta-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <span class="text-gray-900 text-base">{!! nl2br(e($option->content)) !!}</span>
                                            @if($option->is_correct)
                                                <div class="mt-1 text-xs text-green-600 font-semibold flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Jawaban Benar (Kunci)
                                                </div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            
                        @elseif($question->currentVersion->type === 'short_answer')
                            <div class="mt-2">
                                <input type="text" class="shadow-sm focus:ring-sapta-500 focus:border-sapta-500 block w-full sm:text-sm border-gray-300 rounded-md p-3" placeholder="Ketik jawaban singkat Anda di sini...">
                            </div>
                            <div class="mt-4 p-3 bg-gray-50 text-sm text-gray-500 rounded border">
                                <strong>Kunci Jawaban (Metadata):</strong> 
                                {{ $question->currentVersion->scoring_metadata ? json_encode($question->currentVersion->scoring_metadata) : 'Tidak ada metadata spesifik.' }}
                            </div>

                        @elseif($question->currentVersion->type === 'matching')
                            @php
                                $leftItems = [];
                                $rightItems = [];
                                if($question->currentVersion->scoring_metadata && isset($question->currentVersion->scoring_metadata['pairs'])) {
                                    foreach($question->currentVersion->scoring_metadata['pairs'] as $pair) {
                                        $leftItems[] = $pair['left'];
                                        $rightItems[] = $pair['right'];
                                    }
                                    shuffle($rightItems);
                                }
                            @endphp
                            
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200" x-data="matchingPreview({{ json_encode(['left_items' => $leftItems, 'right_items' => $rightItems]) }})">
                                <h4 class="text-sm font-semibold text-gray-800 mb-4">Simulasi Pasangan Jawaban</h4>
                                
                                <template x-if="left_items.length === 0">
                                    <div class="p-4 bg-yellow-50 text-yellow-700 rounded-lg border border-yellow-200">
                                        Peringatan: Tidak ada pasangan yang dikonfigurasi untuk soal ini.
                                    </div>
                                </template>

                                <template x-if="left_items.length > 0">
                                    <div class="relative">
                                        <!-- SVG Overlay -->
                                        <svg x-ref="svgCanvas" class="absolute inset-0 pointer-events-none w-full h-full z-10">
                                            <path :d="lines.map(l => `M ${l.x1} ${l.y1} L ${l.x2} ${l.y2}`).join(' ')" stroke="#111827" stroke-width="4" stroke-linecap="round" fill="none" />
                                            <line x-show="isDrawing && currentLine" :x1="currentLine?.x1" :y1="currentLine?.y1" :x2="currentLine?.x2" :y2="currentLine?.y2" stroke="#111827" stroke-width="4" stroke-linecap="round" stroke-dasharray="6,6" opacity="0.5"/>
                                        </svg>

                                        <div class="grid grid-cols-2 gap-8" @mousemove.window="onMouseMove" @mouseup.window="onMouseUp" @touchmove.window="onTouchMove" @touchend.window="onMouseUp">
                                            <!-- Left Items -->
                                            <div class="space-y-6" x-ref="leftContainer">
                                                <template x-for="(item, index) in left_items" :key="'l'+index">
                                                    <div class="p-4 bg-white border border-gray-200 rounded-lg flex items-center justify-between relative select-none"
                                                         :class="{'ring-2 ring-brand-500': isLeftSelected(item)}">
                                                        <span x-text="item" class="text-sm font-medium text-gray-800 pr-4"></span>
                                                        <div class="w-5 h-5 rounded-full bg-gray-50 border-2 border-brand-500 cursor-pointer flex-shrink-0"
                                                             @mousedown.stop.prevent="startDrawing($event, item, 'left')"
                                                             @touchstart.stop.prevent="startDrawing($event, item, 'left')"
                                                             :id="'left-node-' + index"></div>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Right Items -->
                                            <div class="space-y-6" x-ref="rightContainer">
                                                <template x-for="(item, index) in right_items" :key="'r'+index">
                                                    <div class="p-4 bg-white border border-gray-200 rounded-lg flex items-center justify-between relative select-none cursor-pointer"
                                                         :class="{'ring-2 ring-brand-500': isRightSelected(item)}"
                                                         @mouseup.stop.prevent="finishDrawing(item, 'right')"
                                                         @touchend.stop.prevent="finishDrawing(item, 'right', $event)">
                                                        <div class="w-5 h-5 rounded-full bg-gray-50 border-2 border-brand-500 flex-shrink-0"
                                                             :id="'right-node-' + index"></div>
                                                        <span x-text="item" class="text-sm font-medium text-gray-800 text-right pl-4"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>

                                        <div class="mt-6 flex justify-end">
                                            <button type="button" @click="clearLines()" class="text-sm text-red-600 hover:text-red-800 underline font-medium relative z-20">Reset Tarik Garis</button>
                                        </div>
                                    </div>
                                </template>
                            </div>

                        @elseif($question->currentVersion->type === 'essay')
                            <div class="mt-2">
                                <textarea rows="6" class="shadow-sm focus:ring-sapta-500 focus:border-sapta-500 block w-full sm:text-sm border-gray-300 rounded-md p-3" placeholder="Ketik uraian jawaban Anda di sini..."></textarea>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Simulation Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                        Sebelumnya
                    </button>
                    <label class="flex items-center space-x-2 text-sm text-yellow-600 font-medium cursor-pointer">
                        <input type="checkbox" class="rounded border-gray-300 text-yellow-500 focus:ring-yellow-500">
                        <span>Ragu-Ragu</span>
                    </label>
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-sapta-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sapta-700 active:bg-sapta-900 focus:outline-none focus:border-sapta-900 focus:ring focus:ring-sapta-300 disabled:opacity-25 transition">
                        Selanjutnya
                    </button>
                </div>
            </div>
            
            <div class="flex justify-center mt-6">
                <a href="{{ route('question_banks.show', $question->questionBank) }}" class="text-sm text-gray-500 hover:text-gray-900 underline">
                    &larr; Kembali ke Bank Soal
                </a>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('matchingPreview', (data) => ({
                left_items: data.left_items,
                right_items: data.right_items,
                lines: [],
                currentLine: null,
                startItem: null,
                isDrawing: false,

                getCoordinates(e) {
                    let clientX, clientY;
                    if (e.touches && e.touches.length > 0) {
                        clientX = e.touches[0].clientX;
                        clientY = e.touches[0].clientY;
                    } else if (e.changedTouches && e.changedTouches.length > 0) {
                        clientX = e.changedTouches[0].clientX;
                        clientY = e.changedTouches[0].clientY;
                    } else {
                        clientX = e.clientX;
                        clientY = e.clientY;
                    }
                    
                    const svgRect = this.$refs.svgCanvas.getBoundingClientRect();
                    return {
                        x: clientX - svgRect.left,
                        y: clientY - svgRect.top
                    };
                },

                startDrawing(e, item, side) {
                    if (side !== 'left') return;
                    const existingIndex = this.lines.findIndex(l => l.left === item);
                    if (existingIndex !== -1) {
                        this.lines.splice(existingIndex, 1);
                    }
                    this.isDrawing = true;
                    this.startItem = item;
                    const rect = e.target.getBoundingClientRect();
                    const svgRect = this.$refs.svgCanvas.getBoundingClientRect();
                    const startX = rect.left - svgRect.left + rect.width/2;
                    const startY = rect.top - svgRect.top + rect.height/2;
                    this.currentLine = { x1: startX, y1: startY, x2: startX, y2: startY };
                },

                onMouseMove(e) {
                    if (!this.isDrawing || !this.currentLine) return;
                    const coords = this.getCoordinates(e);
                    this.currentLine.x2 = coords.x;
                    this.currentLine.y2 = coords.y;
                },
                
                onTouchMove(e) {
                    if (!this.isDrawing || !this.currentLine) return;
                    e.preventDefault();
                    this.onMouseMove(e);
                },

                onMouseUp() {
                    if (!this.isDrawing) return;
                    this.isDrawing = false;
                    this.currentLine = null;
                    this.startItem = null;
                },

                finishDrawing(item, side, e = null) {
                    if (!this.isDrawing || !this.currentLine || !this.startItem) return;
                    if (side !== 'right') return;
                    const existingIndex = this.lines.findIndex(l => l.right === item);
                    if (existingIndex !== -1) {
                        this.lines.splice(existingIndex, 1);
                    }
                    
                    let targetEl = null;
                    if (e && e.changedTouches) {
                        const touch = e.changedTouches[0];
                        targetEl = document.elementFromPoint(touch.clientX, touch.clientY);
                        if (!targetEl || !targetEl.classList.contains('bg-gray-50') || !targetEl.id.startsWith('right-node')) {
                            this.onMouseUp();
                            return; 
                        }
                    } else {
                        targetEl = document.getElementById('right-node-' + this.right_items.indexOf(item));
                    }

                    if (targetEl && this.$refs.svgCanvas) {
                        const rRect = targetEl.getBoundingClientRect();
                        const svgRect = this.$refs.svgCanvas.getBoundingClientRect();
                        this.lines.push({
                            x1: this.currentLine.x1,
                            y1: this.currentLine.y1,
                            x2: rRect.left - svgRect.left + rRect.width/2,
                            y2: rRect.top - svgRect.top + rRect.height/2,
                            left: this.startItem,
                            right: item
                        });
                    }
                    this.isDrawing = false;
                    this.currentLine = null;
                    this.startItem = null;
                },

                clearLines() {
                    this.lines = [];
                },

                isLeftSelected(item) {
                    return this.lines.some(l => l.left === item);
                },

                isRightSelected(item) {
                    return this.lines.some(l => l.right === item);
                }
            }));
        });
    </script>
</x-app-layout>
