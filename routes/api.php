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
use App\Http\Controllers\API\ContactInfoController;
use App\Http\Controllers\API\HeroCarouselController;
use App\Http\Controllers\API\HeroCarouselSettingsController;
use App\Http\Controllers\API\RecruiterController;
use App\Http\Controllers\API\SuccessStoryController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\PlacedStudentController;
use App\Http\Controllers\API\SuccessfulEntrepreneurController;
use App\Http\Controllers\API\HeaderComponentController;
use App\Http\Controllers\API\PlacementNoticeController;
use App\Http\Controllers\API\CareerNoticeController;
use App\Http\Controllers\API\WhyUsController;
use App\Http\Controllers\API\ScholarshipController;
use App\Http\Controllers\API\AlumniSpeakController;
use App\Http\Controllers\API\CenterIframeController;
use App\Http\Controllers\API\StatsController;
use App\Http\Controllers\API\NoticeMarqueeController;
use App\Http\Controllers\API\GrandHomepageController;
use App\Http\Controllers\API\FooterComponentController;
use App\Http\Controllers\API\ContactUsController;
use App\Http\Controllers\API\ContactUsPageVisibilityController;
use App\Http\Controllers\API\CourseSemesterController;
use App\Http\Controllers\API\CourseSemesterSectionController;
use App\Http\Controllers\API\FeedbackController;
use App\Http\Controllers\API\SubjectController;
use App\Http\Controllers\API\FeedbackQuestionController;
use App\Http\Controllers\API\FeedbackPostController;
use App\Http\Controllers\API\FeedbackSubmissionController;
use App\Http\Controllers\API\FeedbackResultsController;
use App\Http\Controllers\API\TopHeaderMenuController;
use App\Http\Controllers\API\StudentAcademicDetailsController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\FacultyPreviewOrderController;
use App\Http\Controllers\API\StickyButtonController;
use App\Http\Controllers\API\MasterApprovalController;
use App\Http\Controllers\API\StudentSubjectController;
use App\Http\Controllers\API\TechnicalAssistantPreviewOrderController;
use App\Http\Controllers\API\PlacementOfficerPreviewOrderController;
use App\Http\Controllers\API\UserActivityLogsController;
use App\Http\Controllers\API\MetaTagController;
use App\Http\Controllers\API\AlumniController;
use App\Http\Controllers\API\ProgramTopperController;

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

Route::middleware('checkRole:admin,director,principal')
    ->get('/admin/dashboard', [DashboardController::class, 'adminDashboard']);

// ✅ HOD Dashboard
Route::middleware('checkRole:hod,admin,director,principal')
    ->get('/hod/dashboard', [DashboardController::class, 'hodDashboard']);

// ✅ Technical Assistant Dashboard
Route::middleware('checkRole:technical_assistant,admin,director,principal')
    ->get('/technical-assistant/dashboard', [DashboardController::class, 'technicalAssistantDashboard']);

// ✅ Placement Officer Dashboard
Route::middleware('checkRole:placement_officer,admin,director,principal')
    ->get('/placement-officer/dashboard', [DashboardController::class, 'placementOfficerDashboard']);

// ✅ IT Person Dashboard
Route::middleware('checkRole:it_person,admin,director,principal')
    ->get('/it-person/dashboard', [DashboardController::class, 'itPersonDashboard']);

// ✅ Faculty Dashboard
Route::middleware('checkRole:faculty,admin,director,principal')
    ->get('/faculty/dashboard', [DashboardController::class, 'facultyDashboard']);

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

Route::middleware(['checkRole:admin,director,principal,hod,student,technical_assistant,placement_officer,it_person,faculty'])
    ->prefix('users')
    ->group(function () {
        Route::get('/',                  [UserController::class, 'index']);
        Route::post('/',                 [UserController::class, 'store']);
        Route::get('/me',                [UserController::class, 'me']);
        Route::patch('/me', [UserController::class, 'updateMe']);
        Route::post('/import-csv', [UserController::class, 'importUsersCsv']);
        Route::get('/export-csv', [UserController::class, 'exportUsersCsv']);
        Route::get('/{uuid}',            [UserController::class, 'show']);
        Route::put('/{uuid}',            [UserController::class, 'update']);
        Route::patch('/{uuid}',          [UserController::class, 'update']);
        Route::patch('/{uuid}/password', [UserController::class, 'updatePassword']);
        Route::patch('/{uuid}/image',    [UserController::class, 'updateImage']);
        Route::delete('/{uuid}',         [UserController::class, 'destroy']);
    });

    Route::get('/me/profile', [UserProfileController::class,'show']);
    Route::get('/users/{user_uuid}/profile', [UserProfileController::class,'show']);
    
    // ✅ Other user's profile (protected)
        Route::middleware(['checkRole:admin,director,principal,hod'])->prefix('users')->group(function () {
            Route::post('/{user_uuid}/profile',  [UserProfileController::class,'store']);
            Route::put('/{user_uuid}/profile',   [UserProfileController::class,'update']);
            Route::patch('/{user_uuid}/profile', [UserProfileController::class,'update']);
        });

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
Route::get('/public/departments',              [DepartmentController::class, 'publicIndex']);

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

    Route::get('/announcements/approved', [AnnouncementController::class, 'indexApproved']);
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


