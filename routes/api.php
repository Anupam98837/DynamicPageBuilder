<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ForgotPasswordController;
use App\Http\Controllers\API\PrivilegeController;
use App\Http\Controllers\API\ModuleController;
use App\Http\Controllers\API\UserPrivilegeController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\DepartmentMenuController;
use App\Http\Controllers\API\HeaderMenuController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Auth Routes 

Route::post('/auth/login',  [UserController::class, 'login']);
Route::post('/auth/logout', [UserController::class, 'logout'])
    ->middleware('checkRole');
Route::get('/auth/check',   [UserController::class, 'authenticateToken']);

// Forgot Password Routes 

Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('auth/forgot-password', [ForgotPasswordController::class, 'requestLink']);
    Route::get ('auth/reset-password/verify', [ForgotPasswordController::class, 'verify']);
    Route::post('auth/reset-password',        [ForgotPasswordController::class, 'reset']);
});


// User Routes 

Route::middleware(['checkRole:admin,director,principal,hod'])->prefix('users')->group(function () {
    Route::get('/',              [UserController::class, 'index']);
    Route::post('/',             [UserController::class, 'store']);
    Route::get('/me',            [UserController::class, 'me']);
    Route::get('/{uuid}',        [UserController::class, 'show']);
    Route::put('/{uuid}',        [UserController::class, 'update']);
    Route::patch('/{uuid}',      [UserController::class, 'update']);
    Route::patch('/{uuid}/password', [UserController::class, 'updatePassword']);
    Route::patch('/{uuid}/image',    [UserController::class, 'updateImage']);
    Route::delete('/{uuid}',     [UserController::class, 'destroy']);
});


// Routes By Sampriti(From W3T)

