{{-- Proctor sidebar menu: shared by every role section except Siswa, Finance and Orang Tua.
    Shows the school-scoped items only when a school can be derived from the user's
    staff profile; the PIC approval item only for users flagged as unlock approvers.
    Platform admins (no school profile) get the PIC-management page instead. --}}
@php
    $user = Auth::user();
    $proctorSchool = $user?->SchoolStaffProfile?->SchoolPartner ?? null;
    $isPic = (bool) ($user?->can_approve_unlock ?? false);
    $isApproverAdmin = \App\Services\Proctor\ProctorService::hasRole($user, \App\Services\Proctor\ProctorService::APPROVER_ADMIN_ROLES);
@endphp

@if ($proctorSchool || $isPic || $isApproverAdmin)
    <li class="list-item">
        <div class="dropdown-menu w-full flex flex-col items-start">
            <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                <i class="fa-solid fa-eye text-[15px] w-5 text-center"></i>
                <span class="text-[14px]">Proctor</span>
                <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
            </div>

            <div class="content-dropdown pl-6 pr-3.5 w-full">
                <div class="flex flex-col">
                    @if ($proctorSchool)
                        <a href="{{ route('lms.proctor.view', [
                                'role' => $user->role,
                                'schoolName' => $proctorSchool->nama_sekolah,
                                'schoolId' => $proctorSchool->id
                            ]) }}" class="link-href block py-2 text-[12px]">
                            Proctor Ujian
                        </a>
                        <a href="{{ route('lms.proctor.timeline.view', [
                                'role' => $user->role,
                                'schoolName' => $proctorSchool->nama_sekolah,
                                'schoolId' => $proctorSchool->id
                            ]) }}" class="link-href block py-2 text-[12px]">
                            Timeline Proctor
                        </a>
                    @endif

                    @if ($proctorSchool && $isPic)
                        <a href="{{ route('lms.proctor.pic.view', [
                                'role' => $user->role,
                                'schoolName' => $proctorSchool->nama_sekolah,
                                'schoolId' => $proctorSchool->id
                            ]) }}" class="link-href block py-2 text-[12px]">
                            Persetujuan Buka Kunci (PIC)
                        </a>
                    @endif

                    @if ($isApproverAdmin && !$proctorSchool)
                        <a href="{{ route('admin.proctorApprovers.view') }}" class="link-href block py-2 text-[12px]">
                            Kelola PIC Buka Kunci
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </li>
@endif
