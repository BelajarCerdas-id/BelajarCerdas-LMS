<tr class="hover:bg-slate-50 transition">

    <td class="p-5">

        <div class="font-semibold text-slate-800">
            {{ $data->name }}
        </div>

        <div class="text-sm text-slate-500">
            {{ $data->description }}
        </div>

    </td>

    <td class="text-center">

        @if($data->type=="wajib")

            <span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold">

                WAJIB

            </span>

        @else

            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-600 text-xs font-bold">

                PILIHAN

            </span>

        @endif

    </td>

    <td class="text-center">

        <span class="font-semibold text-green-600">

            0 Siswa

        </span>

    </td>

    <td class="text-center">

        <a
            href="{{ route('lms.student-vice-principal.extracurricular-management.detail',[
                'role'=>Auth::user()->role,
                'schoolName'=>Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                'schoolId'=>Auth::user()->SchoolStaffProfile->SchoolPartner->id,
                'extracurricularId'=>$data->id
            ]) }}"
            class="px-5 py-2 rounded-full bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition">

            Detail Ekskul

        </a>

    </td>

</tr>