<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ForgotPasswordController;
use App\Http\Controllers\API\PagePrivilegeController;
use App\Http\Controllers\API\DashboardMenuController;
use App\Http\Controllers\API\UserPrivilegeController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\DepartmentMenuController;
use App\Http\Controllers\API\HeaderMenuController;
use App\Http\Controllers\API\PageSubmenuController;
use App\Http\Controllers\API\PublicPageController;
use App\Http\Controllers\API\PageController;
use App\Http\Controllers\API\MediaController;
use App\Http\Controllers\API\UserPersonalInformationController;
use App\Http\Controllers\API\UserHonorsController;
use App\Http\Controllers\API\UserJournalsController;
use App\Http\Controllers\API\UserTeachingEngagementsController;
use App\Http\Controllers\API\UserConferencePublicationsController;
use App\Http\Controllers\API\UserEducationsController;
use App\Http\Controllers\API\UserSocialMediaController;
use App\Http\Controllers\API\UserProfileController;
use App\Http\Controllers\API\CurriculumSyllabusController;
use App\Http\Controllers\API\AnnouncementController;
use App\Http\Controllers\API\AchievementController;
use App\Http\Controllers\API\NoticeController;
use App\Http\Controllers\API\StudentActivityController;
use App\Http\Controllers\API\GalleryController;
use App\Http\Controllers\API\CourseController;


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

    Route::get('/me/profile', [UserProfileController::class,'show']);
    Route::get('/users/{user_uuid}/profile', [UserProfileController::class,'show']);
    

Route::middleware(['checkRole:admin,director,principal,hod'])->group(function () {
    Route::get('/users/{user_uuid}/personal-info', [UserPersonalInformationController::class, 'show']);
    Route::post('/users/{user_uuid}/personal-info', [UserPersonalInformationController::class, 'store']);
    Route::match(['put','patch'], '/users/{user_uuid}/personal-info', [UserPersonalInformationController::class, 'update']);
    Route::delete('/users/{user_uuid}/personal-info', [UserPersonalInformationController::class, 'destroy']);

    Route::post('/users/{user_uuid}/personal-info/restore', [UserPersonalInformationController::class, 'restore']);
});
    
    
Route::middleware(['checkRole:admin,director,principal,hod'])->group(function () {

    // ===========================
    // Honors (Active)
    // ===========================
    Route::get('/users/{user_uuid}/honors', [UserHonorsController::class, 'index']);

    Route::post('/users/{user_uuid}/honors', [UserHonorsController::class, 'store']);

    Route::get('/users/{user_uuid}/honors/{honor_uuid}', [UserHonorsController::class, 'show'])
        ->where('honor_uuid', '[0-9a-fA-F-]{36}');

    Route::match(['put','patch'], '/users/{user_uuid}/honors/{honor_uuid}', [UserHonorsController::class, 'update'])
        ->where('honor_uuid', '[0-9a-fA-F-]{36}');

    // Soft delete (move to trash)
    Route::delete('/users/{user_uuid}/honors/{honor_uuid}', [UserHonorsController::class, 'destroy'])
        ->where('honor_uuid', '[0-9a-fA-F-]{36}');


    // ===========================
    // Honors (Trash / Bin)
    // ===========================
    Route::get('/users/{user_uuid}/honors/deleted', [UserHonorsController::class, 'indexDeleted']);

    // Empty trash (hard delete all deleted)
    Route::delete('/users/{user_uuid}/honors/deleted/force', [UserHonorsController::class, 'forceDeleteAllDeleted']);


    // ===========================
    // Single item restore / hard delete
    // ===========================
    Route::post('/users/{user_uuid}/honors/{honor_uuid}/restore', [UserHonorsController::class, 'restore'])
        ->where('honor_uuid', '[0-9a-fA-F-]{36}');

    Route::delete('/users/{user_uuid}/honors/{honor_uuid}/force', [UserHonorsController::class, 'forceDelete'])
        ->where('honor_uuid', '[0-9a-fA-F-]{36}');
});



Route::middleware(['checkRole:admin,director,principal,hod'])->group(function () {

    // ✅ Active (CRUD)
    Route::get('/users/{user_uuid}/journals', [UserJournalsController::class, 'index']);
    Route::get('/users/{user_uuid}/journals/{journal_uuid}', [UserJournalsController::class, 'show']);
    Route::post('/users/{user_uuid}/journals', [UserJournalsController::class, 'store']);
    Route::match(['put','patch'], '/users/{user_uuid}/journals/{journal_uuid}', [UserJournalsController::class, 'update']);
    Route::delete('/users/{user_uuid}/journals/{journal_uuid}', [UserJournalsController::class, 'destroy']);

    // ✅ Trash / Restore / Hard delete (same pattern as your others)
    Route::get('/users/{user_uuid}/journals/deleted', [UserJournalsController::class, 'indexDeleted']);
    Route::post('/users/{user_uuid}/journals/{journal_uuid}/restore', [UserJournalsController::class, 'restore']);
    Route::delete('/users/{user_uuid}/journals/{journal_uuid}/force', [UserJournalsController::class, 'forceDelete']);
    Route::delete('/users/{user_uuid}/journals/deleted/force', [UserJournalsController::class, 'forceDeleteAllDeleted']);
});



Route::middleware(['checkRole:admin,director,principal,hod'])->group(function () {

    // ✅ Active (CRUD)
    Route::get('/users/{user_uuid}/teaching-engagements', [UserTeachingEngagementsController::class, 'index']);
    Route::post('/users/{user_uuid}/teaching-engagements', [UserTeachingEngagementsController::class, 'store']);
    Route::match(['put','patch'], '/users/{user_uuid}/teaching-engagements/{uuid}', [UserTeachingEngagementsController::class, 'update']);
    Route::delete('/users/{user_uuid}/teaching-engagements/{uuid}', [UserTeachingEngagementsController::class, 'destroy']);

    // ✅ Trash / Restore / Hard delete (same pattern as journals/social)
    Route::get('/users/{user_uuid}/teaching-engagements/deleted', [UserTeachingEngagementsController::class, 'indexDeleted']);
    Route::post('/users/{user_uuid}/teaching-engagements/{uuid}/restore', [UserTeachingEngagementsController::class, 'restore']);
    Route::delete('/users/{user_uuid}/teaching-engagements/{uuid}/force', [UserTeachingEngagementsController::class, 'forceDelete']);
    Route::delete('/users/{user_uuid}/teaching-engagements/deleted/force', [UserTeachingEngagementsController::class, 'forceDeleteAllDeleted']);

});



Route::middleware(['checkRole:admin,director,principal,hod'])->group(function () {

    Route::get(
        '/users/{user_uuid}/conference-publications',
        [UserConferencePublicationsController::class, 'index']
    );

    Route::post(
        '/users/{user_uuid}/conference-publications',
        [UserConferencePublicationsController::class, 'store']
    );
    Route::get(
        '/users/{user_uuid}/conference-publications/{uuid}',
        [UserConferencePublicationsController::class, 'show']
    )->where('uuid', '[0-9a-fA-F-]{36}');

    Route::get(
        '/users/{user_uuid}/conference-publications/deleted',
        [UserConferencePublicationsController::class, 'indexDeleted']
    );

    Route::delete(
        '/users/{user_uuid}/conference-publications/deleted/force',
        [UserConferencePublicationsController::class, 'forceDeleteAllDeleted']
    );
    Route::match(
        ['put','patch'],
        '/users/{user_uuid}/conference-publications/{uuid}',
        [UserConferencePublicationsController::class, 'update']
    )->where('uuid', '[0-9a-fA-F-]{36}');

    Route::delete(
        '/users/{user_uuid}/conference-publications/{uuid}',
        [UserConferencePublicationsController::class, 'destroy']
    )->where('uuid', '[0-9a-fA-F-]{36}');
    Route::post(
        '/users/{user_uuid}/conference-publications/{uuid}/restore',
        [UserConferencePublicationsController::class, 'restore']
    )->where('uuid', '[0-9a-fA-F-]{36}');

    Route::delete(
        '/users/{user_uuid}/conference-publications/{uuid}/force',
        [UserConferencePublicationsController::class, 'forceDelete']
    )->where('uuid', '[0-9a-fA-F-]{36}');
});