/*
|--------------------------------------------------------------------------
| Course Semesters Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    // Global listing + show
    Route::get('/course-semesters',              [CourseSemesterController::class, 'index']);
    Route::get('/course-semesters/{identifier}', [CourseSemesterController::class, 'show']);

    // Course-scoped listing + show
    Route::get('/courses/{course}/semesters',                    [CourseSemesterController::class, 'indexByCourse']);
    Route::get('/courses/{course}/semesters/{identifier}',       [CourseSemesterController::class, 'showByCourse']);

    // Department + Course (optional dept override/filter)
    Route::get('/departments/{department}/courses/{course}/semesters',              [CourseSemesterController::class, 'indexByDepartmentCourse']);
    Route::get('/departments/{department}/courses/{course}/semesters/{identifier}', [CourseSemesterController::class, 'showByDepartmentCourse']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    // Bulk import (CSV)
Route::post('/course-semesters/import', [CourseSemesterController::class, 'importCsv']);

    // Create (global)
    Route::post('/course-semesters', [CourseSemesterController::class, 'store']);

    // Create under a course
    Route::post('/courses/{course}/semesters', [CourseSemesterController::class, 'storeForCourse']);

    // Create under department+course (optional override/filter)
    Route::post('/departments/{department}/courses/{course}/semesters', [CourseSemesterController::class, 'storeForDepartmentCourse']);

    // Trash listing
    Route::get('/course-semesters-trash', [CourseSemesterController::class, 'trash']);

    // Update
    Route::match(['put','patch'], '/course-semesters/{identifier}', [CourseSemesterController::class, 'update']);

    // Soft delete / Restore / Force delete
    Route::delete('/course-semesters/{identifier}',       [CourseSemesterController::class, 'destroy']);
    Route::post('/course-semesters/{identifier}/restore', [CourseSemesterController::class, 'restore']);
    Route::delete('/course-semesters/{identifier}/force', [CourseSemesterController::class, 'forceDelete']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    // Global visible list/show
    Route::get('/course-semesters',              [CourseSemesterController::class, 'publicIndex']);
    Route::get('/course-semesters/{identifier}', [CourseSemesterController::class, 'publicShow']);

    // Course visible list/show
    Route::get('/courses/{course}/semesters',              [CourseSemesterController::class, 'publicIndexByCourse']);
    Route::get('/courses/{course}/semesters/{identifier}', [CourseSemesterController::class, 'publicShowByCourse']);

    // Department + course visible list/show
    Route::get('/departments/{department}/courses/{course}/semesters',              [CourseSemesterController::class, 'publicIndexByDepartmentCourse']);
    Route::get('/departments/{department}/courses/{course}/semesters/{identifier}', [CourseSemesterController::class, 'publicShowByDepartmentCourse']);
});

// ===============================
// Course Semester Sections Routes
// ===============================

// Read-only (authenticated)
Route::middleware('checkRole')->prefix('course-semester-sections')->group(function () {
    Route::get('/',        [CourseSemesterSectionController::class, 'index']);
    Route::get('/trash',   [CourseSemesterSectionController::class, 'trash']);
    Route::get('/current', [CourseSemesterSectionController::class, 'current']);

    Route::get('/{idOrUuid}', [CourseSemesterSectionController::class, 'show']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')
    ->prefix('course-semester-sections')
    ->group(function () {

        Route::post('/', [CourseSemesterSectionController::class, 'store']);

        Route::match(['put','patch'], '/{idOrUuid}', [CourseSemesterSectionController::class, 'update']);

        Route::delete('/{idOrUuid}', [CourseSemesterSectionController::class, 'destroy']);

        Route::post('/{idOrUuid}/restore', [CourseSemesterSectionController::class, 'restore']);

        Route::delete('/{idOrUuid}/force', [CourseSemesterSectionController::class, 'forceDelete']);
    });
    

    // ===============================
    // Subjects Routes
    // ===============================
    
    // Read-only (authenticated)
    Route::middleware('checkRole')
        ->prefix('subjects')
        ->group(function () {
            Route::get('/',        [SubjectController::class, 'index']);
            Route::get('/current', [SubjectController::class, 'current']);
            Route::get('/trash',   [SubjectController::class, 'trash']);
            Route::get('/{idOrUuid}', [SubjectController::class, 'show']);
        });
    
    // Modify (authenticated role-based)
    Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')
        ->prefix('subjects')
        ->group(function () {
            Route::post('/', [SubjectController::class, 'store']);
            Route::match(['put','patch'], '/{idOrUuid}', [SubjectController::class, 'update']);
    
            Route::delete('/{idOrUuid}', [SubjectController::class, 'destroy']);
            Route::post('/{idOrUuid}/restore', [SubjectController::class, 'restore']);
            Route::delete('/{idOrUuid}/force', [SubjectController::class, 'forceDelete']);
        });
    
// ===============================
// Course Semester members Routes (MINIMAL / REQUIRED ONLY)
// ===============================

// Read
Route::middleware('checkRole')
  ->prefix('semester-members')
  ->group(function () {
    Route::get('/', [CourseSemesterMemberController::class, 'index']);
  });

// Bulk push user_ids -> individual rows
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person,student')
  ->prefix('semester-members')
  ->group(function () {
    Route::post('/bulk-import', [CourseSemesterMemberController::class, 'bulkImport']);
    // Scoped by token/role
    Route::get('/scoped', [CourseSemesterMemberController::class, 'scoped']);
  });


/* =========================================================
 | Feedback Routes
 | Table: feedbacks
 | NOTE: store + edit only (no delete)
 |========================================================= */

// ✅ Authenticated read (any logged-in user can read their scoped feedback list/show)
Route::middleware('checkRole')
->prefix('feedbacks')
->group(function () {

    // list (supports filters)
    Route::get('/', [FeedbackController::class, 'index']);

    // show by id|uuid
    Route::get('/{idOrUuid}', [FeedbackController::class, 'show'])
        ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');
});


// ✅ Create + Update (restricted roles)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person,student')
->prefix('feedbacks')
->group(function () {

    // create
    Route::post('/', [FeedbackController::class, 'store']);

    // update (edit)
    Route::match(['put','patch'], '/{idOrUuid}', [FeedbackController::class, 'update'])
        ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');
});

/* =========================================================
| Optional: "My Feedback" convenience routes (viewer-scoped)
| If you implement these in controller
|========================================================= */
// Route::middleware('checkRole')->prefix('feedbacks')->group(function () {
//     Route::get('/me/given',    [FeedbackController::class, 'myGiven']);
//     Route::get('/me/received', [FeedbackController::class, 'myReceived']);
// });


// Read-only (authenticated)
Route::middleware('checkRole')->prefix('feedback-questions')->group(function () {
    Route::get('/',        [FeedbackQuestionController::class, 'index']);
    Route::get('/trash',   [FeedbackQuestionController::class, 'trash']);
    Route::get('/current', [FeedbackQuestionController::class, 'current']);
    Route::get('/group-titles', [FeedbackQuestionController::class, 'groupTitles']);
    Route::get('/{idOrUuid}', [FeedbackQuestionController::class, 'show']);
});

// Modify (role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')
    ->prefix('feedback-questions')
    ->group(function () {
        Route::post('/', [FeedbackQuestionController::class, 'store']);
        Route::match(['put','patch'], '/{idOrUuid}', [FeedbackQuestionController::class, 'update']);
        Route::delete('/{idOrUuid}', [FeedbackQuestionController::class, 'destroy']);
        Route::post('/{idOrUuid}/restore', [FeedbackQuestionController::class, 'restore']);
        Route::delete('/{idOrUuid}/force', [FeedbackQuestionController::class, 'forceDelete']);
    });


/* =========================================================
 | Feedback Posts Routes
 | Table: feedback_posts
 | NOTE: Student can READ only (scoped in controller)
 |========================================================= */

// ✅ Authenticated read (any logged-in user)
Route::middleware('checkRole')
->prefix('feedback-posts')
->group(function () {

    // list (supports filters)
    Route::get('/', [FeedbackPostController::class, 'index']);

    // current active (publish/expire window)
    Route::get('/current', [FeedbackPostController::class, 'current']);

    // trash (controller also blocks students)
    Route::get('/trash', [FeedbackPostController::class, 'trash']);

    // show by id|uuid
    Route::get('/{idOrUuid}', [FeedbackPostController::class, 'show'])
        ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');
});


// ✅ Create + Update + Delete + Restore + Force (restricted roles)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')
->prefix('feedback-posts')
->group(function () {

    // create
    Route::post('/', [FeedbackPostController::class, 'store']);

    // update
    Route::match(['put','patch'], '/{idOrUuid}', [FeedbackPostController::class, 'update'])
        ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');

    // soft delete
    Route::delete('/{idOrUuid}', [FeedbackPostController::class, 'destroy'])
        ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');

    // restore
    Route::post('/{idOrUuid}/restore', [FeedbackPostController::class, 'restore'])
        ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');

    // force delete
    Route::delete('/{idOrUuid}/force', [FeedbackPostController::class, 'forceDelete'])
        ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');
});

