<main class="mx-0 lg:mx-10">
    <section id="container" data-content-id="{{ $content->id }}" data-curriculum-id="{{ $content->kurikulum_id }}" data-kelas-id="{{ $content->kelas_id }}"
        data-mapel-id="{{ $content->mapel_id }}" data-bab-id="{{ $content->bab_id }}" data-sub-bab-id="{{ $content->sub_bab_id }}" 
        class="bg-white shadow-lg rounded-lg p-6 border border-gray-200">
        
        <form id="content-management-form">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
                <!--- Kurikulum --->
                <div class="flex flex-col order-1">
                    <label class="mb-2 text-sm">
                        Kurikulum
                        <sup class="text-red-500">&#42;</sup>
                    </label>
                    <select name="kurikulum_id" id="id_kurikulum" data-old-kurikulum="{{ $content->kurikulum_id }}"
                        class="w-full bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 focus:border cursor-pointer">
                        <option value="" class="hidden">Pilih Kurikulum</option>
                        @foreach ($getCurriculum as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_kurikulum }}</option>
                        @endforeach
                    </select>
                    <span id="error-kurikulum_id" class="text-red-500 font-bold text-xs pt-2"></span>
                </div>

                <!--- Kelas --->
                <div class="flex flex-col order-3 lg:order-5">
                    <label class="mb-2 text-sm">
                        Kelas
                        <sup class="text-red-500">&#42;</sup>
                    </label>
                    <select name="kelas_id" id="id_kelas" data-old-kelas="{{ $content->kelas_id }}"
                        class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                        <option class="hidden">Pilih Kelas {{ $content->kelas_id }}</option>
                    </select>
                    <span id="error-kelas_id" class="text-red-500 font-bold text-xs pt-2"></span>
                </div>

                <!--- Bab --->
                <div class="flex flex-col order-4">
                    <label class="mb-2 text-sm">
                        Bab
                        <sup class="text-red-500">&#42;</sup>
                    </label>
                    <select name="bab_id" id="id_bab" data-old-bab="{{ $content->bab_id }}"
                        class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                        <option class="hidden">Pilih Bab</option>
                    </select>
                    <span id="error-bab_id" class="text-red-500 font-bold text-xs pt-2"></span>
                </div>

                <!--- Service --->
                <div class="flex flex-col order-2 lg:order-3">
                    <label class="mb-2 text-sm">
                        Service
                        <sup class="text-red-500">&#42;</sup>
                    </label>
                    <select name="service_id" id="id_service" data-old-service="{{ $content->service_id }}"
                        class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                        <option class="hidden">Pilih Service</option>
                    </select>
                    <span id="error-service_id" class="text-red-500 font-bold text-xs pt-2"></span>
                </div>

                <!--- Mapel --->
                <div class="flex flex-col order-3 lg:order-2">
                    <label class="mb-2 text-sm">
                        Mata Pelajaran
                        <sup class="text-red-500">&#42;</sup>
                    </label>
                    <select name="mapel_id" id="id_mapel" data-old-mapel="{{ $content->mapel_id }}"
                        class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                        <option class="hidden">Pilih Mata Pelajaran</option>
                    </select>
                    <span id="error-mapel_id" class="text-red-500 font-bold text-xs pt-2"></span>
                </div>

                <!--- Sub Bab --->
                <div class="flex flex-col order-5">
                    <label class="mb-2 text-sm">
                        Sub Bab
                        <sup class="text-red-500">&#42;</sup>
                    </label>
                    <select name="sub_bab_id" id="id_sub_bab" data-old-sub-bab="{{ $content->sub_bab_id }}"
                        class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-md px-2 opacity-50 focus:border cursor-default" disabled>
                        <option class="hidden">Pilih Sub Bab</option>
                    </select>
                    <span id="error-sub_bab_id" class="text-red-500 font-bold text-xs pt-2"></span>
                </div>

                <div id="dynamic-form" class="order-6"></div>
            </div>

            <!--- button bulkupload content --->
            <div class="flex justify-end w-full px-6 pb-6">
                <button type="button" id="submit-button-edit-content"
                    class="bg-[#0071BC] text-white text-md font-bold h-10 px-10 rounded-lg shadow-md flex gap-2 items-center justify-center cursor-pointer disabled:cursor-default">
                    <i class="fa-solid fa-circle-plus"></i>
                    Simpan
                </button>
            </div>
        </form>
    </section>
</main>

<script src="{{ asset('assets/js/Features/lms/components/content-management/lms-content-management-edit.js') }}"></script> <!--- lms content management edit ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-kelas-mapel-bab-sub_bab-dropdown.js') }}"></script> <!--- dependent dropdown curriculum core ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-service-dropdown.js') }}"></script> <!--- dependent dropdown ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->