Route::middleware(['checkRole:admin,director,principal,hod'])->group(function () {

    Route::get('/users/{user_uuid}/educations', [UserEducationsController::class, 'index']);
    Route::post('/users/{user_uuid}/educations', [UserEducationsController::class, 'store']);

    Route::get('/users/{user_uuid}/educations/{uuid}', [UserEducationsController::class, 'show'])
        ->where('uuid', '[0-9a-fA-F-]{36}');

    Route::get('/users/{user_uuid}/educations/deleted', [UserEducationsController::class, 'indexDeleted']);

    Route::delete('/users/{user_uuid}/educations/deleted/force', [UserEducationsController::class, 'forceDeleteAllDeleted']);

    Route::match(['put','patch'], '/users/{user_uuid}/educations/{uuid}', [UserEducationsController::class, 'update'])
        ->where('uuid', '[0-9a-fA-F-]{36}');

    Route::delete('/users/{user_uuid}/educations/{uuid}', [UserEducationsController::class, 'destroy'])
        ->where('uuid', '[0-9a-fA-F-]{36}');

    Route::post('/users/{user_uuid}/educations/{uuid}/restore', [UserEducationsController::class, 'restore'])
        ->where('uuid', '[0-9a-fA-F-]{36}');

    Route::delete('/users/{user_uuid}/educations/{uuid}/force', [UserEducationsController::class, 'forceDelete'])
        ->where('uuid', '[0-9a-fA-F-]{36}');
});




Route::middleware(['checkRole:admin,director,principal,hod'])->group(function () {

    /* ============================
     * Trash routes (MUST BE FIRST)
     * ============================ */

    Route::get('/users/{user_uuid}/social/deleted', 
        [UserSocialMediaController::class, 'indexDeleted']
    );

    Route::delete('/users/{user_uuid}/social/deleted/force', 
        [UserSocialMediaController::class, 'forceDeleteAllDeleted']
    );

    /* ============================
     * Active CRUD
     * ============================ */

    Route::get('/users/{user_uuid}/social', 
        [UserSocialMediaController::class, 'index']
    );

    Route::post('/users/{user_uuid}/social', 
        [UserSocialMediaController::class, 'store']
    );

    /* ============================
     * Single item routes
     * ============================ */

    Route::match(['put','patch'], 
        '/users/{user_uuid}/social/{uuid}', 
        [UserSocialMediaController::class, 'update']
    )->whereUuid('uuid');

    Route::delete('/users/{user_uuid}/social/{uuid}', 
        [UserSocialMediaController::class, 'destroy']
    )->whereUuid('uuid');

    /* ============================
     * Restore / Permanent delete
     * ============================ */

    Route::post('/users/{user_uuid}/social/{uuid}/restore', 
        [UserSocialMediaController::class, 'restore']
    )->whereUuid('uuid');

    Route::delete('/users/{user_uuid}/social/{uuid}/force', 
        [UserSocialMediaController::class, 'forceDelete']
    )->whereUuid('uuid');
});

/*
|--------------------------------------------------------------------------
| Modules / Pages / User-Privileges
|--------------------------------------------------------------------------
*/