/* =========================================================
 | Feedback Submissions (Student submit + view)
 |========================================================= */
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person,student')
    ->prefix('feedback-posts')
    ->group(function () {

        // ✅ list available posts for current user
        // student => only assigned + current window
        // staff/admin => current window (all)
        Route::get('/available', [FeedbackSubmissionController::class, 'available']);

        // ✅ submit once per user per post
        Route::post('/{idOrUuid}/submit', [FeedbackSubmissionController::class, 'submit'])
            ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');
    });

Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person,student')
    ->prefix('feedback-submissions')
    ->group(function () {

        // view submissions
        Route::get('/', [FeedbackSubmissionController::class, 'index']);
        Route::get('/{idOrUuid}', [FeedbackSubmissionController::class, 'show'])
            ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');

        // optional admin delete
        Route::delete('/{idOrUuid}', [FeedbackSubmissionController::class, 'destroy'])
            ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');
    });


    Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')
    ->group(function () {
        Route::get('/feedback-results', [FeedbackResultsController::class, 'results']);
    });




Route::middleware('checkRole')->group(function () {
    Route::get('/my/page-access', [MyAccessController::class, 'pageAccess']);
    Route::get('/my/access-tree', [MyAccessController::class, 'myAccessTree']);
    Route::get('/my/sidebar-menus', [\App\Http\Controllers\API\UserPrivilegeController::class, 'mySidebarMenus']);
    
    });


Route::prefix('contact-info')->group(function () {
    // Admin CRUD (wrap with auth middleware if needed)
    Route::get('/', [ContactInfoController::class, 'index']);
    Route::get('/trash', [ContactInfoController::class, 'trash']);

    Route::get('/{identifier}', [ContactInfoController::class, 'show']);

    Route::post('/', [ContactInfoController::class, 'store']);

    Route::put('/{identifier}', [ContactInfoController::class, 'update']);
    Route::patch('/{identifier}', [ContactInfoController::class, 'update']);

    Route::post('/{identifier}/toggle-featured', [ContactInfoController::class, 'toggleFeatured']);

    Route::delete('/{identifier}', [ContactInfoController::class, 'destroy']);
    Route::post('/{identifier}/restore', [ContactInfoController::class, 'restore']);
    Route::delete('/{identifier}/force', [ContactInfoController::class, 'forceDelete']);
});

// Public (no auth) - website usage
Route::get('/public/contact-info', [ContactInfoController::class, 'publicIndex']);
Route::get('/public/contact-info/{identifier}', [ContactInfoController::class, 'publicShow']);


Route::prefix('hero-carousel')->group(function () {

    /* ===== Admin ===== */
    Route::get('/',               [HeroCarouselController::class, 'index']);       // list (supports filters)
    Route::get('/trash',          [HeroCarouselController::class, 'trash']);       // only deleted
    Route::get('/{id}',           [HeroCarouselController::class, 'show']);        // show by id|uuid|slug
    Route::post('/',              [HeroCarouselController::class, 'store']);       // create
    Route::put('/{id}',           [HeroCarouselController::class, 'update']);      // update
    Route::delete('/{id}',        [HeroCarouselController::class, 'destroy']);     // soft delete
    Route::put('/{id}/restore',   [HeroCarouselController::class, 'restore']);     // restore
    Route::delete('/{id}/force',  [HeroCarouselController::class, 'forceDelete']); // permanent delete

    // optional utility: reorder many items at once
    Route::post('/reorder',       [HeroCarouselController::class, 'reorder']);     // [{id, sort_order},...]

    /* ===== Public ===== */
    Route::get('/public/list',    [HeroCarouselController::class, 'publicIndex']); // visible published list
    Route::get('/public/{id}',    [HeroCarouselController::class, 'publicShow']);  // visible published show
});


Route::prefix('hero-carousel-settings')->group(function () {
    // current settings (frontend-friendly)
    Route::get('/current', [HeroCarouselSettingsController::class, 'current']);

    // admin list + trash
    Route::get('/',        [HeroCarouselSettingsController::class, 'index']);
    Route::get('/trash',   [HeroCarouselSettingsController::class, 'trash']);

    // CRUD
    Route::get('/{idOrUuid}',              [HeroCarouselSettingsController::class, 'show']);
    Route::post('/',                       [HeroCarouselSettingsController::class, 'store']);
    Route::post('/upsert-current',         [HeroCarouselSettingsController::class, 'upsertCurrent']);
    Route::put('/{idOrUuid}',              [HeroCarouselSettingsController::class, 'update']);
    Route::patch('/{idOrUuid}',            [HeroCarouselSettingsController::class, 'update']);
    Route::delete('/{idOrUuid}',           [HeroCarouselSettingsController::class, 'destroy']);

    // restore / force delete
    Route::post('/{idOrUuid}/restore',     [HeroCarouselSettingsController::class, 'restore']);
    Route::delete('/{idOrUuid}/force',     [HeroCarouselSettingsController::class, 'forceDelete']);
});

Route::prefix('recruiters')->group(function () {
    // Admin CRUD
    Route::get('/', [RecruiterController::class, 'index']);
    Route::get('/trash', [RecruiterController::class, 'trash']);

    Route::get('/department/{department}', [RecruiterController::class, 'indexByDepartment']);
    Route::post('/department/{department}', [RecruiterController::class, 'storeForDepartment']);
    Route::get('/department/{department}/{identifier}', [RecruiterController::class, 'showByDepartment']);

    Route::get('/{identifier}', [RecruiterController::class, 'show']);
    Route::post('/', [RecruiterController::class, 'store']);
    Route::match(['put','patch'], '/{identifier}', [RecruiterController::class, 'update']);

    Route::patch('/{identifier}/toggle-featured', [RecruiterController::class, 'toggleFeatured']);

    Route::delete('/{identifier}', [RecruiterController::class, 'destroy']);
    Route::post('/{identifier}/restore', [RecruiterController::class, 'restore']);
    Route::delete('/{identifier}/force', [RecruiterController::class, 'forceDelete']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('/recruiters', [RecruiterController::class, 'publicIndex']);
    Route::get('/recruiters/department/{department}', [RecruiterController::class, 'publicIndexByDepartment']);
    Route::get('/recruiters/{identifier}', [RecruiterController::class, 'publicShow']);
});



Route::prefix('success-stories')->group(function () {
    // Admin CRUD
    Route::get('/',                [SuccessStoryController::class, 'index']);
    Route::get('/trash',           [SuccessStoryController::class, 'trash']);
    Route::get('/{identifier}',    [SuccessStoryController::class, 'show']);
    Route::post('/',               [SuccessStoryController::class, 'store']);
    Route::put('/{identifier}',    [SuccessStoryController::class, 'update']);
    Route::patch('/{identifier}/toggle-featured', [SuccessStoryController::class, 'toggleFeatured']);
    Route::delete('/{identifier}', [SuccessStoryController::class, 'destroy']);
    Route::post('/{identifier}/restore',          [SuccessStoryController::class, 'restore']);
    Route::delete('/{identifier}/force',          [SuccessStoryController::class, 'forceDelete']);
});

// Department-scoped (admin)
Route::prefix('departments/{department}')->group(function () {
    Route::get('/success-stories',                [SuccessStoryController::class, 'indexByDepartment']);
    Route::post('/success-stories',               [SuccessStoryController::class, 'storeForDepartment']);
    Route::get('/success-stories/{identifier}',   [SuccessStoryController::class, 'showByDepartment']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('/success-stories',                      [SuccessStoryController::class, 'publicIndex']);
    Route::get('/departments/{department}/success-stories', [SuccessStoryController::class, 'publicIndexByDepartment']);
    Route::get('/success-stories/{identifier}',         [SuccessStoryController::class, 'publicShow']);
});


/*
|--------------------------------------------------------------------------
| Events (Admin / Auth side)
|--------------------------------------------------------------------------
| Wrap this group with your auth middleware if needed.
| Example:
| Route::middleware(['auth:sanctum'])->group(function(){ ... });
*/
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/trash', [EventController::class, 'trash']);

    Route::get('/department/{department}', [EventController::class, 'indexByDepartment']);
    Route::get('/department/{department}/{identifier}', [EventController::class, 'showByDepartment']);
    Route::post('/department/{department}', [EventController::class, 'storeForDepartment']);

    Route::get('/{identifier}', [EventController::class, 'show']);
    Route::post('/', [EventController::class, 'store']);
    Route::put('/{identifier}', [EventController::class, 'update']);

    Route::patch('/{identifier}/toggle-featured', [EventController::class, 'toggleFeatured']);

    Route::delete('/{identifier}', [EventController::class, 'destroy']);
    Route::patch('/{identifier}/restore', [EventController::class, 'restore']);
    Route::delete('/{identifier}/force', [EventController::class, 'forceDelete']);
});

