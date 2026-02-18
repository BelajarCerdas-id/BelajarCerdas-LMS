@include('components/sidebar-beranda', [
    'headerSideNav' => 'Review Content',
    'linkBackButton' => route('lms.teacherContentForReleaseReviewMeeting.view', [$role, $schoolName, $schoolId, $schoolClassId, $mapelId, $semester, $serviceId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            @foreach ($data as $item)
                <!-- TEXT -->
                @if ($item['type'] === 'text')
                    <div class="w-full bg-white border border-gray-200 rounded-2xl p-5 mb-5 shadow-sm">

                        <!-- SERVICE -->
                        @if (!empty($item['service_name']))
                            <p class="text-md font-semibold text-blue-600 uppercase tracking-wide mb-1">
                                {{ $item['service_name'] }}
                            </p>
                        @endif

                        <!-- HEADER -->
                        <div class="flex items-start justify-between mb-3">
                            <p class="text-xs font-medium text-gray-700">
                                Text Content
                            </p>
                        </div>

                        <!-- CONTENT -->
                        <div class="text-sm text-gray-800 leading-relaxed whitespace-pre-line">
                            {{ $item['value_text'] ?: '— Tidak ada konten teks —' }}
                        </div>

                    </div>

                <!-- FILE -->
                @elseif ($item['type'] === 'file')
                    <div class="w-full flex justify-center items-center h-full">
                        @if (str_starts_with($item['mime'], 'video/'))
                            <div class="w-full h-165 aspect-video mb-3 rounded-md overflow-hidden">
                                <video
                                    src="{{ $item['file_url'] }}"
                                    class="w-full h-full object-contain"
                                    controls>
                                </video>
                            </div>
    
                        @elseif ($item['mime'] === 'application/pdf')
                            <div class="preview-content w-full h-165 mb-3 border rounded-md overflow-hidden">
                                <iframe
                                    src="{{ $item['file_url'] }}"
                                    class="w-full h-full">
                                </iframe>
                            </div>
    
                        @else
                            <div class="flex justify-center items-center h-165">
                                <h4 class="font-bold opacity-70">Preview tidak tersedia</h4>
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif