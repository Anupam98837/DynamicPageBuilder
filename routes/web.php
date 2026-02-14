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
Route::get('/user/profile/edit/{uuid?}', function () {
    return view('modules.users.editUserProfile');
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
Route::get('/user/basic-information/manage', function () {
    return view('pages.users.pages.users.profile.basicInformation');
});


// Department Manage Pages

Route::get('/department/manage', function () {
    return view('pages.users.pages.departments.manageDepartment');
});

Route::get('/department/curriculum-syllabus', function () {
    return view('pages.users.pages.curriculumSyllabuses.manageCurriculumSyllabuses');
});

Route::get('/department/announcements', function () {
    return view('pages.users.pages.announcements.manageAnnouncements');
});

Route::get('/department/achievements', function () {
    return view('pages.users.pages.achievements.manageAchievements');
});

Route::get('/department/notices', function () {
    return view('pages.users.pages.notices.manageNotices');
});

Route::get('/department/student-activities', function () {
    return view('pages.users.pages.studentActivities.manageStudentActivities');
});

Route::get('/department/gallery', function () {
    return view('pages.users.pages.gallery.manageGallery');
});

Route::get('/department/placed-students', function () {
    return view('pages.users.pages.placedStudents.managePlacedStudents');
});

Route::get('/department/placement-notices', function () {
    return view('pages.users.pages.placementNotices.managePlacementNotices');
});

Route::get('/department/successful-entrepreneurs', function () {
    return view('pages.users.pages.successfulEntrepreneurs.manageSuccessfulEntrepreneurs');
});

Route::get('/career-notices', function () {
    return view('pages.users.pages.careerNotices.manageCareerNotices');
});

Route::get('/why-us', function () {
    return view('pages.users.pages.whyUs.manageWhyUs');
});

Route::get('/scholarships', function () {
    return view('pages.users.pages.scholarship.manageScholarships');
});

// Department View Pages

Route::get('/department/view/{uuid}', function () {
    return view('modules.departments.viewDepartment');
});

Route::get('/curriculum-syllabus/view/{uuid}', function () {
    return view('modules.curriculumSyllabuses.viewCurriculumSyllabuses');
});

Route::get('/announcements/view/{uuid}', function () {
    return view('modules.announcements.viewAnnouncements');
});

Route::get('/achievements/view/{uuid}', function () {
    return view('modules.achievements.viewAchievements');
});

Route::get('/notices/view/{uuid}', function () {
    return view('modules.notices.viewNotices');
});

Route::get('/student-activities/view/{uuid}', function () {
    return view('modules.studentActivities.viewStudentActivities');
});

Route::get('/gallery/view/{uuid}', function () {
    return view('modules.gallery.viewGallery');
});

Route::get('/placed-students/view/{uuid}', function () {
    return view('modules.placedStudents.viewPlacedStudents');
});

Route::get('/placement-notices/view/{uuid}', function () {
    return view('modules.placementNotices.viewPlacementNotices');
});

Route::get('/successful-entrepreneurs/view/{uuid}', function () {
    return view('modules.successfulEntrepreneurs.viewSuccessfulEntrepreneurs');
});

Route::get('/career-notices/view/{uuid}', function () {
    return view('modules.careerNotices.viewCareerNotices');
});

Route::get('/why-us/view/{uuid}', function () {
    return view('modules.whyUs.viewWhyUs');
});

Route::get('/scholarships/view/{uuid}', function () {
    return view('modules.scholarship.viewScholarships');
});

Route::get('/success-stories/view/{uuid}', function () {
    return view('modules.successStory.viewSuccessStories');
});


// Course Manage

Route::get('/course/manage', function () {
    return view('pages.users.pages.course.manageCourses');
});
Route::get('/course/manage', function () {
    return view('pages.users.pages.course.manageCourses');
});
Route::get('/course/semester/manage', function () {
    return view('pages.users.pages.course.manageCourseSemester');
});
Route::get('/course/semester/section/manage', function () {
    return view('pages.users.pages.course.manageCourseSemesterSection');
});

Route::get('/course/subject/manage', function () {
    return view('pages.users.pages.subject.manageSubject');
});


//Feedback
Route::get('/feedback/submit', function () {
    return view('pages.users.pages.feedback.giveFeedBack');
});
Route::get('/feedback/question/manage', function () {
    return view('pages.users.pages.feedback.feedbackQuestion');
});
Route::get('/feedback/post/manage', function () {
    return view('pages.users.pages.feedback.feedbackPost');
});
Route::get('/feedback/manage', function () {
    return view('pages.users.pages.feedback.feedbackManage');
});
Route::get('/feedback/result', function () {
    return view('pages.users.pages.feedback.feedbackResult');
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
Route::get('/top-header/menu', function () {
    return view('pages.users.pages.headerMenus.manageTopHeaderMenu');
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

Route::get('/alumni-speak/manage', function () {
    return view('pages.users.pages.home.manageAlumniSpeaks');
});

Route::get('/center-iframes/manage', function () {
    return view('pages.users.pages.home.manageCenterIframes');
});

Route::get('/stats/settings', function () {
    return view('pages.users.pages.home.settingsStats');
});

Route::get('/notice-marquee/settings', function () {
    return view('pages.users.pages.home.settingsNoticeMarquee');
});

Route::get('/recruiters', function () {
    return view('pages.users.pages.home.recruiters');
});

Route::get('/success-stories/manage', function () {
    return view('pages.users.pages.successStory.manageSuccessStories');
});

Route::get('/events/manage', function () {
    return view('pages.users.pages.home.manageEvents');
});

Route::get('/header-components/manage', function () {
    return view('pages.users.pages.headerComponents.manageHeaderComponents');
});

Route::get('/footer-components/manage', function () {
    return view('pages.users.pages.footerComponents.manageFooterComponents');
});


// View All Pages

Route::get('/announcements', function () {

    return view('landing.pages.announcements.viewAllAnnouncements');

});
 
Route::get('/achievements', function () {

    return view('landing.pages.achievements.viewAllAchievements');

});
 
Route::get('/notices', function () {

    return view('landing.pages.notices.viewAllNotices');

});
 
Route::get('/student-activities', function () {

    return view('landing.pages.studentActivities.viewAllStudentActivities');

});
 
Route::get('/gallery', function () {

    return view('landing.pages.gallery.viewAllGallery');

});

// Without Headers & Footer
Route::get('/gallery/all-images', function () {

    return view('landing.pages.gallery.viewAllGallery');

});
 
Route::get('/our-recruiters', function () {

    return view('landing.pages.ourRecruiters.viewAllOurRecruiters');

});
 
Route::get('/success-stories', function () {

    return view('landing.pages.successStory.viewAllsuccessStory');

});
 
Route::get('/courses', function () {

    return view('landing.pages.course.viewAllCourses');

});
 
Route::get('/courses/view/{uuid}', function () {

    return view('modules.course.viewcourses');

});
 
Route::get('/events', function () {

    return view('landing.pages.events.viewAllEvents');

});

Route::get('/faculty-members', function () {

    return view('landing.pages.faculty.viewAllFaculty');

});

Route::get('/technical-assistants', function () {

    return view('landing.pages.technicalAssistant.viewAllTechnicalAssistant');

});

Route::get('/placement-officers', function () {

    return view('landing.pages.placementOfficer.viewAllPlacementOfficer');

});

Route::get('/placed-students', function () {

    return view('landing.pages.placedStudents.viewAllPlacedStudents');

});
 
Route::get('/tp-cell', function () {

    return view('landing.pages.t&pCell.viewAllT&PCell');

});

Route::get('/placement-notices', function () {

    return view('landing.pages.placementNotices.viewAllPlacementNotices');

});
 
Route::get('/statistics', function () {

    return view('landing.pages.statistics.viewAllStatistics');

});
 
Route::get('/contact-us', function () {

    return view('landing.pages.contactUs.viewContactUs');

});
 
Route::get('/contact-us/manage', function () {
    return view('pages.users.pages.contactUs.manageContacts');
});
 
Route::get('/contact-us-visibility/manage', function () {
    return view('pages.users.pages.contactUs.manageContactVisibility');
});

Route::get('/enquiry', function () {
    return view('landing.pages.enquiry.createEnquiry');
});
 
Route::get('/students/manage', function () {
    return view('pages.users.pages.users.manageStudents');
});
 
Route::get('/senior-authority/manage', function () {
    return view('pages.users.pages.users.manageSeniorAuthority');
});
 
Route::get('/other-roles/manage', function () {
    return view('pages.users.pages.users.manageOtherRole');
});
 
Route::get('/faculty/manage', function () {
    return view('pages.users.pages.users.manageFaculty');
});
 
Route::get('/faculty-preview-order', function () {
    return view('pages.users.pages.faculty.facultyPreviewOrder');
});
 
Route::get('/technical-assistant-preview-order', function () {
    return view('pages.users.pages.technicalAssistant.technicalAssistantPreviewOrder');
});
 
Route::get('/placement-officer-preview-order', function () {
    return view('pages.users.pages.placementOfficer.placementOfficerPreviewOrder');
});
 
Route::get('/sticky-buttons/manage', function () {
    return view('pages.users.pages.stickyButtons.manageStickyButtons');
});
 
Route::get('/master-approval/manage', function () {
    return view('pages.users.pages.masterApproval.manageMasterApproval');
});
 
Route::get('/student-subject-attendance', function () {
    return view('pages.users.pages.subject.studentSubjectAttendance');
});


// Activity Logs
Route::get('/activity-logs', fn () => view('pages.users.pages.userActivityLogs.userActivityLogsView'));

 
Route::get('/meta-tags/manage', function () {
    return view('pages.users.pages.metaTags.manageMetaTags');
});