/*
|--------------------------------------------------------------------------
| Events (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('public/events')->group(function () {
    Route::get('/', [EventController::class, 'publicIndex']);
    Route::get('/department/{department}', [EventController::class, 'publicIndexByDepartment']);
    Route::get('/{identifier}', [EventController::class, 'publicShow']);
});



Route::prefix('placed-students')->group(function () {
    Route::get('/', [PlacedStudentController::class, 'index']);
    Route::get('/trash', [PlacedStudentController::class, 'trash']);

    Route::get('/department/{department}', [PlacedStudentController::class, 'indexByDepartment']);
    Route::post('/department/{department}', [PlacedStudentController::class, 'storeForDepartment']);
    Route::get('/department/{department}/{identifier}', [PlacedStudentController::class, 'showByDepartment']);

    Route::get('/{identifier}', [PlacedStudentController::class, 'show']);
    Route::post('/', [PlacedStudentController::class, 'store']);

    Route::put('/{identifier}', [PlacedStudentController::class, 'update']); // ✅ PUT (not PATCH)
    Route::put('/{identifier}/toggle-featured', [PlacedStudentController::class, 'toggleFeatured']);

    Route::delete('/{identifier}', [PlacedStudentController::class, 'destroy']);
    Route::put('/{identifier}/restore', [PlacedStudentController::class, 'restore']);
    Route::delete('/{identifier}/force', [PlacedStudentController::class, 'forceDelete']);
});



Route::prefix('alumni')->group(function () {

    // Admin-style listing (supports filters, pagination, trash, etc.)
    Route::get('/', [AlumniController::class, 'index']);
    Route::get('/trash', [AlumniController::class, 'trash']);

    // Department scoped
    Route::get('/department/{department}', [AlumniController::class, 'indexByDepartment']);
    Route::post('/department/{department}', [AlumniController::class, 'storeForDepartment']);
    Route::get('/department/{department}/{identifier}', [AlumniController::class, 'showByDepartment']);

    // CRUD
    Route::get('/{identifier}', [AlumniController::class, 'show']);
    Route::post('/', [AlumniController::class, 'store']);

    Route::put('/{identifier}', [AlumniController::class, 'update']); // ✅ PUT (not PATCH)
    Route::put('/{identifier}/toggle-featured', [AlumniController::class, 'toggleFeatured']);

    Route::delete('/{identifier}', [AlumniController::class, 'destroy']);
    Route::put('/{identifier}/restore', [AlumniController::class, 'restore']);
    Route::delete('/{identifier}/force', [AlumniController::class, 'forceDelete']);

    // Public listing endpoint
    Route::get('/public/index', [AlumniController::class, 'publicIndex']);
});


Route::prefix('program-toppers')->group(function () {

    // Admin-style listing (supports filters, pagination, trash, etc.)
    Route::get('/', [ProgramTopperController::class, 'index']);
    Route::get('/trash', [ProgramTopperController::class, 'trash']);

    // Department scoped
    Route::get('/department/{department}', [ProgramTopperController::class, 'indexByDepartment']);
    Route::post('/department/{department}', [ProgramTopperController::class, 'storeForDepartment']);
    Route::get('/department/{department}/{identifier}', [ProgramTopperController::class, 'showByDepartment']);

    // CRUD
    Route::get('/{identifier}', [ProgramTopperController::class, 'show']);
    Route::post('/', [ProgramTopperController::class, 'store']);

    Route::put('/{identifier}', [ProgramTopperController::class, 'update']); // ✅ PUT (not PATCH)
    Route::put('/{identifier}/toggle-featured', [ProgramTopperController::class, 'toggleFeatured']);

    Route::delete('/{identifier}', [ProgramTopperController::class, 'destroy']);
    Route::put('/{identifier}/restore', [ProgramTopperController::class, 'restore']);
    Route::delete('/{identifier}/force', [ProgramTopperController::class, 'forceDelete']);

    // Public listing endpoint
    Route::get('/public/index', [ProgramTopperController::class, 'publicIndex']);
});


/*
|--------------------------------------------------------------------------
| Placement Notices (Admin)
| Tip: Wrap these in your auth middleware group if needed
|--------------------------------------------------------------------------
*/
Route::get('placement-notices', [PlacementNoticeController::class, 'index']);
Route::get('placement-notices/trash', [PlacementNoticeController::class, 'trash']);
Route::get('placement-notices/department/{department}', [PlacementNoticeController::class, 'indexByDepartment']);

Route::get('placement-notices/{identifier}', [PlacementNoticeController::class, 'show']);
Route::get('placement-notices/department/{department}/{identifier}', [PlacementNoticeController::class, 'showByDepartment']);

Route::post('placement-notices', [PlacementNoticeController::class, 'store']);
Route::post('placement-notices/department/{department}', [PlacementNoticeController::class, 'storeForDepartment']);

Route::put('placement-notices/{identifier}', [PlacementNoticeController::class, 'update']);

Route::patch('placement-notices/{identifier}/toggle-featured', [PlacementNoticeController::class, 'toggleFeatured']);

Route::delete('placement-notices/{identifier}', [PlacementNoticeController::class, 'destroy']);
Route::patch('placement-notices/{identifier}/restore', [PlacementNoticeController::class, 'restore']);
Route::delete('placement-notices/{identifier}/force', [PlacementNoticeController::class, 'forceDelete']);

/*
|--------------------------------------------------------------------------
| Placement Notices (Public)
|--------------------------------------------------------------------------
*/
Route::get('public/placement-notices', [PlacementNoticeController::class, 'publicIndex']);
Route::get('public/placement-notices/department/{department}', [PlacementNoticeController::class, 'publicIndexByDepartment']);
Route::get('public/placement-notices/{identifier}', [PlacementNoticeController::class, 'publicShow']);


/*
|--------------------------------------------------------------------------
| Successful Entrepreneurs (Admin)
|--------------------------------------------------------------------------
| Put inside your auth middleware group if needed.
*/

