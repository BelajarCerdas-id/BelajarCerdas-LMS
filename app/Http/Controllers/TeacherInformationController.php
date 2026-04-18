<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AcademicCalendar;
use App\Models\LessonSchedule;
use App\Models\LessonScheduleItem;
use App\Models\Poll;
use App\Models\PollOption;

class TeacherInformationController extends Controller
{
    /**
     * Menampilkan Halaman Kalender Akademik Guru
     */
    public function teacherCalendarView($role, $schoolName, $schoolId)
    {
        $eventsFromDb = AcademicCalendar::where('school_partner_id', $schoolId)->get();
        
        $savedEvents = [];
        foreach($eventsFromDb as $ev) {
            $savedEvents[] = [
                'date'   => date('Y-m-d', strtotime($ev->date)), 
                'title'  => $ev->title,
                'type'   => $ev->type,
                'color'  => $ev->color,
                'status' => $ev->status
            ];
        }

        return view('features.lms.teacher.information.calender', compact('role', 'schoolName', 'schoolId', 'savedEvents'));
    }

    /**
     * Menyimpan Data Kalender Akademik Guru
     */
    public function saveCalendarData(Request $request, $role, $schoolName, $schoolId)
    {
        try {
            $status = $request->status; 
            $events = $request->events;

            AcademicCalendar::where('school_partner_id', $schoolId)->delete();

            if (!empty($events)) {
                $insertData = [];
                foreach ($events as $event) {
                    $insertData[] = [
                        'school_partner_id' => $schoolId,
                        'date'              => $event['date'],
                        'title'             => $event['title'],
                        'type'              => $event['type'] ?? 'school_event',
                        'color'             => $event['color'] ?? '#F59E0B',
                        'status'            => $status,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
                AcademicCalendar::insert($insertData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kalender berhasil disimpan permanen ke database!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'GAGAL DATABASE: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan Halaman Jadwal Pelajaran
     */
    public function scheduleView($role, $schoolName, $schoolId)
    {
        $timeSlots = [
            ['start' => '07:00', 'end' => '07:45', 'is_break' => false],
            ['start' => '07:45', 'end' => '08:30', 'is_break' => false],
            ['start' => '08:30', 'end' => '09:15', 'is_break' => false],
            ['start' => '09:15', 'end' => '10:00', 'is_break' => false],
            ['start' => '10:00', 'end' => '10:45', 'is_break' => true],
            ['start' => '10:45', 'end' => '11:30', 'is_break' => false],
            ['start' => '11:30', 'end' => '12:15', 'is_break' => false],
            ['start' => '12:15', 'end' => '13:00', 'is_break' => true],
            ['start' => '13:00', 'end' => '13:45', 'is_break' => false],
            ['start' => '13:45', 'end' => '14:30', 'is_break' => false],
            ['start' => '14:30', 'end' => '15:15', 'is_break' => false],
        ];

        $classes = DB::table('school_classes')
            ->where('school_partner_id', $schoolId)
            ->where('status_class', 'active')
            ->select('id', 'class_name', 'kelas_id') 
            ->orderBy('kelas_id', 'asc')
            ->orderBy('class_name', 'asc')
            ->get();

        return view('features.lms.teacher.information.schedule', compact(
            'role', 'schoolName', 'schoolId', 'timeSlots', 'classes'
        ));
    }

    /**
     * Menarik Data Guru/Mapel dan Jadwal Tersimpan via AJAX
     */
    public function getScheduleDataAjax($schoolId, $classId)
    {
        try {
            $classInfo = DB::table('school_classes')
                ->where('id', $classId)
                ->where('school_partner_id', $schoolId)
                ->first();

            if (!$classInfo) {
                return response()->json(['success' => false, 'message' => 'Kelas tidak ditemukan.']);
            }

            $teachersData = DB::table('teacher_mapels')
                ->join('school_staff_profiles', 'teacher_mapels.user_id', '=', 'school_staff_profiles.user_id')
                ->join('mapels', 'teacher_mapels.mapel_id', '=', 'mapels.id')
                ->join('school_classes', 'teacher_mapels.school_class_id', '=', 'school_classes.id')
                ->where('school_staff_profiles.school_partner_id', $schoolId)
                ->where('school_classes.kelas_id', $classInfo->kelas_id)
                ->where(function($query) use ($classInfo) {
                    if ($classInfo->major_id) {
                        $query->where('school_classes.major_id', $classInfo->major_id)
                              ->orWhereNull('school_classes.major_id');
                    } else {
                        $query->whereNull('school_classes.major_id');
                    }
                })
                ->select(
                    'teacher_mapels.user_id', 
                    'teacher_mapels.mapel_id',
                    'school_staff_profiles.nama_lengkap',
                    'mapels.mata_pelajaran'
                )
                ->distinct() 
                ->get();

            $available_mapels = [];
            $colors = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4', '#EAB308'];
            
            foreach ($teachersData as $index => $t) {
                $available_mapels[] = [
                    'id'         => $t->user_id,
                    'name'       => $t->nama_lengkap ?? "Guru " . $t->user_id,
                    'subject_id' => $t->mapel_id,
                    'subject'    => $t->mata_pelajaran, 
                    'color'      => $colors[$index % count($colors)]
                ];
            }

            $parent = LessonSchedule::where('school_partner_id', $schoolId)
                ->where('class_id', $classId)
                ->first();
                
            $formattedSchedules = [];
            if ($parent) {
                $items = LessonScheduleItem::where('lesson_schedule_id', $parent->id)->get();
                foreach ($items as $item) {
                    $formattedSchedules[] = [
                        'day_of_week'  => $item->day_of_week,
                        'start_time'   => substr($item->start_time, 0, 5), 
                        'teacher_id'   => $item->teacher_id,
                        'teacher_name' => $item->teacher_name,
                        'subject_id'   => $item->mapel_id,
                        'subject_name' => $item->subject_name,
                        'color'        => $item->color
                    ];
                }
            }

            return response()->json([
                'success'          => true,
                'available_mapels' => $available_mapels,
                'data'             => $formattedSchedules
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Menyimpan Jadwal
     */
    public function saveSchedule(Request $request, $role, $schoolName, $schoolId)
    {
        $classId = $request->class_id;
        $className = $request->class_name;
        $status = $request->status ?? 'draft';
        $schedules = $request->schedules;

        if (!$classId) {
            return response()->json(['success' => false, 'message' => 'ID Kelas tidak valid.']);
        }

        DB::beginTransaction();
        try {
            // 1. Cek Bentrok
            if (!empty($schedules)) {
                foreach ($schedules as $s) {
                    $clash = DB::table('lesson_schedule_items')
                        ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
                        ->where('lesson_schedules.school_partner_id', $schoolId)
                        ->where('lesson_schedules.class_id', '!=', $classId)
                        ->where('lesson_schedule_items.day_of_week', $s['day'])
                        ->where('lesson_schedule_items.start_time', $s['start_time'])
                        ->where('lesson_schedule_items.teacher_id', $s['teacher_id'])
                        ->select('lesson_schedules.class_name')
                        ->first();

                    if ($clash) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false, 
                            'message' => "BENTROK JADWAL: {$s['teacher_name']} sudah mengajar di kelas {$clash->class_name} pada hari {$s['day']} jam {$s['start_time']}."
                        ]);
                    }
                }
            }

            // 2. Hapus Data Lama
            $existingParent = LessonSchedule::where('school_partner_id', $schoolId)
                ->where('class_id', $classId)
                ->first();

            if ($existingParent) {
                LessonScheduleItem::where('lesson_schedule_id', $existingParent->id)->delete();
                $existingParent->delete();
            }

            // 3. Simpan Parent
            // 3. Simpan Parent (Menggunakan DB::table untuk mengakali database)
            // 3. Simpan Parent (Smart Insert - Anti Error)
            $parentData = [
                'school_partner_id' => $schoolId,
                'class_id'          => $classId,
                'class_name'        => $className,
                'status'            => $status,
                'created_at'        => now(),
                'updated_at'        => now(),
            ];

            // Daftar kolom masa lalu yang mungkin masih nyasar di database
            $legacyColumns = [
                'day_of_week'  => '-',
                'start_time'   => '00:00',
                'end_time'     => '00:00',
                'teacher_id'   => '0',
                'teacher_name' => '-',
                'subject_name' => '-',
                'mapel_id'     => '0',
                'color'        => '-',
            ];

            // Mengecek ke database: Jika kolomnya masih ada, masukkan data dummy. Jika tidak ada, biarkan.
            foreach ($legacyColumns as $col => $dummyValue) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('lesson_schedules', $col)) {
                    $parentData[$col] = $dummyValue;
                }
            }

            $newParentId = DB::table('lesson_schedules')->insertGetId($parentData);

            // 4. Simpan Detail Jadwal (Children)
            if (!empty($schedules)) {
                $items = [];
                $now = now();
                
                foreach ($schedules as $s) {
                    $endTime = date('H:i', strtotime('+45 minutes', strtotime($s['start_time'])));
                    $items[] = [
                        'lesson_schedule_id' => $newParentId, // Menggunakan ID dari trik di atas
                        'teacher_id'         => $s['teacher_id'],
                        'mapel_id'           => $s['subject_id'],
                        'teacher_name'       => $s['teacher_name'],
                        'subject_name'       => $s['subject_name'],
                        'day_of_week'        => $s['day'],
                        'start_time'         => $s['start_time'],
                        'end_time'           => $endTime,
                        'color'              => $s['color'] ?? '#0071BC',
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ];
                }
                LessonScheduleItem::insert($items);
            }

            DB::commit();
            
            $msgStatus = $status === 'published' ? 'dipublikasikan' : 'disimpan sebagai draft';
            return response()->json([
                'success' => true, 
                'message' => "Jadwal kelas {$className} berhasil {$msgStatus}!"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan Halaman Polling
     */
    public function teacherPollingView($role, $schoolName, $schoolId)
    {
        $userId = Auth::id();

        // 1. Ambil daftar kelas yang DIAJAR oleh guru ini saja (Dropdown)
        $classes = \Illuminate\Support\Facades\DB::table('lesson_schedule_items')
            ->join('lesson_schedules', 'lesson_schedule_items.lesson_schedule_id', '=', 'lesson_schedules.id')
            ->where('lesson_schedules.school_partner_id', $schoolId)
            ->where('lesson_schedule_items.teacher_id', $userId)
            ->select('lesson_schedules.class_id', 'lesson_schedules.class_name')
            ->distinct()
            ->get();

        // 2. Ambil riwayat polling yang pernah dibuat guru ini
        // PERBAIKAN: Tambahkan awalan \ pada App\Models\Poll
        $polls = \App\Models\Poll::where('school_partner_id', $schoolId)
            ->where('teacher_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('features.lms.teacher.information.polling', compact('role', 'schoolName', 'schoolId', 'polls', 'classes'));
    }

    public function savePollingData(Request $request, $role, $schoolName, $schoolId)
    {
        try {
            // PERBAIKAN: Tambahkan awalan \ pada App\Models\Poll
            $poll = \App\Models\Poll::create([
                'school_partner_id' => $schoolId,
                'teacher_id'        => Auth::id(),
                'class_id'          => $request->class_id,   
                'class_name'        => $request->class_name, 
                'question'          => $request->question,
                'status'            => 'active',
            ]);

            $optionsData = [];
            foreach ($request->options as $opt) {
                if (!empty($opt)) {
                    $optionsData[] = [
                        'poll_id'     => $poll->id,
                        'option_text' => $opt,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }
            
            // PERBAIKAN: Tambahkan awalan \ pada App\Models\PollOption
            \App\Models\PollOption::insert($optionsData);

            return response()->json(['success' => true, 'message' => 'Polling berhasil dipublikasikan ke kelas ' . $request->class_name]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}