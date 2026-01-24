<?php

namespace App\Http\Controllers;

use App\Models\Kurikulum;
use Illuminate\Http\Request;

class SchoolSyllabusController extends Controller
{
    // function kurikulum view
    public function curriculumView($schoolName, $schoolId)
    {
        return view('syllabus-services.school.list-kurikulum', compact('schoolName', 'schoolId'));
    }

    // function paginate kurikulum
    public function paginateCurriculum($schoolName, $schoolId)
    {
        $getCurriculum = Kurikulum::with(['UserAccount.OfficeProfile'])->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'data' => $getCurriculum->items(),
            'links' => (string) $getCurriculum->links(),
            'faseDetail' => '/lms/school-subscription/:schoolName/:schoolId/:curriculumName/:curriculumId/fase',
        ]);
    }
}