// Route::middleware(['auth:sanctum'])->group(function () {

Route::prefix('successful-entrepreneurs')->group(function () {
    Route::get('/',                [SuccessfulEntrepreneurController::class, 'index']);
    Route::get('/trash',           [SuccessfulEntrepreneurController::class, 'trash']);

    Route::get('/{identifier}',    [SuccessfulEntrepreneurController::class, 'show']);

    Route::post('/',               [SuccessfulEntrepreneurController::class, 'store']);

    // ✅ Use PUT (not PATCH) to avoid 405 issue
    Route::put('/{identifier}',    [SuccessfulEntrepreneurController::class, 'update']);

    Route::post('/{identifier}/toggle-featured', [SuccessfulEntrepreneurController::class, 'toggleFeatured']);

    Route::delete('/{identifier}',           [SuccessfulEntrepreneurController::class, 'destroy']);
    Route::post('/{identifier}/restore',     [SuccessfulEntrepreneurController::class, 'restore']);
    Route::delete('/{identifier}/force',     [SuccessfulEntrepreneurController::class, 'forceDelete']);
});

// Department scoped
Route::prefix('departments/{department}')->group(function () {
    Route::get('/successful-entrepreneurs',                [SuccessfulEntrepreneurController::class, 'indexByDepartment']);
    Route::post('/successful-entrepreneurs',               [SuccessfulEntrepreneurController::class, 'storeForDepartment']);
    Route::get('/successful-entrepreneurs/{identifier}',   [SuccessfulEntrepreneurController::class, 'showByDepartment']);
});

// });

/*
|--------------------------------------------------------------------------
| Successful Entrepreneurs (Public - No Auth)
|--------------------------------------------------------------------------
*/
Route::prefix('public')->group(function () {
    Route::get('/successful-entrepreneurs',                       [SuccessfulEntrepreneurController::class, 'publicIndex']);
    Route::get('/departments/{department}/successful-entrepreneurs',[SuccessfulEntrepreneurController::class, 'publicIndexByDepartment']);
    Route::get('/successful-entrepreneurs/{identifier}',          [SuccessfulEntrepreneurController::class, 'publicShow']);
});



Route::prefix('header-components')->group(function () {

    Route::get('/recruiter-options', [HeaderComponentController::class, 'recruiterOptions']);

    Route::get('/',              [HeaderComponentController::class, 'index']);
    Route::get('/trash',         [HeaderComponentController::class, 'trash']);
    Route::get('/{identifier}',  [HeaderComponentController::class, 'show']);

    Route::post('/',             [HeaderComponentController::class, 'store']);
    Route::put('/{identifier}',  [HeaderComponentController::class, 'update']);

    Route::delete('/{identifier}',          [HeaderComponentController::class, 'destroy']);
    Route::put('/{identifier}/restore',     [HeaderComponentController::class, 'restore']);
    Route::delete('/{identifier}/force',    [HeaderComponentController::class, 'forceDelete']);
});


/*
|--------------------------------------------------------------------------
| Career Notices Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/career-notices',              [CareerNoticeController::class, 'index']);
    Route::get('/career-notices/{identifier}', [CareerNoticeController::class, 'show']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/career-notices', [CareerNoticeController::class, 'store']);

    Route::get('/career-notices-trash', [CareerNoticeController::class, 'trash']);

    Route::match(['put','patch'], '/career-notices/{identifier}', [CareerNoticeController::class, 'update']);
    Route::patch('/career-notices/{identifier}/toggle-featured',  [CareerNoticeController::class, 'toggleFeatured']);

    Route::delete('/career-notices/{identifier}',       [CareerNoticeController::class, 'destroy']);
    Route::post('/career-notices/{identifier}/restore', [CareerNoticeController::class, 'restore']);
    Route::delete('/career-notices/{identifier}/force', [CareerNoticeController::class, 'forceDelete']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('/career-notices',              [CareerNoticeController::class, 'publicIndex']);
    Route::get('/career-notices/{identifier}', [CareerNoticeController::class, 'publicShow']);
});


/*
|--------------------------------------------------------------------------
| Why Us Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/why-us',              [WhyUsController::class, 'index']);
    Route::get('/why-us/{identifier}', [WhyUsController::class, 'show']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/why-us', [WhyUsController::class, 'store']);

    Route::get('/why-us-trash', [WhyUsController::class, 'trash']);

    Route::match(['put','patch'], '/why-us/{identifier}', [WhyUsController::class, 'update']);
    Route::patch('/why-us/{identifier}/toggle-featured',  [WhyUsController::class, 'toggleFeatured']);

    Route::delete('/why-us/{identifier}',       [WhyUsController::class, 'destroy']);
    Route::post('/why-us/{identifier}/restore', [WhyUsController::class, 'restore']);
    Route::delete('/why-us/{identifier}/force', [WhyUsController::class, 'forceDelete']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('/why-us',              [WhyUsController::class, 'publicIndex']);
    Route::get('/why-us/{identifier}', [WhyUsController::class, 'publicShow']);
});


/*
|--------------------------------------------------------------------------
| Scholarships Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/scholarships',              [ScholarshipController::class, 'index']);
    Route::get('/scholarships/{identifier}', [ScholarshipController::class, 'show']);

    Route::get('/departments/{department}/scholarships',              [ScholarshipController::class, 'indexByDepartment']);
    Route::get('/departments/{department}/scholarships/{identifier}', [ScholarshipController::class, 'showByDepartment']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/scholarships', [ScholarshipController::class, 'store']);
    Route::post('/departments/{department}/scholarships', [ScholarshipController::class, 'storeForDepartment']);

    Route::get('/scholarships-trash', [ScholarshipController::class, 'trash']);

    Route::match(['put','patch'], '/scholarships/{identifier}', [ScholarshipController::class, 'update']);
    Route::patch('/scholarships/{identifier}/toggle-featured',  [ScholarshipController::class, 'toggleFeatured']);

    Route::delete('/scholarships/{identifier}',       [ScholarshipController::class, 'destroy']);
    Route::post('/scholarships/{identifier}/restore', [ScholarshipController::class, 'restore']);
    Route::delete('/scholarships/{identifier}/force', [ScholarshipController::class, 'forceDelete']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('/scholarships',              [ScholarshipController::class, 'publicIndex']);
    Route::get('/scholarships/{identifier}', [ScholarshipController::class, 'publicShow']);

    Route::get('/departments/{department}/scholarships', [ScholarshipController::class, 'publicIndexByDepartment']);
});


/*
|--------------------------------------------------------------------------
| Alumni Speak Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/alumni-speaks',              [AlumniSpeakController::class, 'index']);
    Route::get('/alumni-speaks/{identifier}', [AlumniSpeakController::class, 'show']);

    Route::get('/departments/{department}/alumni-speaks',              [AlumniSpeakController::class, 'indexByDepartment']);
    Route::get('/departments/{department}/alumni-speaks/{identifier}', [AlumniSpeakController::class, 'showByDepartment']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/alumni-speaks', [AlumniSpeakController::class, 'store']);
    Route::post('/departments/{department}/alumni-speaks', [AlumniSpeakController::class, 'storeForDepartment']);

    Route::get('/alumni-speaks-trash', [AlumniSpeakController::class, 'trash']);

    Route::match(['put','patch'], '/alumni-speaks/{identifier}', [AlumniSpeakController::class, 'update']);

    Route::delete('/alumni-speaks/{identifier}',       [AlumniSpeakController::class, 'destroy']);
    Route::post('/alumni-speaks/{identifier}/restore', [AlumniSpeakController::class, 'restore']);
    Route::delete('/alumni-speaks/{identifier}/force', [AlumniSpeakController::class, 'forceDelete']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('/alumni-speaks', [AlumniSpeakController::class, 'publicIndex']);
    Route::get('/alumni-speaks/{identifier}', [AlumniSpeakController::class, 'publicShow']);
    Route::get('/departments/{department}/alumni-speaks', [AlumniSpeakController::class, 'publicIndexByDepartment']);
});


/*
|--------------------------------------------------------------------------
| Center Iframes Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/center-iframes',              [CenterIframeController::class, 'index']);
    Route::get('/center-iframes/{identifier}', [CenterIframeController::class, 'show']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/center-iframes', [CenterIframeController::class, 'store']);

    Route::get('/center-iframes-trash', [CenterIframeController::class, 'trash']);

    Route::match(['put','patch'], '/center-iframes/{identifier}', [CenterIframeController::class, 'update']);

    Route::delete('/center-iframes/{identifier}',       [CenterIframeController::class, 'destroy']);
    Route::post('/center-iframes/{identifier}/restore', [CenterIframeController::class, 'restore']);
    Route::delete('/center-iframes/{identifier}/force', [CenterIframeController::class, 'forceDelete']);
});

// Public (no auth)
Route::get('/public/center-iframes',              [CenterIframeController::class, 'publicIndex']);
Route::get('/public/center-iframes/{identifier}', [CenterIframeController::class, 'publicShow']);


/*
|--------------------------------------------------------------------------
| Stats Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated)
Route::middleware('checkRole')->group(function () {
    Route::get('/stats',              [StatsController::class, 'index']);
    Route::get('/stats/current',      [StatsController::class, 'current']);   // ✅ keep before {identifier}
    Route::get('/stats/{identifier}', [StatsController::class, 'show']);
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')->group(function () {
    Route::post('/stats',                    [StatsController::class, 'store']);
    Route::post('/stats/upsert-current',     [StatsController::class, 'upsertCurrent']);

    Route::get('/stats-trash',               [StatsController::class, 'trash']);

    Route::match(['put','patch'], '/stats/{identifier}', [StatsController::class, 'update']);

    Route::delete('/stats/{identifier}',     [StatsController::class, 'destroy']);
    Route::post('/stats/{identifier}/restore',[StatsController::class, 'restore']);
    Route::delete('/stats/{identifier}/force',[StatsController::class, 'forceDelete']);
});

// Public (no auth)
Route::prefix('public')->group(function () {
    Route::get('/stats',              [StatsController::class, 'publicIndex']);
    Route::get('/stats/current',      [StatsController::class, 'publicCurrent']); // ✅ keep before {identifier}
    Route::get('/stats/{identifier}', [StatsController::class, 'publicShow']);
});


/*
|--------------------------------------------------------------------------
| Notice Marquee
|--------------------------------------------------------------------------
*/

