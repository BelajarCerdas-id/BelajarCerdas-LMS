<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceRuleController extends Controller
{
    public function index(Service $service)
    {
        return response()->json(
        $service->serviceRule()->select([
                'id',
                'upload_type',
                'allowed_extension',
                'max_size_mb',
                'is_repeatable'
            ])->orderBy('id')->get()
        );
    }
}
