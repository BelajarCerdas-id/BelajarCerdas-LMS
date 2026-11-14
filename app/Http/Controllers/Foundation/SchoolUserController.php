<?php

namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Models\SchoolPartner;
use App\Models\SchoolStaffProfile;
use App\Models\StudentProfile;
use App\Models\UserAccount;

class SchoolUserController extends Controller
{
    public function schoolUserView($role, $foundationId = null)
    {
        return view('features.lms.foundation.school-user.monitoring-school-user', compact('role', 'foundationId'));
    }
    public function schoolUserKPI($role, $foundationId = null)
    {
        $totalUser = UserAccount::where('status_akun', 'aktif')->where(function ($query) use ($foundationId) {

            $query->whereHas('SchoolStaffProfile.SchoolPartner', function ($q) use ($foundationId) {
                $q->where('school_foundation_id', $foundationId);
            })

            ->orWhereHas('StudentProfile.SchoolPartner', function ($q) use ($foundationId) {
                $q->where('school_foundation_id', $foundationId);
            })

            ->orWhereHas('ParentProfile.SchoolPartner', function ($q) use ($foundationId) {
                $q->where('school_foundation_id', $foundationId);
            });
        })->count();

        $totalTeacher = SchoolStaffProfile::whereHas('SchoolPartner', function ($q) use ($foundationId) {
            $q->where('school_foundation_id', $foundationId);

        })->whereHas('UserAccount', function ($q) {
            $q->where('role', 'Guru')->where('status_akun', 'aktif');
        })->count();

        $totalStudent = StudentProfile::whereHas('SchoolPartner', function ($q) use ($foundationId) {
            $q->where('school_foundation_id', $foundationId);

        })->whereHas('UserAccount', function ($q) {
            $q->where('status_akun', 'aktif');
        })->count();

        return response()->json([
            'total_user'    => $totalUser,
            'total_teacher' => $totalTeacher,
            'total_student' => $totalStudent,
        ]);
    }

    public function schoolUserChartBySchool($role, $foundationId = null) 
    {
        $schools = SchoolPartner::where('school_foundation_id', $foundationId)->orderBy('nama_sekolah')->get();

        $users = UserAccount::with(['SchoolStaffProfile', 'StudentProfile', 'ParentProfile'])->where('status_akun', 'aktif')->where(function ($query) use ($foundationId) {
            $query->whereHas('SchoolStaffProfile', function ($q) use ($foundationId) {
                $q->whereHas('SchoolPartner', function ($schoolQuery) use ($foundationId) {
                    $schoolQuery->where('school_foundation_id', $foundationId);
                    }
                );
            })->orWhereHas('StudentProfile', function ($q) use ($foundationId) {
                $q->whereHas('SchoolPartner', function ($schoolQuery) use ($foundationId) {
                        $schoolQuery->where('school_foundation_id', $foundationId);
                    }
                );
            })->orWhereHas('ParentProfile', function ($q) use ($foundationId) {
                $q->whereHas('SchoolPartner', function ($schoolQuery) use ($foundationId) {
                        $schoolQuery->where('school_foundation_id', $foundationId);
                    }
                );
            });
        })->get();

        $schoolRoleCounts = [];

        foreach ($schools as $school) {
            $schoolRoleCounts[$school->id] = [];
        }

        foreach ($users as $user) {
            $schoolId = null;

            if ($user->SchoolStaffProfile) {
                $schoolId = $user->SchoolStaffProfile->school_partner_id;

            } elseif ($user->StudentProfile) {
                $schoolId = $user->StudentProfile->school_partner_id;

            } elseif ($user->ParentProfile) {
                $schoolId = $user->ParentProfile->school_partner_id;
            }

            if (!$schoolId) {
                continue;
            }

            if (!array_key_exists($schoolId, $schoolRoleCounts)) {
                continue;
            }

            $userRole = trim((string) $user->role);

            if ($userRole === '') {
                continue;
            }

            if (!isset($schoolRoleCounts[$schoolId][$userRole])) {
                $schoolRoleCounts[$schoolId][$userRole] = 0;
            }

            $schoolRoleCounts[$schoolId][$userRole]++;
        }

        $allRoles = collect($schoolRoleCounts)->flatMap(function ($roles) {
            return array_keys($roles);
        })->unique()->sort()->values()->all();

        $result = $schools->map(function ($school) use ($schoolRoleCounts, $allRoles) {

            $roles = $schoolRoleCounts[$school->id] ?? [];
                $normalizedRoles = [];


                foreach ($allRoles as $userRole) {
                    $normalizedRoles[$userRole] = $roles[$userRole] ?? 0;
                }

                $total = array_sum($normalizedRoles);

                return [
                    'school_id' => $school->id,
                    'school_name' => $school->nama_sekolah,
                    'roles' => $normalizedRoles,
                    'total' => $total,
                ];
            }
        );

        return response()->json([
            'data' => $result->values(),
            'roles' => $allRoles,
        ]);
    }

    public function schoolUserChartByRole($role, $foundationId = null) 
    {
        $schoolId = request()->get('school_id');

        $users = UserAccount::where('status_akun', 'aktif')->where(function ($query) use ($foundationId, $schoolId) {
            $query->whereHas('SchoolStaffProfile.SchoolPartner', function ($q) use ($foundationId, $schoolId) {
                $q->where('school_foundation_id', $foundationId);
    
                if ($schoolId) {
                    $q->where('id', $schoolId);
                }
            });

            $query->orWhereHas('StudentProfile.SchoolPartner', function ($q) use ($foundationId, $schoolId) {
                $q->where('school_foundation_id', $foundationId);
        
                if ($schoolId) {
                    $q->where('id', $schoolId);
                }
            });
            
            $query->orWhereHas('ParentProfile.SchoolPartner', function ($q) use ( $foundationId, $schoolId) {
                $q->where('school_foundation_id', $foundationId);

                if ($schoolId) {
                    $q->where('id', $schoolId);
                }
            });
        })->select('role')->selectRaw('COUNT(*) as total')->groupBy('role')->orderByDesc('total')->get();

        return response()->json([
            'data' => $users,
        ]);
    }

    public function schoolUserChartByStatus($role, $foundationId = null) 
    {
        $schoolId = request()->get('school_id');

        $users = UserAccount::where(function ($query) use ($foundationId, $schoolId) {
            $query->whereHas('SchoolStaffProfile.SchoolPartner', function ($q) use ($foundationId, $schoolId) {
                $q->where('school_foundation_id', $foundationId);
    
                if ($schoolId) {
                    $q->where('id', $schoolId);
                }
            });

            $query->orWhereHas('StudentProfile.SchoolPartner', function ($q) use ($foundationId, $schoolId) {
                $q->where('school_foundation_id', $foundationId);
        
                if ($schoolId) {
                    $q->where('id', $schoolId);
                }
            });
            
            $query->orWhereHas('ParentProfile.SchoolPartner', function ($q) use ( $foundationId, $schoolId) {
                $q->where('school_foundation_id', $foundationId);

                if ($schoolId) {
                    $q->where('id', $schoolId);
                }
            });
        })->select('status_akun')->selectRaw('COUNT(*) as total')->groupBy('status_akun')->get();

        $statusLabels = [
            'aktif' => 'Aktif',
            'nonaktif' => 'Nonaktif',
        ];

        $result = $users->map(function ($item) use ($statusLabels) {
            return [
                'status' => $item->status_akun,
                'label' => $statusLabels[$item->status_akun] ?? ucfirst($item->status_akun),
                'total' => (int) $item->total,
            ];
        });

        return response()->json([
            'data' => $result->values(),
        ]);
    }
}