Route::prefix('notice-marquee')->group(function () {

    // current visible (frontend-friendly)
    Route::get('/current', [NoticeMarqueeController::class, 'current']);

    // admin list + trash
    Route::get('/',      [NoticeMarqueeController::class, 'index']);
    Route::get('/trash', [NoticeMarqueeController::class, 'trash']);

    // CRUD (id|uuid|slug)
    Route::get('/{identifier}',    [NoticeMarqueeController::class, 'show']);
    Route::post('/',              [NoticeMarqueeController::class, 'store']);
    Route::match(['put','patch'], '/{identifier}', [NoticeMarqueeController::class, 'update']);
    Route::delete('/{identifier}', [NoticeMarqueeController::class, 'destroy']);

    // restore / force delete
    Route::post('/{identifier}/restore', [NoticeMarqueeController::class, 'restore']);
    Route::delete('/{identifier}/force', [NoticeMarqueeController::class, 'forceDelete']);

    // optional analytics
    Route::post('/{identifier}/views', [NoticeMarqueeController::class, 'incrementViews']);
});


// Public (no auth) - website usage
Route::prefix('public')->group(function () {
    Route::get('/notice-marquee/current', [NoticeMarqueeController::class, 'current']);
});


Route::prefix('footer-components')->group(function () {

    // helpers for UI selections
    Route::get('/header-menu-options',       [FooterComponentController::class, 'headerMenuOptions']);
    Route::get('/header-component-options',  [FooterComponentController::class, 'headerComponentOptions']);

    // CRUD
    Route::get('/',             [FooterComponentController::class, 'index']);
    Route::get('/trash',        [FooterComponentController::class, 'trash']);
    Route::get('/{identifier}', [FooterComponentController::class, 'show']);

    Route::post('/',            [FooterComponentController::class, 'store']);
    Route::put('/{identifier}', [FooterComponentController::class, 'update']);

    Route::delete('/{identifier}',         [FooterComponentController::class, 'destroy']);
    Route::put('/{identifier}/restore',    [FooterComponentController::class, 'restore']);
    Route::delete('/{identifier}/force',   [FooterComponentController::class, 'forceDelete']);
});




Route::prefix('public/grand-homepage')->group(function () {
    // ✅ Bootstrap (fast)
    Route::get('/', [GrandHomepageController::class, 'index']);

    // ✅ Section wise endpoints (lazy-load on scroll)
    Route::get('/notice-marquee', [GrandHomepageController::class, 'noticeMarquee']);
    Route::get('/hero-carousel',  [GrandHomepageController::class, 'heroCarousel']);

    Route::get('/quick-links',    [GrandHomepageController::class, 'quickLinks']);      // career_notices, why_us, scholarships
    Route::get('/notice-board',   [GrandHomepageController::class, 'noticeBoard']);     // notices, center_iframe, announcements
    Route::get('/activities',     [GrandHomepageController::class, 'activities']);      // achievements, student_activities
    Route::get('/placement-notices', [GrandHomepageController::class, 'placementNotices']);

    Route::get('/courses',        [GrandHomepageController::class, 'courses']);
    Route::get('/stats',          [GrandHomepageController::class, 'stats']);

    Route::get('/successful-entrepreneurs', [GrandHomepageController::class, 'successfulEntrepreneurs']);
    Route::get('/alumni-speak',             [GrandHomepageController::class, 'alumniSpeak']);
    Route::get('/success-stories',          [GrandHomepageController::class, 'successStories']);
    Route::get('/recruiters',               [GrandHomepageController::class, 'recruiters']);

    // Optional explicit full endpoint (same as ?full=1)
    Route::get('/full', [GrandHomepageController::class, 'full']);
});


//Contact Us
/* Public */
Route::post('/contact-us', [ContactUsController::class, 'store']);
 
/* Admin */
Route::get('/contact-us', [ContactUsController::class, 'index']);
Route::get('/contact-us/{id}', [ContactUsController::class, 'show']);
Route::delete('/contact-us/{id}', [ContactUsController::class, 'destroy']);
Route::get('/contact-us/export/csv', [ContactUsController::class, 'exportCsv']);
Route::patch('/contact-us/{id}/read', [ContactUsController::class, 'markAsRead']);
 
 
Route::get('/public/contact-us/visibility', [ContactUsPageVisibilityController::class, 'publicShow']);
 