Route::middleware('checkRole:admin,director,principal,hod,admin,super_admin')->group(function () {
    // -----------------------
    // Modules (list / create)
    // -----------------------
    Route::get('modules', [ModuleController::class, 'index'])->name('modules.index');
    Route::get('modules/archived', [ModuleController::class, 'archived'])->name('modules.archived');
    Route::get('modules/bin', [ModuleController::class, 'bin'])->name('modules.bin');
    Route::post('modules', [ModuleController::class, 'store'])->name('modules.store');

    // -----------------------
    // Module actions (specific first)
    // -----------------------
    // all-with-privileges
    Route::get('modules/all-with-privileges', [ModuleController::class, 'allWithPrivileges'])
        ->name('modules.allWithPrivileges');

    // restore / archive / unarchive / force delete (specific routes before parameter route)
    Route::post('modules/{id}/restore', [ModuleController::class, 'restore'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.restore');

    Route::post('modules/{id}/archive', [ModuleController::class, 'archive'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.archive');

    Route::post('modules/{id}/unarchive', [ModuleController::class, 'unarchive'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.unarchive');

    Route::delete('modules/{id}/force', [ModuleController::class, 'forceDelete'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.forceDelete');

    // reorder (collection action)
    Route::post('modules/reorder', [ModuleController::class, 'reorder'])->name('modules.reorder');

    // single-resource show/update/destroy (allow numeric id or UUID)
    Route::get('modules/{id}', [ModuleController::class, 'show'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.show');

    Route::match(['put', 'patch'], 'modules/{id}', [ModuleController::class, 'update'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.update');

    Route::delete('modules/{id}', [ModuleController::class, 'destroy'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.destroy');

    // -----------------------
    // Privileges (collection first, then specific actions, then single-resource)
    // -----------------------
    Route::get('privileges', [PrivilegeController::class, 'index'])->name('privileges.index'); // optional module_id filter
    Route::get('privileges/archived', [PrivilegeController::class, 'archived'])->name('privileges.archived');
    Route::get('privileges/bin', [PrivilegeController::class, 'bin'])->name('privileges.bin');

    Route::post('privileges', [PrivilegeController::class, 'store'])->name('privileges.store');

    // actions on particular privilege (specific before the param-based show)
    Route::delete('privileges/{id}/force', [PrivilegeController::class, 'forceDelete'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.forceDelete');

    Route::post('privileges/{id}/restore', [PrivilegeController::class, 'restore'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.restore');

    Route::post('privileges/{id}/archive', [PrivilegeController::class, 'archive'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.archive');

    Route::post('privileges/{id}/unarchive', [PrivilegeController::class, 'unarchive'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.unarchive');

    Route::post('privileges/reorder', [PrivilegeController::class, 'reorder'])
        ->name('privileges.reorder'); // expects { ids: [...] }

    // single-resource show/update/destroy — allow id or uuid
    Route::get('privileges/{id}', [PrivilegeController::class, 'show'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.show');

    Route::match(['put', 'patch'], 'privileges/{id}', [PrivilegeController::class, 'update'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.update');

    Route::delete('privileges/{id}', [PrivilegeController::class, 'destroy'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('privileges.destroy');

    // module-specific privileges
    Route::get('modules/{id}/privileges', [PrivilegeController::class, 'forModule'])
        ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('modules.privileges');

    // -----------------------
    // User privilege endpoints
    // -----------------------
    // prefer explicit names and avoid ambiguous "delete" path for semantic clarity
    Route::post('user-privileges/sync',   [UserPrivilegeController::class, 'sync'])->name('user-privileges.sync');
    Route::post('user-privileges/assign', [UserPrivilegeController::class, 'assign'])->name('user-privileges.assign');
    Route::post('user-privileges/unassign', [UserPrivilegeController::class, 'unassign'])->name('user-privileges.unassign');
    Route::post('user-privileges/delete', [UserPrivilegeController::class, 'destroy'])->name('user-privileges.destroy'); // revoke mapping (soft-delete)
    Route::get('user-privileges/list',    [UserPrivilegeController::class, 'list'])->name('user-privileges.list');

    // user lookup routes
    // show by numeric id or uuid (keep constraint to avoid accidental greedy matches)
    Route::get('user/{idOrUuid}', [UserPrivilegeController::class, 'show'])
        ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}')
        ->name('user.show');

    // explicit by-uuid route if you need a looser pattern or different handling
    Route::get('user/by-uuid/{uuid}', [UserPrivilegeController::class, 'byUuid'])
        ->where('uuid', '[0-9a-fA-F\-]{36}')
        ->name('user.byUuid');
});


Route::middleware(['checkRole'])->group(function () {
    // current logged-in user (best for structure.blade.php)
    Route::get('/my/modules', [UserPrivilegeController::class, 'myModules']);

    // admin/self: query params
    Route::get('/users/modules', [UserPrivilegeController::class, 'modulesForUser']);

    // admin/self: path param (id or uuid)
    Route::get('/users/{idOrUuid}/modules', [UserPrivilegeController::class, 'modulesForUserByPath']);
});





// Department Routes 

Route::middleware('checkRole')->group(function () {
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/departments/{identifier}', [DepartmentController::class, 'show']);
});

// Only management roles can MODIFY departments
Route::middleware('checkRole:director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::get('/departments-trash', [DepartmentController::class, 'trash']);
    Route::match(['put', 'patch'], '/departments/{identifier}', [DepartmentController::class, 'update']);
    Route::patch('/departments/{identifier}/toggle-active', [DepartmentController::class, 'toggleActive']);
    Route::delete('/departments/{identifier}', [DepartmentController::class, 'destroy']);
    Route::post('/departments/{identifier}/restore', [DepartmentController::class, 'restore']);
    Route::delete('/departments/{identifier}/force', [DepartmentController::class, 'forceDelete']);
});


// Department Manu route 

Route::middleware('checkRole')->group(function () {
    Route::get('/departments/{department}/menus',            [DepartmentMenuController::class, 'index']);
    Route::get('/departments/{department}/menus-trash',      [DepartmentMenuController::class, 'indexTrash']);
    Route::get('/departments/{department}/menus/tree',       [DepartmentMenuController::class, 'tree']);
    Route::get('/departments/{department}/menus/resolve',    [DepartmentMenuController::class, 'resolve']); // ?slug=
    Route::get('/departments/{department}/menus/{id}',       [DepartmentMenuController::class, 'show']);
});

Route::middleware('checkRole:director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/departments/{department}/menus',                 [DepartmentMenuController::class, 'store']);
    Route::put('/departments/{department}/menus/{id}',             [DepartmentMenuController::class, 'update']);
    Route::patch('/departments/{department}/menus/{id}/toggle-default', [DepartmentMenuController::class, 'toggleDefault']);
    Route::patch('/departments/{department}/menus/{id}/toggle-active',  [DepartmentMenuController::class, 'toggleActive']);
    Route::post('/departments/{department}/menus/reorder',         [DepartmentMenuController::class, 'reorder']);
    Route::delete('/departments/{department}/menus/{id}',          [DepartmentMenuController::class, 'destroy']);
    Route::post('/departments/{department}/menus/{id}/restore',    [DepartmentMenuController::class, 'restore']);
    Route::delete('/departments/{department}/menus/{id}/force',    [DepartmentMenuController::class, 'forceDelete']);
});

// Header Menus 

Route::prefix('/header-menus')->middleware('checkRole:admin,super_admin,director')->group(function () {
    Route::get('/', [HeaderMenuController::class, 'index']);
    Route::get('/tree', [HeaderMenuController::class, 'tree']);
    Route::get('/trash', [HeaderMenuController::class, 'indexTrash']);
    Route::get('/resolve', [HeaderMenuController::class, 'resolve']);
    Route::post('/', [HeaderMenuController::class, 'store']);
    Route::get('{id}', [HeaderMenuController::class, 'show']);
    Route::put('{id}', [HeaderMenuController::class, 'update']);
    Route::delete('{id}', [HeaderMenuController::class, 'destroy']);
    Route::post('{id}/restore', [HeaderMenuController::class, 'restore']);
    Route::delete('{id}/force', [HeaderMenuController::class, 'forceDelete']);
    Route::post('{id}/toggle-active', [HeaderMenuController::class, 'toggleActive']);
    Route::post('/reorder', [HeaderMenuController::class, 'reorder']);
});
