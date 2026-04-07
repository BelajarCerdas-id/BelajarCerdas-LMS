<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $schoolId = $user->StudentProfile->school_partner_id ?? null;
        $studentId = $user->id;

        $schoolName = 'Belum Ada Sekolah';
        if ($schoolId) {
            $schoolRecord = \Illuminate\Support\Facades\DB::table('school_partners')
                ->where('id', $schoolId)
                ->first();
                
            if ($schoolRecord) {

                $schoolName = $schoolRecord->school_name ?? $schoolRecord->name ?? 'Sekolah Mitra';
            }
        }

        $studentClass = 'Belum Ada Kelas';
        $studentClassId = null; 
        
        $classRecord = \Illuminate\Support\Facades\DB::table('student_school_classes')
            ->join('school_classes', 'student_school_classes.school_class_id', '=', 'school_classes.id')
            ->where('student_school_classes.student_id', $studentId)
            ->where('student_school_classes.student_class_status', 'active') 
            ->select('school_classes.class_name', 'student_school_classes.school_class_id') 
            ->first();

        if ($classRecord) {
            $studentClass = $classRecord->class_name;
            $studentClassId = $classRecord->school_class_id; 
        }


        $currentMonth = date('m');
        $currentYear = date('Y');
        $allAgenda = [];
        
        if ($schoolId) {
            $dbEvents = \App\Models\AcademicCalendar::where('school_partner_id', $schoolId)
                ->where('status', 'published') 
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->get();

            foreach ($dbEvents as $ev) {
                $allAgenda[] = [
                    'date' => $ev->date,
                    'title' => $ev->title,
                    'color' => $ev->color
                ];
            }
        }

        $nationalHolidays = [
            "2026-01-01" => "Tahun Baru 2026 Masehi", 
            "2026-01-16" => "Isra Mikraj Nabi Muhammad SAW",
            "2026-02-16" => "Cuti Bersama Imlek",
            "2026-02-17" => "Tahun Baru Imlek 2577 Kongzili", 
            "2026-03-18" => "Cuti Bersama Nyepi",
            "2026-03-19" => "Hari Suci Nyepi",
            "2026-03-20" => "Cuti Bersama Idul Fitri", 
            "2026-03-21" => "Idul Fitri 1447 Hijriah", 
            "2026-03-22" => "Idul Fitri 1447 Hijriah",
            "2026-03-23" => "Cuti Bersama Idul Fitri", 
            "2026-03-24" => "Cuti Bersama Idul Fitri", 
            "2026-04-03" => "Wafat Yesus Kristus", 
            "2026-04-05" => "Hari Paskah",
            "2026-05-01" => "Hari Buruh Internasional", 
            "2026-05-14" => "Kenaikan Yesus Kristus",
            "2026-05-15" => "Cuti Bersama Kenaikan Yesus Kristus",
            "2026-05-27" => "Idul Adha 1447 Hijriah", 
            "2026-05-28" => "Cuti Bersama Idul Adha", 
            "2026-05-31" => "Hari Raya Waisak 2570 BE",
            "2026-06-01" => "Hari Lahir Pancasila", 
            "2026-06-16" => "Tahun Baru Islam 1448 Hijriah",
            "2026-08-17" => "Proklamasi Kemerdekaan RI", 
            "2026-08-25" => "Maulid Nabi Muhammad SAW",
            "2026-12-24" => "Cuti Bersama Natal",
            "2026-12-25" => "Hari Raya Natal"
        ];
        
        $prefixFilter = $currentYear . '-' . $currentMonth;
        foreach ($nationalHolidays as $date => $title) {
            if (strpos($date, $prefixFilter) === 0) {
                $allAgenda[] = ['date' => $date, 'title' => $title, 'color' => '#B91C1C'];
            }
        }

        usort($allAgenda, function ($a, $b) { return strtotime($a['date']) - strtotime($b['date']); });
        $monthlyEvents = json_decode(json_encode($allAgenda));

        $hariInggris = date('l');
        $mapHari = ['Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu', 'Sunday'=>'Minggu'];
        $hariIni = $mapHari[$hariInggris] ?? 'Senin';

        $dbSchedules = [];

        if ($schoolId && $studentClassId) {
            $dbSchedules = \Illuminate\Support\Facades\DB::table('lesson_schedule_items')
                ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
                ->leftJoin('users', 'lesson_schedule_items.teacher_id', '=', 'users.id') 
                ->where('lesson_schedules.school_partner_id', $schoolId)
                ->where('lesson_schedules.class_id', $studentClassId) 
                ->where('lesson_schedule_items.day_of_week', $hariIni) 
                ->where('lesson_schedules.status', 'published') 
                ->orderBy('lesson_schedule_items.start_time', 'asc')
                ->select(
                    'lesson_schedule_items.start_time',
                    'lesson_schedule_items.end_time',
                    'lesson_schedule_items.subject_name',
                    'lesson_schedules.class_name',
                    'users.name as teacher_name'
                )
                ->get();
        }

        $jadwalHariIni = [];
        foreach ($dbSchedules as $jadwal) {
            $jadwalHariIni[] = [
                'is_break'   => false,
                'start_time' => $jadwal->start_time,
                'jam'        => substr($jadwal->start_time, 0, 5) . ' - ' . substr($jadwal->end_time, 0, 5),
                'mapel'      => $jadwal->subject_name,
                'guru'       => $jadwal->teacher_name ?? 'Guru', 
                'ruang'      => $jadwal->class_name,
                'color'      => '#0071BC'
            ];
        }

        if (in_array($hariIni, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])) {
            $jadwalHariIni[] = ['is_break' => true, 'start_time' => '10:00:00', 'jam' => '10:00 - 10:45', 'mapel' => 'ISTIRAHAT PERTAMA', 'color' => '#f97316'];
            $jadwalHariIni[] = ['is_break' => true, 'start_time' => '12:15:00', 'jam' => '12:15 - 13:00', 'mapel' => 'ISTIRAHAT KEDUA', 'color' => '#f97316'];
        }
        
        usort($jadwalHariIni, function ($a, $b) { return strcmp($a['start_time'], $b['start_time']); });
        $activePolls = [];
        $votedPolls = [];

        if ($schoolId) {
            $polls = \App\Models\Poll::where('school_partner_id', $schoolId)
                        ->where('status', 'active')
                        ->where(function ($query) use ($studentClassId) {
                            $query->where('class_id', $studentClassId)
                                  ->orWhereNull('class_id'); 
                        })
                        ->orderBy('created_at', 'desc')
                        ->get();

            foreach ($polls as $poll) {
                $options = \App\Models\PollOption::where('poll_id', $poll->id)->get();
                $hasVoted = \App\Models\PollVote::where('poll_id', $poll->id)
                                ->where('student_id', $studentId)
                                ->exists();
                $totalVotes = \App\Models\PollVote::where('poll_id', $poll->id)->count();

                $formattedOptions = [];
                foreach ($options as $opt) {
                    $votesForOption = \App\Models\PollVote::where('poll_option_id', $opt->id)->count();
                    $percentage = $totalVotes > 0 ? round(($votesForOption / $totalVotes) * 100) : 0;
                    
                    $formattedOptions[] = (object)[
                        'id'         => $opt->id,
                        'text'       => $opt->option_text,
                        'votes'      => $votesForOption,
                        'percentage' => $percentage
                    ];
                }

                $pollData = (object)[
                    'id'          => $poll->id,
                    'question'    => $poll->question,
                    'total_votes' => $totalVotes,
                    'options'     => $formattedOptions
                ];

                if ($hasVoted) {
                    $votedPolls[] = $pollData; 
                } else {
                    $activePolls[] = $pollData; 
                }
            }
        }

        $pengumumanTerkini = [];
        $totalSiswaKelas = 1; 
        
        if ($schoolId && $studentClassId) {

            $totalSiswaKelas = \Illuminate\Support\Facades\DB::table('student_school_classes')
                ->where('school_class_id', $studentClassId)
                ->where('student_class_status', 'active')
                ->count();

            $pengumumanTerkini = \Illuminate\Support\Facades\DB::table('announcements')
                ->where('school_partner_id', $schoolId)
                ->where(function ($query) use ($studentClassId) {
                    $query->where('target_class_id', $studentClassId)
                          ->orWhereNull('target_class_id'); 
                })
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();
                
            
        }

        return view('features.lms.students.dashboard', compact(
            'schoolName',
            'monthlyEvents', 
            'jadwalHariIni', 
            'hariIni', 
            'studentClass', 
            'activePolls', 
            'votedPolls',
            'pengumumanTerkini', 
            'totalSiswaKelas'    
        ));
    }

    public function submitVote(Request $request)
    {
        try {
            $userId = Auth::id();
            $pollId = $request->poll_id;
            $optionId = $request->option_id;

            $sudahVote = \App\Models\PollVote::where('poll_id', $pollId)
                            ->where('student_id', $userId)
                            ->exists();

            if ($sudahVote) {
                return response()->json(['success' => false, 'message' => 'Kamu sudah pernah mengisi polling ini!']);
            }

            \App\Models\PollVote::insert([
                'poll_id'        => $pollId,
                'poll_option_id' => $optionId,
                'student_id'     => $userId,
                'created_at'     => now(),
                'updated_at'     => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Suaramu berhasil direkam!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error Sistem: ' . $e->getMessage()], 500);
        }
    }
}