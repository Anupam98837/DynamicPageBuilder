<?php

use Illuminate\Support\Facades\Route;

// Login Routes 


Route::get('/', function () {
    return view('landing.pages.home');
});

//pages route
Route::get('/page/{slug}', function () {
    return view('landing.pages.dynamicPage');
});
Route::get('/login', function () {
    return view('pages.auth.login');
});



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
    return view('pages.users.pages.headerMenus.createHeaderMenu');
});
Route::get('/header/menu/manage', function () {
    return view('pages.users.pages.headerMenus.manageHeaderMenu');
});
Route::get('/page/submenu/create', function () {
    return view('pages.users.pages.pageSubmenus.createPageSubmenu');
});
Route::get('/page/submenu/manage', function () {
    return view('pages.users.pages.pageSubmenus.managePageSubmenu');
});

Route::get('/dashboard-menu/manage', function () {
    return view('modules.dashboardMenu.manageDashboardMenu');
});

Route::get('/dashboard-menu/create', function () {
    return view('modules.dashboardMenu.createDashboardMenu');
});

Route::get('/page-privilege/manage', function () {
    return view('modules.privileges.managePagePrivileges');
});

Route::get('/page-privilege/create', function () {
    return view('modules.privileges.createPagePrivileges');
});
//   Route::get('/admin/privilege/assign/{userId?}', function ($userId = null) {
//         return view('modules.privileges.assignPrivileges', compact('userId'));
//     })->where('userId','[0-9]+')->name('admin.privileges.assign.user');

  // Accept either numeric ID OR UUID via query params
Route::get('/user-privileges/manage', function () {
    $userUuid = request('user_uuid');
    $userId   = request('user_id'); // fallback
    
    return view('modules.privileges.assignPrivileges', [
        'userUuid' => $userUuid,
        'userId'   => $userId,
    ]);
})->name('modules.privileges.assign.user');


Route::get('/contact-info/manage', function () {
    return view('pages.users.pages.contact.manageContactInfo');
});

Route::get('/hero-carousel/manage', function () {
    return view('pages.users.pages.home.manageHeroCarousel');
});

Route::get('/hero-carousel/settings', function () {
    return view('pages.users.pages.home.settingsHeroCarousel');
});

Route::get('/recruiters', function () {
    return view('pages.users.pages.home.recruiters');
});

Route::get('/success-stories/manage', function () {
    return view('pages.users.pages.home.manageSuccessStories');
});

Route::get('/events/manage', function () {
    return view('pages.users.pages.home.manageEvents');
});