// Put these behind your admin/auth middleware group
Route::get('/contact-us/visibility', [ContactUsPageVisibilityController::class, 'Show']);
Route::put('/contact-us/visibility', [ContactUsPageVisibilityController::class, 'Update']);

 Route::get('/public/faculty',                  [UserController::class, 'facultyindex']);
Route::get('/public/placement-officers', [UserController::class, 'placementOfficerIndex']);


/*
|--------------------------------------------------------------------------
| Top Header Menu Routes
|--------------------------------------------------------------------------
*/
Route::prefix('/top-header-menus')
  ->middleware('checkRole:admin,super_admin,director')
  ->group(function () {

    // List + Trash + Resolve
    Route::get('/',        [TopHeaderMenuController::class, 'index']);
    Route::get('/trash',   [TopHeaderMenuController::class, 'indexTrash']);
    Route::get('/resolve', [TopHeaderMenuController::class, 'resolve']);

    // Contact infos list (for picking from)
    Route::get('/contact-infos', [TopHeaderMenuController::class, 'contactInfos']);

    // ✅ Contact selection (GLOBAL) - MUST be BEFORE "{id}" routes
    Route::get('/contact-info',    [TopHeaderMenuController::class, 'getContactSelection']);
    Route::put('/contact-info',    [TopHeaderMenuController::class, 'putContactSelection']);
    Route::delete('/contact-info', [TopHeaderMenuController::class, 'deleteContactSelection']);

    // CRUD
    Route::post('/',      [TopHeaderMenuController::class, 'store']);

    // ✅ Strongly recommended: constrain {id} so it never eats "contact-info"
    Route::get('{id}',    [TopHeaderMenuController::class, 'show'])->whereNumber('id');
    Route::put('{id}',    [TopHeaderMenuController::class, 'update'])->whereNumber('id');
    Route::delete('{id}', [TopHeaderMenuController::class, 'destroy'])->whereNumber('id');

    // Bin actions
    Route::post('{id}/restore', [TopHeaderMenuController::class, 'restore'])->whereNumber('id');
    Route::delete('{id}/force', [TopHeaderMenuController::class, 'forceDelete'])->whereNumber('id');
    Route::post('{id}/toggle-active', [TopHeaderMenuController::class, 'toggleActive'])->whereNumber('id');

    Route::post('/reorder', [TopHeaderMenuController::class, 'reorder']);
});
Route::prefix('/public/top-header-menus')
  ->group(function () {

    // List + Trash + Resolve
    Route::get('/',        [TopHeaderMenuController::class, 'index']);
    Route::get('/trash',   [TopHeaderMenuController::class, 'indexTrash']);
    Route::get('/resolve', [TopHeaderMenuController::class, 'resolve']);

    // Contact infos list (for picking from)
    Route::get('/contact-infos', [TopHeaderMenuController::class, 'contactInfos']);

    // ✅ Contact selection (GLOBAL) - MUST be BEFORE "{id}" routes
    Route::get('/contact-info',    [TopHeaderMenuController::class, 'getContactSelection']);
    Route::put('/contact-info',    [TopHeaderMenuController::class, 'putContactSelection']);
    Route::delete('/contact-info', [TopHeaderMenuController::class, 'deleteContactSelection']);

    // CRUD
    Route::post('/',      [TopHeaderMenuController::class, 'store']);

    // ✅ Strongly recommended: constrain {id} so it never eats "contact-info"
    Route::get('{id}',    [TopHeaderMenuController::class, 'show'])->whereNumber('id');
    Route::put('{id}',    [TopHeaderMenuController::class, 'update'])->whereNumber('id');
    Route::delete('{id}', [TopHeaderMenuController::class, 'destroy'])->whereNumber('id');

    // Bin actions
    Route::post('{id}/restore', [TopHeaderMenuController::class, 'restore'])->whereNumber('id');
    Route::delete('{id}/force', [TopHeaderMenuController::class, 'forceDelete'])->whereNumber('id');
    Route::post('{id}/toggle-active', [TopHeaderMenuController::class, 'toggleActive'])->whereNumber('id');

    Route::post('/reorder', [TopHeaderMenuController::class, 'reorder']);
});


 
/*
|--------------------------------------------------------------------------
| Student Academic Details Routes
|--------------------------------------------------------------------------
| Admin/Staff protected (adjust roles as you want)
*/
 
Route::prefix('student-academic-details')
    ->middleware('checkRole:admin,super_admin,director,principal,hod,faculty,technical_assistant,it_person')
    ->group(function () {

        Route::get('by-academics', [StudentAcademicDetailsController::class, 'studentsByAcademics']);
 
        // CRUD
        Route::get('/',        [StudentAcademicDetailsController::class, 'index']);
        Route::post('/',       [StudentAcademicDetailsController::class, 'store']);
        Route::get('{id}',     [StudentAcademicDetailsController::class, 'show']);
        Route::put('{id}',     [StudentAcademicDetailsController::class, 'update']);
        Route::delete('{id}',  [StudentAcademicDetailsController::class, 'destroy']);
 
        // Soft-delete restore
        Route::post('{id}/restore', [StudentAcademicDetailsController::class, 'restore']);
    });
 
 
/*
|--------------------------------------------------------------------------
| Faculty Preview Order (Reorder Faculties by Department)
|--------------------------------------------------------------------------
| - Select dept -> load users excluding admin/director/student
| - Tab1 Assigned: users present in faculty_ids_json
| - Tab2 Unassigned: eligible users not in assigned list
| - Save reorder: POST { faculty_ids: [...] }
*/

Route::middleware(['checkRole:admin,director,principal,hod'])
    ->prefix('faculty-preview-order')
    ->group(function () {

        // list all dept order rows
        Route::get('/', [FacultyPreviewOrderController::class, 'index']);

        // get assigned + unassigned for a department (id|uuid|slug)
        Route::get('/{department}', [FacultyPreviewOrderController::class, 'show']);

        // save / upsert order for a department
        Route::post('/{department}/save', [FacultyPreviewOrderController::class, 'save']);

        // optional: toggle active (1/0)
        Route::post('/{department}/toggle-active', [FacultyPreviewOrderController::class, 'toggleActive']);

        // optional: remove order record
        Route::delete('/{department}', [FacultyPreviewOrderController::class, 'destroy']);
});

Route::prefix('public')->group(function () {
    Route::get('/faculty-preview-order', [FacultyPreviewOrderController::class, 'publicIndex']);
    Route::get('/faculty-preview-order/{department}', [FacultyPreviewOrderController::class, 'publicShow']);
});


// Technical Assistant Preview Order
Route::middleware(['checkRole:admin,director,principal,hod'])
    ->prefix('technical-assistant-preview-order')
    ->group(function () {
Route::get('/', [TechnicalAssistantPreviewOrderController::class, 'index']);
Route::get('/{department}', [TechnicalAssistantPreviewOrderController::class, 'show']);
Route::post('/{department}/save', [TechnicalAssistantPreviewOrderController::class, 'save']);
Route::post('/{department}/toggle-active', [TechnicalAssistantPreviewOrderController::class, 'toggleActive']);
Route::delete('/{department}', [TechnicalAssistantPreviewOrderController::class, 'destroy']);
});

