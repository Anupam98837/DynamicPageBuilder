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

/*
|--------------------------------------------------------------------------
| Base Authenticated User (Sanctum)
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::post('/auth/login',  [UserController::class, 'login']);

Route::post('/auth/logout', [UserController::class, 'logout'])
    ->middleware('checkRole');

Route::get('/auth/check',   [UserController::class, 'authenticateToken']);


/*
|--------------------------------------------------------------------------
| Forgot Password Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('auth/forgot-password',        [ForgotPasswordController::class, 'requestLink']);
    Route::get ('auth/reset-password/verify',  [ForgotPasswordController::class, 'verify']);
    Route::post('auth/reset-password',         [ForgotPasswordController::class, 'reset']);
});


/*
|--------------------------------------------------------------------------
| User Routes (Admin / Management)
|--------------------------------------------------------------------------
*/

Route::middleware(['checkRole:admin,director,principal,hod'])
    ->prefix('users')
    ->group(function () {
        Route::get('/',                  [UserController::class, 'index']);
        Route::post('/',                 [UserController::class, 'store']);
        Route::get('/me',                [UserController::class, 'me']);
        Route::get('/{uuid}',            [UserController::class, 'show']);
        Route::put('/{uuid}',            [UserController::class, 'update']);
        Route::patch('/{uuid}',          [UserController::class, 'update']);
        Route::patch('/{uuid}/password', [UserController::class, 'updatePassword']);
        Route::patch('/{uuid}/image',    [UserController::class, 'updateImage']);
        Route::delete('/{uuid}',         [UserController::class, 'destroy']);
    });


/*
|--------------------------------------------------------------------------
| Modules / Privileges / User-Privileges
|--------------------------------------------------------------------------
*/

