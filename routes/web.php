<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GuidanceController; 
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    
    //  MAIN REDIRECT GATEKEEPER 
    Route::get('/dashboard', function () {
        $role = Auth::user()->role;
        
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'guidance') {
            return redirect()->route('guidance.dashboard'); 
        }

        return app(StudentController::class)->dashboard(request());
    })->name('dashboard');

    // ADMIN ROUTES 
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard',        [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/manage-accounts',  [AdminController::class, 'manageAccounts'])->name('admin.manageAccounts');
        Route::get('/register-user',    [AdminController::class, 'showRegisterUserForm'])->name('admin.showRegister');

        // Register actions
        Route::post('/register-student',    [AdminController::class, 'registerStudent'])->name('admin.registerStudent');
        Route::post('/register-guidance',   [AdminController::class, 'registerGuidance'])->name('admin.registerGuidance');
        Route::post('/register-user-action',[AdminController::class, 'registerUser'])->name('admin.registerUser');

        // User management
        Route::get('/edit-user/{id}',    [AdminController::class, 'editUser'])->name('admin.editUser');
        Route::post('/update-user/{id}', [AdminController::class, 'updateUser'])->name('admin.updateUser');
        Route::delete('/delete-user/{id}',[AdminController::class, 'deleteUser'])->name('admin.deleteUser');

        // Admin settings 
        Route::patch('/settings/profile',  [AdminController::class, 'updateProfile'])->name('admin.updateProfile');
        Route::patch('/settings/password', [AdminController::class, 'updatePassword'])->name('admin.updatePassword');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
    });

    // STUDENT ROUTES 
    Route::post('/submit-report',           [StudentController::class, 'submitReport'])->name('report.submit');
    Route::get('/my-reports',               [StudentController::class, 'myReports'])->name('student.reports');
    Route::get('/my-reports/{id}',          [StudentController::class, 'viewReport'])->name('student.reports.view');
    Route::patch('/my-reports/{id}/withdraw',[StudentController::class, 'withdrawReport'])->name('student.reports.withdraw');

    // Messaging
    Route::get('/student/messages',              [StudentController::class, 'messagesIndex'])->name('student.messages');
    Route::patch('/student/settings/profile',    [StudentController::class, 'updateProfile'])->name('student.updateProfile');
    Route::patch('/student/settings/password',   [StudentController::class, 'updatePassword'])->name('student.updatePassword');

    // SHARED MESSAGING ACTION
    Route::post('/messages/send', [StudentController::class, 'sendMessage'])->name('messages.send');

    // GUIDANCE ROUTES
    Route::get('/guidance/dashboard',                    [GuidanceController::class, 'index'])->name('guidance.dashboard');
    Route::patch('/guidance/report/{id}/status',         [GuidanceController::class, 'updateStatus'])->name('guidance.updateStatus');
    Route::patch('/guidance/report/{id}/archive',        [GuidanceController::class, 'archive'])->name('guidance.archive');
    Route::patch('/guidance/report/{id}/unarchive',      [GuidanceController::class, 'unarchive'])->name('guidance.unarchive');
    Route::patch('/guidance/settings/profile',           [GuidanceController::class, 'updateProfile'])->name('guidance.updateProfile');
    Route::patch('/guidance/settings/password',          [GuidanceController::class, 'updatePassword'])->name('guidance.updatePassword');

    // PROFILE ROUTES
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    
});

require __DIR__.'/auth.php';