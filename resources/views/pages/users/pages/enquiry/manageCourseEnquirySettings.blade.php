@extends('pages.users.layout.structure')

@section('title', 'Manage Course Enquiry Settings')

@include('modules.enquiry.manageCourseEnquirySettings')

@section('scripts')
<script>
  // On DOM ready, verify token; if missing, redirect home
  document.addEventListener('DOMContentLoaded', function() {
    if (!sessionStorage.getItem('token') && !localStorage.getItem('token')) {
      window.location.href = '/';
    }
  });
</script>
@endsection
