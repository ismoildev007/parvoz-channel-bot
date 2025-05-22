<?php

namespace App\Http\Controllers;

use App\Models\ContestSetting;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(ContestSetting $contest)
    {
        $students = Student::all();
        return view('students.index', compact('contest', 'students'));
    }
    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mentor_name' => 'nullable|string|max:255',
        ]);

        Student::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'mentor_name' => $request->mentor_name,
        ]);

        return redirect()->route('students.index')->with('success', 'Student muvaffaqiyatli qo‘shildi!');
    }
    public function destroyStudent(Student $student)
    {
        $contest = $student->contest;
        $student->delete();
        return redirect()->route('students', $contest)->with('success', 'Talaba o‘chirildi.');
    }
}
