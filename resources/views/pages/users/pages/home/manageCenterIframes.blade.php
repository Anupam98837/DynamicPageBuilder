@extends('pages.users.layout.structure')

@section('title', 'Manage Center Iframes')

@include('modules.home.manageCenterIframes')

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
