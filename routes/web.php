<?php

use Illuminate\Support\Facades\Route;

// Login Routes 

Route::get('/', function () {
    return view('pages.auth.login');
});


Route::get('/page/{slug}', function () {
    return view('common');
});

Route::view('/landingpage', 'landingPage.home')->name('home');

Route::get('/dashboard', function () {
    return view('pages.users.pages.common.dashboard');
});

Route::get('/user/manage', function () {
    return view('pages.users.pages.users.manageUsers');
});

Route::get('/department/manage', function () {
    return view('pages.users.pages.departments.manageDepartment');
});
Route::get('/department/menu/create', function () {
    return view('pages.users.pages.deptMenu.createMenu');
});
Route::get('/header/menu/create', function () {
    return view('pages.users.pages.landingPage.headerMenus.createHeaderMenu');
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