<?php

namespace App\Http\Controllers\Foundation;

use App\Http\Controllers\Controller;
use App\Models\LmsMeetingContent;
use App\Models\SchoolAssessment;
use App\Models\SchoolClass;
use App\Models\SchoolPartner;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TeacherPerformanceController extends Controller
{
    public function teacherPerformanceView($role, $foundationId = null)
    {
        return view('features.lms.foundation.teacher-performance.monitoring-school-teacher-performance', compact('role', 'foundationId'));
    }

    public function loadAcademicYears($role, $foundationId = null)
    {
        $academicYears = collect();

        if ($foundationId) {
            $academicYears = SchoolClass::whereHas('SchoolPartner', function ($query) use ($foundationId) {
                $query->where('school_foundation_id', $foundationId);
            })->select('tahun_ajaran')->distinct()->orderByDesc('tahun_ajaran')->pluck('tahun_ajaran');
        }

        return response()->json([
            'academic_years' => $academicYears,
            'selected_year' => $academicYears->first(),
        ]);
    }

    public function schoolTeacherPerformanceKPI(Request $request, $role, $foundationId = null)
    {
        if ($foundationId) {
            $selectedYear = $request->academic_year;
            
            $schoolIds = SchoolPartner::where('school_foundation_id', $foundationId)->pluck('id')->toArray();
    
            // assessments
            $totalAssessments = SchoolAssessment::whereIn('school_partner_id', $schoolIds)->whereHas('SchoolClass', function ($query) use ($selectedYear) {
                $query->where('tahun_ajaran', $selectedYear);
            })->count();
    
            $publishedAssessments = SchoolAssessment::whereIn('school_partner_id', $schoolIds)->whereHas('SchoolClass', function ($query) use ($selectedYear) {
                $query->where('tahun_ajaran', $selectedYear);
            })->where('status', 'published')->count();
    
            $assessmentPercentage = $totalAssessments > 0 ? round(($publishedAssessments / $totalAssessments) * 100, 1) : 0;
    
            // contents
            $totalContents = LmsMeetingContent::whereIn('school_partner_id', $schoolIds)->whereHas('SchoolClass', function ($query) use ($selectedYear) {
                $query->where('tahun_ajaran', $selectedYear);
            })->count();
    
            $publishedContents = LmsMeetingContent::whereIn('school_partner_id', $schoolIds)->whereHas('SchoolClass', function ($query) use ($selectedYear) {
                $query->where('tahun_ajaran', $selectedYear);
            })->where('is_active', true)->count();
    
            $contentPercentage = $totalContents > 0 ? round(($publishedContents / $totalContents) * 100, 1) : 0;
        }

        return response()->json([
            'assessments' => [
                'total' => $totalAssessments,
                'published' => $publishedAssessments,
                'percentage' => $assessmentPercentage
            ],
            'contents' => [
                'total' => $totalContents,
                'published' => $publishedContents,
                'percentage' => $contentPercentage
            ]
        ]);
    }

    public function paginateSchoolTeacherPerformance(Request $request, $role, $foundationId = null)
    {
        if ($foundationId) {
            $selectedYear = $request->academic_year;
    
            $schools = SchoolPartner::where('school_foundation_id', $foundationId)->orderBy('nama_sekolah')->get();
    
            $result = $schools->map(function ($school) use ($selectedYear) {
    
                // assessments
                $totalAssessments = SchoolAssessment::where('school_partner_id', $school->id)->whereHas('SchoolClass', function ($query) use ($selectedYear) {
                    $query->where('tahun_ajaran', $selectedYear);
                })->count();
    
                $publishedAssessments = SchoolAssessment::where('school_partner_id', $school->id)->whereHas('SchoolClass', function ($query) use ($selectedYear) {
                    $query->where('tahun_ajaran', $selectedYear);
                })->where('status', 'published')->count();
    
                $assessmentPercentage = $totalAssessments > 0 ? round(($publishedAssessments / $totalAssessments) * 100, 1) : 0;
    
                // contents
                $totalContents = LmsMeetingContent::where('school_partner_id', $school->id)->whereHas('SchoolClass', function ($query) use ($selectedYear) {
                    $query->where('tahun_ajaran', $selectedYear);
                })->count();
    
                $publishedContents = LmsMeetingContent::where('school_partner_id', $school->id)->where('is_active', true)->whereHas('SchoolClass', function ($query) use ($selectedYear) {
                    $query->where('tahun_ajaran', $selectedYear);
                })->count();
    
                $contentPercentage = $totalContents > 0 ? round(($publishedContents / $totalContents) * 100, 1) : 0;
    
                return [
                    'school_name' => $school->nama_sekolah,
                    'npsn' => $school->npsn,
                    'total_assessments' => $totalAssessments,
                    'published_assessments' => $publishedAssessments,
                    'assessment_percentage' => $assessmentPercentage,
                    'total_contents' => $totalContents,
                    'published_contents' => $publishedContents,
                    'content_percentage' => $contentPercentage
                ];
            });
    
            $perPage = $request->input('per_page', 10);
    
            $paginated = new LengthAwarePaginator(
                $result->forPage(
                    $request->input('page', 1),
                    $perPage
                )->values(),
    
                $result->count(),
    
                $perPage,
    
                $request->input('page', 1),
    
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        }
        
        return response()->json([
            'data' => $paginated->items(),
            'links' => $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }

    public function schoolTeacherPerformanceChart(Request $request, $role, $foundationId = null)
    {
        $result = collect();
        
        if ($foundationId) {
            $selectedYear = $request->academic_year;
    
            $schools = SchoolPartner::where('school_foundation_id', $foundationId)->orderBy('nama_sekolah')->get();
    
            $schoolIds = $schools->pluck('id');
    
            // assessments
            $assessmentData = SchoolAssessment::select('school_partner_id',DB::raw('COUNT(*) as total'), DB::raw("SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published")
            )->whereIn('school_partner_id', $schoolIds)        ->whereHas('SchoolClass', function ($query) use ($selectedYear) {
                $query->where('tahun_ajaran', $selectedYear);
            })->groupBy('school_partner_id')->get()->keyBy('school_partner_id');
    
            // contents
            $contentData = LmsMeetingContent::select('school_partner_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as published')
            )->whereIn('school_partner_id', $schoolIds)->whereHas('SchoolClass', function ($query) use ($selectedYear) {
                $query->where('tahun_ajaran', $selectedYear);
            })->groupBy('school_partner_id')->get()->keyBy('school_partner_id');
    
            $result = $schools->map(function ($school) use ($assessmentData, $contentData) {
                // assessments
                $assessment = $assessmentData->get($school->id);
                $totalAssessments = $assessment?->total ?? 0;
                $publishedAssessments = $assessment?->published ?? 0;
                $assessmentPercentage = $totalAssessments > 0 ? round(($publishedAssessments / $totalAssessments) * 100, 1) : 0;
    
                // contents
                $content = $contentData->get($school->id);
                $totalContents = $content?->total ?? 0;
                $publishedContents = $content?->published ?? 0;
                $contentPercentage = $totalContents > 0 ? round(($publishedContents / $totalContents) * 100, 1) : 0;
    
                return [
                    'school_name' => $school->nama_sekolah,
                    'assessment_percentage' => $assessmentPercentage,
                    'content_percentage' => $contentPercentage,
                ];
            });
        }

        return response()->json([
            'data' => $result,
        ]);
    }
}