Route::get('public/technical-assistant-preview-order', [TechnicalAssistantPreviewOrderController::class, 'publicIndex']);
Route::get('public/technical-assistant-preview-order/{department}', [TechnicalAssistantPreviewOrderController::class, 'publicShow']);

// Placement Officer Preview Order
Route::middleware(['checkRole:admin,director,principal,hod'])
    ->prefix('placement-officer-preview-order')
    ->group(function () {
Route::get('/', [PlacementOfficerPreviewOrderController::class, 'index']);
Route::get('/{department}', [PlacementOfficerPreviewOrderController::class, 'show']);
Route::post('/{department}/save', [PlacementOfficerPreviewOrderController::class, 'save']);
Route::post('/{department}/toggle-active', [PlacementOfficerPreviewOrderController::class, 'toggleActive']);
Route::delete('/{department}', [PlacementOfficerPreviewOrderController::class, 'destroy']);
});

// Public
Route::get('/public/placement-officer-preview-order', [PlacementOfficerPreviewOrderController::class, 'publicIndex']);
Route::get('/public/placement-officer-preview-order/{department}', [PlacementOfficerPreviewOrderController::class, 'publicShow']);

/*
|--------------------------------------------------------------------------
| Sticky Buttons (Admin + Public)
|--------------------------------------------------------------------------
| Table: sticky_buttons
| - buttons_json: JSON array storing selected contact_info snapshot
| - Admin can CRUD + upsert-current
| - Public can read active (current + list + show)
*/

Route::middleware(['checkRole:admin,director,principal,hod'])
    ->prefix('sticky-buttons')
    ->group(function () {

        // static first
        Route::get('/current', [StickyButtonController::class, 'current']);
        Route::get('/trash',   [StickyButtonController::class, 'trash']);
        Route::post('/upsert-current', [StickyButtonController::class, 'upsertCurrent']);

        // list + CRUD
        Route::get('/',  [StickyButtonController::class, 'index']);

        Route::get('/{identifier}', [StickyButtonController::class, 'show'])
            ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');

        Route::post('/', [StickyButtonController::class, 'store']);

        Route::match(['put','patch'], '/{identifier}', [StickyButtonController::class, 'update'])
            ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');

        Route::post('/{identifier}/toggle-status', [StickyButtonController::class, 'toggleStatus'])
            ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');

        Route::delete('/{identifier}', [StickyButtonController::class, 'destroy'])
            ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');

        Route::post('/{identifier}/restore', [StickyButtonController::class, 'restore'])
            ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');

        Route::delete('/{identifier}/force', [StickyButtonController::class, 'forceDelete'])
            ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');
    });

/* ===== Public (no auth) ===== */
Route::prefix('public')->group(function () {
    Route::get('/sticky-buttons',          [StickyButtonController::class, 'publicIndex']);
    Route::get('/sticky-buttons/current',  [StickyButtonController::class, 'publicCurrent']);
    Route::get('/sticky-buttons/{identifier}', [StickyButtonController::class, 'publicShow'])
        ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');
});



/*
|--------------------------------------------------------------------------
| Master Approval Routes (Authority Control)
|--------------------------------------------------------------------------
*/


Route::middleware('checkRole:admin,director,principal,hod,technical_assistant,it_person')->group(function () {

    // ✅ Overview (supports: ?status=pending|approved|all)
    Route::get('/master-approval', [MasterApprovalController::class, 'overview']);

    // ✅ FINAL combined data API (all modules data with is_approved)
    Route::get('/master-approval/final', [MasterApprovalController::class, 'final']);

    // ✅ Approve / Reject (updates the correct module table by UUID)
    Route::post('/master-approval/{uuid}/approve', [MasterApprovalController::class, 'approve']);
    Route::post('/master-approval/{uuid}/reject', [MasterApprovalController::class, 'reject']);
});



Route::get('/announcements/approved', [AnnouncementController::class, 'indexApproved']);


/* =========================================================
 | Student Subjects Routes
 | Table: student_subject
 | Columns: uuid, department_id, course_id, semester_id (nullable),
 |          subject_json (required), status, metadata, softDeletes
 |========================================================= */

// ✅ Read-only (authenticated)
Route::middleware('checkRole')
    ->prefix('student-subjects')
    ->group(function () {
        Route::get('/',        [StudentSubjectController::class, 'index']);
        Route::get('/current', [StudentSubjectController::class, 'current']);
        Route::get('/trash',   [StudentSubjectController::class, 'trash']);

        Route::get('/{idOrUuid}', [StudentSubjectController::class, 'show'])
            ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');
    });

// ✅ Modify (authenticated role-based)
Route::middleware('checkRole:admin,director,principal,hod,faculty,technical_assistant,it_person')
    ->prefix('student-subjects')
    ->group(function () {
        Route::post('/', [StudentSubjectController::class, 'store']);

        Route::match(['put','patch'], '/{idOrUuid}', [StudentSubjectController::class, 'update'])
            ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');

        // soft delete
        Route::delete('/{idOrUuid}', [StudentSubjectController::class, 'destroy'])
            ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');

        // restore
        Route::post('/{idOrUuid}/restore', [StudentSubjectController::class, 'restore'])
            ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');

        // force delete
        Route::delete('/{idOrUuid}/force', [StudentSubjectController::class, 'forceDelete'])
            ->where('idOrUuid', '[0-9]+|[0-9a-fA-F\-]{36}');
    });


// Activity Logs
Route::middleware('checkRole')->get('/activity-logs', [UserActivityLogsController::class, 'index']);
 
 
/*
|--------------------------------------------------------------------------
| Meta Tags Routes
|--------------------------------------------------------------------------
*/

// Read-only (authenticated) - optional, keep if you want admin screens to load via auth
Route::middleware('checkRole')->group(function () {
    Route::get('/meta-tags',                 [MetaTagController::class, 'index']);
    Route::get('/meta-tags/trash',           [MetaTagController::class, 'trash']);
    Route::get('/meta-tags/resolve',         [MetaTagController::class, 'resolve']);

    Route::get('/meta-tags/{identifier}',    [MetaTagController::class, 'show'])
        ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');
});

// Modify (authenticated role-based)
Route::middleware('checkRole:admin,super_admin,director')->group(function () {
    Route::post('/meta-tags', [MetaTagController::class, 'store']);

    Route::post('/meta-tags/bulk', [MetaTagController::class, 'bulk']);

    Route::match(['put','patch'], '/meta-tags/{identifier}', [MetaTagController::class, 'update'])
        ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');

    Route::delete('/meta-tags/{identifier}', [MetaTagController::class, 'destroy'])
        ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');

    Route::post('/meta-tags/{identifier}/restore', [MetaTagController::class, 'restore'])
        ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');

    Route::delete('/meta-tags/{identifier}/force', [MetaTagController::class, 'forceDelete'])
        ->where('identifier', '[0-9]+|[0-9a-fA-F\-]{36}');
});

// Public (no auth) - website usage
Route::prefix('public')->group(function () {
    // recommended: pass ?page_link=/about or full URL, etc.
    Route::get('/meta-tags', [MetaTagController::class, 'publicIndex']);
});
