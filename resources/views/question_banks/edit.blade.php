<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('question_banks.show', $questionBank) }}" class="text-gray-400 hover:text-sapta-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-semibold text-xl text-sapta-900 leading-tight">
                {{ __('Edit Bank Soal') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('question_banks.update', $questionBank) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-6">
                            <div>
                                <x-input-label for="name" :value="__('Nama Bank Soal *')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $questionBank->name)" required autofocus placeholder="Contoh: UTS Matematika Ganjil" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="subject_id" :value="__('Mata Pelajaran *')" />
                                    @if($subjects->isEmpty())
                                        <div class="mt-1 p-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm flex items-start gap-2">
                                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <p>Anda belum ditugaskan untuk mata pelajaran apapun. Silakan hubungi Administrator atau Kurikulum untuk mengonfigurasi data pengajar Anda.</p>
                                        </div>
                                        <select id="subject_id" name="subject_id" disabled class="border-gray-300 bg-gray-100 text-gray-400 rounded-md shadow-sm block mt-2 w-full">
                                            <option value="">Belum ada mata pelajaran</option>
                                        </select>
                                    @else
                                        <select id="subject_id" name="subject_id" required class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block mt-1 w-full">
                                            <option value="" disabled>Pilih Mata Pelajaran</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ old('subject_id', $questionBank->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <x-input-error :messages="$errors->get('subject_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="grade" :value="__('Kelas / Tingkat')" />
                                    <select id="grade" name="grade" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block mt-1 w-full">
                                        <option value="">Semua Tingkat</option>
                                        <option value="X" {{ old('grade', $questionBank->grade) == 'X' ? 'selected' : '' }}>Kelas X</option>
                                        <option value="XI" {{ old('grade', $questionBank->grade) == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                                        <option value="XII" {{ old('grade', $questionBank->grade) == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('grade')" class="mt-2" />
                                </div>
                            </div>

                            @if(isset($teachers) && $teachers->isNotEmpty())
                            <div>
                                <x-input-label for="teacher_id" :value="__('Guru Pengampu *')" />
                                <select id="teacher_id" name="teacher_id" required class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block mt-1 w-full">
                                    <option value="" disabled>Pilih Guru Pengampu</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ old('teacher_id', $questionBank->teacher_id) == $teacher->id ? 'selected' : '' }}>{{ $teacher->user->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('teacher_id')" class="mt-2" />
                                <p class="text-xs text-gray-500 mt-1">Hanya admin/kurikulum yang dapat mengubah pengampu bank soal.</p>
                            </div>
                            @endif

                            <div>
                                <x-input-label for="description" :value="__('Deskripsi (Opsional)')" />
                                <textarea id="description" name="description" rows="4" class="border-gray-300 focus:border-sapta-500 focus:ring-sapta-500 rounded-md shadow-sm block mt-1 w-full" placeholder="Tuliskan deskripsi atau catatan singkat mengenai bank soal ini">{{ old('description', $questionBank->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4 pt-4 border-t border-gray-100 gap-3">
                                <a href="{{ route('question_banks.show', $questionBank) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200">Batal</a>
                                <x-primary-button class="bg-sapta-600 hover:bg-sapta-700 rounded-xl">
                                    {{ __('Simpan Perubahan') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
