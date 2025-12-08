<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $studentCount = \App\Models\Student::count();
        $teacherCount = \App\Models\Teacher::count();
        $totalCount = $studentCount + $teacherCount;

        return view('content.pages.admin.AdminDashboard', compact('studentCount', 'teacherCount', 'totalCount'));
    }
}