Route::middleware('checkRole:admin,super_admin,director,principal,hod')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Modules (prefix: modules)
        |--------------------------------------------------------------------------
        */
        Route::prefix('dashboard-menus')->group(function () {
            // Collection
            Route::get('/',          [DashboardMenuController::class, 'index'])->name('modules.index');
                    Route::get('/tree',    [DashboardMenuController::class, 'tree']);

            Route::get('/archived',  [DashboardMenuController::class, 'archived'])->name('modules.archived');
            Route::get('/bin',       [DashboardMenuController::class, 'bin'])->name('modules.bin');
            Route::post('/',         [DashboardMenuController::class, 'store'])->name('modules.store');

            // Extra collection: all-with-privileges
            Route::get('/all-with-privileges', [DashboardMenuController::class, 'allWithPrivileges'])
                ->name('modules.allWithPrivileges');

            // Module actions (specific)
            Route::post('{id}/restore',   [DashboardMenuController::class, 'restore'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.restore');

            Route::post('{id}/archive',   [DashboardMenuController::class, 'archive'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.archive');

            Route::post('{id}/unarchive', [DashboardMenuController::class, 'unarchive'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.unarchive');

            Route::delete('{id}/force',   [DashboardMenuController::class, 'forceDelete'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.forceDelete');

            // Reorder modules
            Route::post('/reorder', [DashboardMenuController::class, 'reorder'])
                ->name('modules.reorder');

            // Single-resource module routes
            Route::get('{id}', [DashboardMenuController::class, 'show'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.show');

            Route::match(['put', 'patch'], '{id}', [DashboardMenuController::class, 'update'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.update');

            Route::delete('{id}', [DashboardMenuController::class, 'destroy'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('modules.destroy');

            // Module-specific privileges (same URL as before: modules/{id}/privileges)
            Route::get('{id}/privileges', [PagePrivilegeController::class, 'forModule'])
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
            Route::get('/',          [PagePrivilegeController::class, 'index'])->name('privileges.index');
            Route::get('/index-of-api', [PagePrivilegeController::class, 'indexOfApi']);

            Route::get('/archived',  [PagePrivilegeController::class, 'archived'])->name('privileges.archived');
            Route::get('/bin',       [PagePrivilegeController::class, 'bin'])->name('privileges.bin');

            Route::post('/',         [PagePrivilegeController::class, 'store'])->name('privileges.store');

            // Bulk update
            Route::post('/bulk-update', [PagePrivilegeController::class, 'bulkUpdate'])
                ->name('privileges.bulkUpdate');

            // Reorder privileges
            Route::post('/reorder', [PagePrivilegeController::class, 'reorder'])
                ->name('privileges.reorder'); // expects { ids: [...] }

            // Actions on a specific privilege
            Route::delete('{id}/force', [PagePrivilegeController::class, 'forceDelete'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.forceDelete');

            Route::post('{id}/restore', [PagePrivilegeController::class, 'restore'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.restore');

            Route::post('{id}/archive', [PagePrivilegeController::class, 'archive'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.archive');

            Route::post('{id}/unarchive', [PagePrivilegeController::class, 'unarchive'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.unarchive');

            // Single privilege show/update/destroy
            Route::get('{id}', [PagePrivilegeController::class, 'show'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.show');

            Route::match(['put', 'patch'], '{id}', [PagePrivilegeController::class, 'update'])
                ->where('id', '[0-9]+|[0-9a-fA-F\-]{36}')
                ->name('privileges.update');

            Route::delete('{id}', [PagePrivilegeController::class, 'destroy'])
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
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')
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

    // Public routes (no authentication required)
Route::prefix('/public/header-menus')->group(function () {
    Route::get('/tree', [HeaderMenuController::class, 'publicTree']);
    Route::get('/resolve', [HeaderMenuController::class, 'resolve']);
});

/*
|--------------------------------------------------------------------------
| Page Submenu Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/page-submenus')
    ->middleware('checkRole:admin,super_admin,director')
    ->group(function () {

        Route::get('/pages', [PageSubmenuController::class, 'pages']);

        Route::get('/includables', [PageSubmenuController::class, 'includables']);

        Route::get('/',        [PageSubmenuController::class, 'index']);
        Route::get('/tree',    [PageSubmenuController::class, 'tree']);
        Route::get('/trash',   [PageSubmenuController::class, 'indexTrash']);
        Route::get('/resolve', [PageSubmenuController::class, 'resolve']);

        Route::post('/',       [PageSubmenuController::class, 'store']);

        Route::get('{id}',     [PageSubmenuController::class, 'show']);
        Route::put('{id}',     [PageSubmenuController::class, 'update']);
        Route::delete('{id}',  [PageSubmenuController::class, 'destroy']);

        Route::post('{id}/restore',       [PageSubmenuController::class, 'restore']);
        Route::delete('{id}/force',       [PageSubmenuController::class, 'forceDelete']);
        Route::post('{id}/toggle-active', [PageSubmenuController::class, 'toggleActive']);

        Route::post('/reorder', [PageSubmenuController::class, 'reorder']);
    });

// Public routes (no authentication required)
Route::prefix('/public/page-submenus')->group(function () {
    Route::get('/tree',    [PageSubmenuController::class, 'publicTree']); // requires page_id or page_slug
    Route::get('/resolve', [PageSubmenuController::class, 'resolve']);
    Route::get('/render', [PageSubmenuController::class, 'renderPublic']);
});



Route::prefix('public/pages')->group(function () {
    Route::get('/resolve', [PublicPageController::class, 'resolve']); // ?slug=
});

// Public
Route::get('/public/pages/{identifier}', [PageController::class, 'publicApi']);
 
Route::middleware('checkRole:admin,super_admin,director')->group(function () {
 
    // ===== LISTING (STATIC FIRST) =====
    Route::get('/pages', [PageController::class, 'index']);
    Route::get('/pages/archived', [PageController::class, 'archivedIndex']);
    Route::get('/pages/trash', [PageController::class, 'indexTrash']);
    Route::get('/pages/resolve', [PageController::class, 'resolve']);
 
    // ===== CRUD =====
    Route::post('/pages', [PageController::class, 'store']);
    Route::put('/pages/{identifier}', [PageController::class, 'update']);
    Route::delete('/pages/{identifier}', [PageController::class, 'destroy']);
 
    // ===== STATE ACTIONS =====
    Route::post('/pages/{identifier}/archive', [PageController::class, 'archive']);
    Route::post('/pages/{identifier}/restore', [PageController::class, 'restorePage']);
    Route::delete('/pages/{identifier}/force', [PageController::class, 'hardDelete']);
    Route::post('/pages/{identifier}/toggle-status', [PageController::class, 'toggleStatus']);
 
    // ===== DYNAMIC (MUST BE LAST) =====
    Route::get('/pages/{identifier}', [PageController::class, 'show']);
});
 

/*
|--------------------------------------------------------------------------
| Media Manage
|--------------------------------------------------------------------------
*/
Route::prefix('media')->group(function(){
    Route::get('/',          [MediaController::class, 'index']);
    Route::post('/',         [MediaController::class, 'store']);
    Route::delete('{id}',    [MediaController::class, 'destroy']);
});


/*
|--------------------------------------------------------------------------
| Curriculum & Syllabus Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    // Global listing + show
    Route::get('/curriculum-syllabuses',              [CurriculumSyllabusController::class, 'index']);
    Route::get('/curriculum-syllabuses/{identifier}', [CurriculumSyllabusController::class, 'show']);

    // Nested listing + show (department can be id|uuid|slug)
    Route::get('/departments/{department}/curriculum-syllabuses',                 [CurriculumSyllabusController::class, 'indexByDepartment']);
    Route::get('/departments/{department}/curriculum-syllabuses/{identifier}',    [CurriculumSyllabusController::class, 'showByDepartment']);

    // Preview + Download
    Route::get('/curriculum-syllabuses/{identifier}/stream',   [CurriculumSyllabusController::class, 'stream']);
    Route::get('/curriculum-syllabuses/{identifier}/download', [CurriculumSyllabusController::class, 'download']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    // Create
    Route::post('/curriculum-syllabuses', [CurriculumSyllabusController::class, 'store']);

    // Create under department (optional helper)
    Route::post('/departments/{department}/curriculum-syllabuses', [CurriculumSyllabusController::class, 'storeForDepartment']);

    // Trash listing
    Route::get('/curriculum-syllabuses-trash', [CurriculumSyllabusController::class, 'trash']);

    // Update
    Route::match(['put', 'patch'], '/curriculum-syllabuses/{identifier}', [CurriculumSyllabusController::class, 'update']);

    // Toggle active
    Route::patch('/curriculum-syllabuses/{identifier}/toggle-active', [CurriculumSyllabusController::class, 'toggleActive']);

    // Soft delete / Restore / Force delete
    Route::delete('/curriculum-syllabuses/{identifier}',       [CurriculumSyllabusController::class, 'destroy']);
    Route::post('/curriculum-syllabuses/{identifier}/restore', [CurriculumSyllabusController::class, 'restore']);
    Route::delete('/curriculum-syllabuses/{identifier}/force', [CurriculumSyllabusController::class, 'forceDelete']);
});

// Public (no auth) - for website render page
Route::prefix('public')->group(function () {
    Route::get('/departments/{department}/curriculum-syllabuses', [CurriculumSyllabusController::class, 'publicIndexByDepartment']);
    Route::get('/curriculum-syllabuses/{identifier}/stream',      [CurriculumSyllabusController::class, 'publicStream']);
    Route::get('/curriculum-syllabuses/{identifier}/download',    [CurriculumSyllabusController::class, 'publicDownload']);
});
/*
|--------------------------------------------------------------------------
| Announcements Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/announcements',                 [AnnouncementController::class, 'index']);
    Route::get('/announcements/{identifier}',    [AnnouncementController::class, 'show']);

    Route::get('/departments/{department}/announcements',              [AnnouncementController::class, 'indexByDepartment']);
    Route::get('/departments/{department}/announcements/{identifier}', [AnnouncementController::class, 'showByDepartment']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/announcements', [AnnouncementController::class, 'store']);
    Route::post('/departments/{department}/announcements', [AnnouncementController::class, 'storeForDepartment']);

    Route::get('/announcements-trash', [AnnouncementController::class, 'trash']);

    Route::match(['put','patch'], '/announcements/{identifier}', [AnnouncementController::class, 'update']);
    Route::patch('/announcements/{identifier}/toggle-featured',  [AnnouncementController::class, 'toggleFeatured']);

    Route::delete('/announcements/{identifier}',        [AnnouncementController::class, 'destroy']);
    Route::post('/announcements/{identifier}/restore',  [AnnouncementController::class, 'restore']);
    Route::delete('/announcements/{identifier}/force',  [AnnouncementController::class, 'forceDelete']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('/announcements', [AnnouncementController::class, 'publicIndex']);
    Route::get('/announcements/{identifier}', [AnnouncementController::class, 'publicShow']);

    Route::get('/departments/{department}/announcements', [AnnouncementController::class, 'publicIndexByDepartment']);
});


/*
|--------------------------------------------------------------------------
| Achievements Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/achievements',              [AchievementController::class, 'index']);
    Route::get('/achievements/{identifier}', [AchievementController::class, 'show']);

    Route::get('/departments/{department}/achievements',              [AchievementController::class, 'indexByDepartment']);
    Route::get('/departments/{department}/achievements/{identifier}', [AchievementController::class, 'showByDepartment']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/achievements', [AchievementController::class, 'store']);
    Route::post('/departments/{department}/achievements', [AchievementController::class, 'storeForDepartment']);

    Route::get('/achievements-trash', [AchievementController::class, 'trash']);

    Route::match(['put','patch'], '/achievements/{identifier}', [AchievementController::class, 'update']);
    Route::patch('/achievements/{identifier}/toggle-featured',  [AchievementController::class, 'toggleFeatured']);

    Route::delete('/achievements/{identifier}',       [AchievementController::class, 'destroy']);
    Route::post('/achievements/{identifier}/restore', [AchievementController::class, 'restore']);
    Route::delete('/achievements/{identifier}/force', [AchievementController::class, 'forceDelete']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('/achievements',              [AchievementController::class, 'publicIndex']);
    Route::get('/achievements/{identifier}', [AchievementController::class, 'publicShow']);

    Route::get('/departments/{department}/achievements', [AchievementController::class, 'publicIndexByDepartment']);
});

/*
|--------------------------------------------------------------------------
| Notices Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/notices',              [NoticeController::class, 'index']);
    Route::get('/notices/{identifier}', [NoticeController::class, 'show']);

    Route::get('/departments/{department}/notices',              [NoticeController::class, 'indexByDepartment']);
    Route::get('/departments/{department}/notices/{identifier}', [NoticeController::class, 'showByDepartment']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/notices', [NoticeController::class, 'store']);
    Route::post('/departments/{department}/notices', [NoticeController::class, 'storeForDepartment']);

    Route::get('/notices-trash', [NoticeController::class, 'trash']);

    Route::match(['put','patch'], '/notices/{identifier}', [NoticeController::class, 'update']);
    Route::patch('/notices/{identifier}/toggle-featured',  [NoticeController::class, 'toggleFeatured']);

    Route::delete('/notices/{identifier}',       [NoticeController::class, 'destroy']);
    Route::post('/notices/{identifier}/restore', [NoticeController::class, 'restore']);
    Route::delete('/notices/{identifier}/force', [NoticeController::class, 'forceDelete']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('/notices',              [NoticeController::class, 'publicIndex']);
    Route::get('/notices/{identifier}', [NoticeController::class, 'publicShow']);

    Route::get('/departments/{department}/notices', [NoticeController::class, 'publicIndexByDepartment']);
});


/*
|--------------------------------------------------------------------------
| Student Activities Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/student-activities',              [StudentActivityController::class, 'index']);
    Route::get('/student-activities/{identifier}', [StudentActivityController::class, 'show']);

    Route::get('/departments/{department}/student-activities',              [StudentActivityController::class, 'indexByDepartment']);
    Route::get('/departments/{department}/student-activities/{identifier}', [StudentActivityController::class, 'showByDepartment']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/student-activities', [StudentActivityController::class, 'store']);
    Route::post('/departments/{department}/student-activities', [StudentActivityController::class, 'storeForDepartment']);

    Route::get('/student-activities-trash', [StudentActivityController::class, 'trash']);

    Route::put('/student-activities/{identifier}', [StudentActivityController::class, 'update']);

    Route::post('/student-activities/{identifier}/toggle-featured', [StudentActivityController::class, 'toggleFeatured']);

    Route::delete('/student-activities/{identifier}', [StudentActivityController::class, 'destroy']);
    Route::post('/student-activities/{identifier}/restore', [StudentActivityController::class, 'restore']);
    Route::delete('/student-activities/{identifier}/force', [StudentActivityController::class, 'forceDelete']);
});

// Public (no auth)
Route::get('/public/student-activities',              [StudentActivityController::class, 'publicIndex']);
Route::get('/public/student-activities/{identifier}', [StudentActivityController::class, 'publicShow']);
Route::get('/public/departments/{department}/student-activities', [StudentActivityController::class, 'publicIndexByDepartment']);



/*
|--------------------------------------------------------------------------
| Gallery Routes
|--------------------------------------------------------------------------
*/

// Public (no auth)
Route::get('/public/gallery',                          [GalleryController::class, 'publicIndex']);
Route::get('/public/departments/{department}/gallery', [GalleryController::class, 'publicIndexByDepartment']);
Route::get('/public/gallery/{identifier}',             [GalleryController::class, 'publicShow']);

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/gallery',              [GalleryController::class, 'index']);
    Route::get('/gallery/{identifier}', [GalleryController::class, 'show']);

    Route::get('/departments/{department}/gallery',              [GalleryController::class, 'indexByDepartment']);
    Route::get('/departments/{department}/gallery/{identifier}', [GalleryController::class, 'showByDepartment']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/gallery', [GalleryController::class, 'store']);
    Route::post('/departments/{department}/gallery', [GalleryController::class, 'storeForDepartment']);

    Route::get('/gallery-trash', [GalleryController::class, 'trash']);

    Route::put('/gallery/{identifier}', [GalleryController::class, 'update']);

    Route::patch('/gallery/{identifier}/toggle-featured', [GalleryController::class, 'toggleFeatured']);

    Route::delete('/gallery/{identifier}', [GalleryController::class, 'destroy']);
    Route::post('/gallery/{identifier}/restore', [GalleryController::class, 'restore']);
    Route::delete('/gallery/{identifier}/force-delete', [GalleryController::class, 'forceDelete']);
});


/*
|--------------------------------------------------------------------------
| Courses Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/courses',                 [CourseController::class, 'index']);
    Route::get('/courses/{identifier}',    [CourseController::class, 'show']);

    Route::get('/departments/{department}/courses',              [CourseController::class, 'indexByDepartment']);
    Route::get('/departments/{department}/courses/{identifier}', [CourseController::class, 'showByDepartment']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/courses', [CourseController::class, 'store']);
    Route::post('/departments/{department}/courses', [CourseController::class, 'storeForDepartment']);

    Route::get('/courses-trash', [CourseController::class, 'trash']);

    Route::match(['put','patch'], '/courses/{identifier}', [CourseController::class, 'update']);
    Route::patch('/courses/{identifier}/toggle-featured',  [CourseController::class, 'toggleFeatured']);

    Route::delete('/courses/{identifier}',       [CourseController::class, 'destroy']);
    Route::post('/courses/{identifier}/restore', [CourseController::class, 'restore']);
    Route::delete('/courses/{identifier}/force', [CourseController::class, 'forceDelete']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('/courses',              [CourseController::class, 'publicIndex']);
    Route::get('/courses/{identifier}', [CourseController::class, 'publicShow']);

    Route::get('/departments/{department}/courses', [CourseController::class, 'publicIndexByDepartment']);
});
