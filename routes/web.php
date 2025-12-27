<?php

use Illuminate\Support\Facades\Route;

// Login Routes 

Route::get('/', function () {
    return view('pages.auth.login');
});


Route::get('/page/{slug}', function () {
    return view('test');
});

Route::view('/landingpage', 'landingPage.home')->name('home');

Route::get('/dashboard', function () {
    return view('pages.users.pages.common.dashboard');
});

Route::get('/user/manage', function () {
    return view('pages.users.pages.users.manageUsers');
});
Route::get('/user/profile/{uuid?}', function () {
    return view('modules.users.userProfile');
});

Route::get('/user/conference-publications/manage', function () {
    return view('pages.users.pages.users.profile.manageConferencePublications');
});
Route::get('/user/education/manage', function () {
    return view('pages.users.pages.users.profile.manageEducations');
});
Route::get('/user/honors/manage', function () {
    return view('pages.users.pages.users.profile.manageHonors');
});
Route::get('/user/journals/manage', function () {
    return view('pages.users.pages.users.profile.manageJournals');
});

Route::get('/user/social-media/manage', function () {
    return view('pages.users.pages.users.profile.manageSocialMedia');
});
Route::get('/user/teaching-engagements/manage', function () {
    return view('pages.users.pages.users.profile.manageTeachingEngagements');
});
Route::get('/user/personal-information/manage', function () {
    return view('pages.users.pages.users.profile.personalInformation');
});

Route::get('/department/manage', function () {
    return view('pages.users.pages.departments.manageDepartment');
});

Route::get('/department/curriculum-syllabus', function () {
    return view('pages.users.pages.departments.manageCurriculumSyllabuses');
});

Route::get('/department/announcements', function () {
    return view('pages.users.pages.departments.manageAnnouncements');
});

Route::get('/department/achievements', function () {
    return view('pages.users.pages.departments.manageAchievements');
});

Route::get('/department/notices', function () {
    return view('pages.users.pages.departments.manageNotices');
});

Route::get('/department/student-activities', function () {
    return view('pages.users.pages.departments.manageStudentActivities');
});

Route::get('/department/gallery', function () {
    return view('pages.users.pages.departments.manageGallery');
});

Route::get('/course/manage', function () {
    return view('pages.users.pages.course.manageCourses');
});
Route::get('/department/menu/create', function () {
    return view('pages.users.pages.deptMenu.createMenu');
});

Route::get('/pages/create', function () {
    return view('pages.users.pages.pages.pageEditor');
});

Route::get('/pages/manage', function () {
    return view('pages.users.pages.pages.managePage');
});

Route::get('/header/menu/create', function () {
    return view('pages.users.pages.landingPage.headerMenus.createHeaderMenu');
});
Route::get('/header/menu/manage', function () {
    return view('pages.users.pages.landingPage.headerMenus.manageHeaderMenu');
});
Route::get('/page/submenu/create', function () {
    return view('pages.users.pages.landingPage.pageSubmenus.createPageSubmenu');
});
Route::get('/page/submenu/manage', function () {
    return view('pages.users.pages.landingPage.pageSubmenus.managePageSubmenu');
});

// Director routes

Route::get('/module/manage', function () {
    return view('modules.module.manageModule');
});

Route::get('/privilege/manage', function () {
    return view('modules.privileges.managePrivileges');
});
//   Route::get('/admin/privilege/assign/{userId?}', function ($userId = null) {
//         return view('modules.privileges.assignPrivileges', compact('userId'));
//     })->where('userId','[0-9]+')->name('admin.privileges.assign.user');

  // Accept either numeric ID OR UUID via query params
Route::get('/user-privileges/manage', function () {
    $userUuid = request('user_uuid');
    $userId   = request('user_id'); // fallback
    
    return view('pages.users.admin.pages.privileges.assignPrivileges', [
        'userUuid' => $userUuid,
        'userId'   => $userId,
    ]);
})->name('modules.privileges.assign.user');