Route::middleware('checkRole:admin,super_admin,director,principal,hod')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Modules (prefix: modules)
        |--------------------------------------------------------------------------
        */
        Route::prefix('modules')->group(function () {
            // Collection
            Route::get('/',          [ModuleController::class, 'index'])->name('modules.index');
            Route::get('/archived',  [ModuleController::class, 'archived'])->name('modules.archived');
            Route::get('/bin',       [ModuleController::class, 'bin'])->name('modules.bin');
            Route::post('/',         [ModuleController::class, 'store'])->name('modules.store');

            // Extra collection: all-with-privileges
            Route::get('/all-with-privileges', [ModuleController::class, 'allWithPrivileges'])
                ->name('modules.allWithPrivileges');

            // Module actions (specific)
            Route::post('{id}/restore',   [ModuleController::class, 'restore'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.restore');

            Route::post('{id}/archive',   [ModuleController::class, 'archive'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.archive');

            Route::post('{id}/unarchive', [ModuleController::class, 'unarchive'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.unarchive');

            Route::delete('{id}/force',   [ModuleController::class, 'forceDelete'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.forceDelete');

            // Reorder modules
            Route::post('/reorder', [ModuleController::class, 'reorder'])
                ->name('modules.reorder');

            // Single-resource module routes
            Route::get('{id}', [ModuleController::class, 'show'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.show');

            Route::match(['put', 'patch'], '{id}', [ModuleController::class, 'update'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.update');

            Route::delete('{id}', [ModuleController::class, 'destroy'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.destroy');

            // Module-specific privileges (same URL as before: modules/{id}/privileges)
            Route::get('{id}/privileges', [PrivilegeController::class, 'forModule'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.privileges');
        });


        /*
        |--------------------------------------------------------------------------
        | Privileges (prefix: privileges)
        |--------------------------------------------------------------------------
        */
        Route::prefix('privileges')->group(function () {
            // Collection
            Route::get('/',          [PrivilegeController::class, 'index'])->name('privileges.index');
            Route::get('/archived',  [PrivilegeController::class, 'archived'])->name('privileges.archived');
            Route::get('/bin',       [PrivilegeController::class, 'bin'])->name('privileges.bin');

            Route::post('/',         [PrivilegeController::class, 'store'])->name('privileges.store');

            // Bulk update
            Route::post('/bulk-update', [PrivilegeController::class, 'bulkUpdate'])
                ->name('privileges.bulkUpdate');

            // Reorder privileges
            Route::post('/reorder', [PrivilegeController::class, 'reorder'])
                ->name('privileges.reorder'); // expects { ids: [...] }

            // Actions on a specific privilege
            Route::delete('{id}/force', [PrivilegeController::class, 'forceDelete'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.forceDelete');

            Route::post('{id}/restore', [PrivilegeController::class, 'restore'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.restore');

            Route::post('{id}/archive', [PrivilegeController::class, 'archive'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.archive');

            Route::post('{id}/unarchive', [PrivilegeController::class, 'unarchive'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.unarchive');

            // Single privilege show/update/destroy
            Route::get('{id}', [PrivilegeController::class, 'show'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.show');

            Route::match(['put', 'patch'], '{id}', [PrivilegeController::class, 'update'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.update');

            Route::delete('{id}', [PrivilegeController::class, 'destroy'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.destroy');
        });


        /*
        |--------------------------------------------------------------------------
        | User-Privileges (prefix: user-privileges)
        |--------------------------------------------------------------------------
        */
        Route::prefix('user-privileges')->group(function () {
            // Mapping operations
            Route::post('/sync',     [UserPrivilegeController::class, 'sync'])
                ->name('user-privileges.sync');

            Route::post('/assign',   [UserPrivilegeController::class, 'assign'])
                ->name('user-privileges.assign');

            Route::post('/unassign', [UserPrivilegeController::class, 'unassign'])
                ->name('user-privileges.unassign');

            Route::post('/delete',   [UserPrivilegeController::class, 'destroy'])
                ->name('user-privileges.destroy'); // revoke mapping (soft-delete)

            Route::get('/list',      [UserPrivilegeController::class, 'list'])
                ->name('user-privileges.list');
        });

        /*
        |--------------------------------------------------------------------------
        | User lookup related to privileges (same URLs as before)
        |--------------------------------------------------------------------------
        */
        Route::prefix('user')->group(function () {
            Route::get('{idOrUuid}', [UserPrivilegeController::class, 'show'])
                ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('user.show');

            Route::get('by-uuid/{uuid}', [UserPrivilegeController::class, 'byUuid'])
                ->where('uuid', '[0-9a-fA-F\-]{36}')
                ->name('user.byUuid');
        });
    });


/*
|--------------------------------------------------------------------------
| Current User Modules / Other User Modules
|--------------------------------------------------------------------------
*/

Route::middleware(['checkRole'])->group(function () {
    // Modules for current logged-in user
    Route::get('/my/modules', [UserPrivilegeController::class, 'myModules']);

    // Modules for a user via query (?user_id= or ?user_uuid=)
    Route::get('/users/modules', [UserPrivilegeController::class, 'modulesForUser']);

    // Modules for a user via path (id or uuid)
    Route::get('/users/{idOrUuid}/modules', [UserPrivilegeController::class, 'modulesForUserByPath']);
});


/*
|--------------------------------------------------------------------------
| Department Routes
|--------------------------------------------------------------------------
*/

// Read-only departments
Route::middleware('checkRole')->group(function () {
    Route::get('/departments',              [DepartmentController::class, 'index']);
    Route::get('/departments/{identifier}', [DepartmentController::class, 'show']);
});

// Modify departments
Route::middleware('checkRole:director,principal,hod,faculty,technical_assistant,it_person')
    ->group(function () {
        Route::post('/departments',                         [DepartmentController::class, 'store']);
        Route::get('/departments-trash',                    [DepartmentController::class, 'trash']);
        Route::match(['put', 'patch'], '/departments/{identifier}', [DepartmentController::class, 'update']);
        Route::patch('/departments/{identifier}/toggle-active',     [DepartmentController::class, 'toggleActive']);
        Route::delete('/departments/{identifier}',                 [DepartmentController::class, 'destroy']);
        Route::post('/departments/{identifier}/restore',           [DepartmentController::class, 'restore']);
        Route::delete('/departments/{identifier}/force',           [DepartmentController::class, 'forceDelete']);
    });


/*
|--------------------------------------------------------------------------
| Department Menu Routes
|--------------------------------------------------------------------------
*/

// Read-only department menus
Route::middleware('checkRole')->group(function () {
    Route::get('/departments/{department}/menus',         [DepartmentMenuController::class, 'index']);
    Route::get('/departments/{department}/menus-trash',   [DepartmentMenuController::class, 'indexTrash']);
    Route::get('/departments/{department}/menus/tree',    [DepartmentMenuController::class, 'tree']);
    Route::get('/departments/{department}/menus/resolve', [DepartmentMenuController::class, 'resolve']); // ?slug=
    Route::get('/departments/{department}/menus/{id}',    [DepartmentMenuController::class, 'show']);
});

// Modify department menus
Route::middleware('checkRole:director,principal,hod,faculty,technical_assistant,it_person')
    ->group(function () {
        Route::post('/departments/{department}/menus',                 [DepartmentMenuController::class, 'store']);
        Route::put('/departments/{department}/menus/{id}',             [DepartmentMenuController::class, 'update']);
        Route::patch('/departments/{department}/menus/{id}/toggle-default', [DepartmentMenuController::class, 'toggleDefault']);
        Route::patch('/departments/{department}/menus/{id}/toggle-active',  [DepartmentMenuController::class, 'toggleActive']);
        Route::post('/departments/{department}/menus/reorder',         [DepartmentMenuController::class, 'reorder']);
        Route::delete('/departments/{department}/menus/{id}',          [DepartmentMenuController::class, 'destroy']);
        Route::post('/departments/{department}/menus/{id}/restore',    [DepartmentMenuController::class, 'restore']);
        Route::delete('/departments/{department}/menus/{id}/force',    [DepartmentMenuController::class, 'forceDelete']);
    });


/*
|--------------------------------------------------------------------------
| Header Menu Routes
|--------------------------------------------------------------------------
*/

Route::prefix('/header-menus')
    ->middleware('checkRole:admin,super_admin,director')
    ->group(function () {
        Route::get('/',        [HeaderMenuController::class, 'index']);
        Route::get('/tree',    [HeaderMenuController::class, 'tree']);
        Route::get('/trash',   [HeaderMenuController::class, 'indexTrash']);
        Route::get('/resolve', [HeaderMenuController::class, 'resolve']);

        Route::post('/',       [HeaderMenuController::class, 'store']);

        Route::get('{id}',     [HeaderMenuController::class, 'show']);
        Route::put('{id}',     [HeaderMenuController::class, 'update']);
        Route::delete('{id}',  [HeaderMenuController::class, 'destroy']);

        Route::post('{id}/restore',       [HeaderMenuController::class, 'restore']);
        Route::delete('{id}/force',       [HeaderMenuController::class, 'forceDelete']);
        Route::post('{id}/toggle-active', [HeaderMenuController::class, 'toggleActive']);

        Route::post('/reorder', [HeaderMenuController::class, 'reorder']);
    });
