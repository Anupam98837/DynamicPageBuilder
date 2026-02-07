@extends('pages.users.layout.structure')

@section('title', 'Manage Contact Info')

@php
  // Role comes from query param after JS fetches /api/users/me
  $roleParam = request()->query('role');

  $role = strtolower(trim((string) $roleParam));
  $role = str_replace([' ', '-'], '_', $role);
  $tmp  = preg_replace('/_+/', '_', $role);
  $role = $tmp !== null ? $tmp : $role;
  $role = trim($role, '_');

  // Map roles -> dashboard view
  $roleToView = [
    'admin'               => 'modules.dashboard.adminDashboard',
    'student'             => 'modules.dashboard.studentDashboard',
    'director'            => 'modules.dashboard.directorDashboard',
    'principal'           => 'modules.dashboard.principalDashboard',
    'hod'                 => 'modules.dashboard.hodDashboard',
    'faculty'             => 'modules.dashboard.facultyDashboard',
    'technical_assistant' => 'modules.dashboard.technicalAssistantDashboard',
    'it_person'           => 'modules.dashboard.itPersonDashboard',
    'placement_officer'   => 'modules.dashboard.placementOfficerDashboard',
  ];

  $dashboardView = $role !== '' ? ($roleToView[$role] ?? null) : null;

  // If role is present but dashboard file doesn't exist -> Page Not Found
  if ($role !== '' && (!$dashboardView || !view()->exists($dashboardView))) {
    abort(404);
  }
@endphp

{{-- If role already resolved (via ?role=...), include the correct dashboard --}}
@if ($dashboardView)
  @include($dashboardView)
@else
  {{-- First load: no role param yet -> show loading UI (JS will fetch role and reload) --}}
  <div class="p-4">
    <div class="alert alert-info mb-0">
      Loading dashboard...
    </div>
  </div>
@endif

@section('scripts')
<script>
  document.addEventListener('DOMContentLoaded', async function () {
    const token = sessionStorage.getItem('token') || localStorage.getItem('token');
    if (!token) {
      window.location.href = '/';
      return;
    }

    const url = new URL(window.location.href);
    const currentRoleParam = (url.searchParams.get('role') || '').trim();

    const normalizeRole = function (r) {
      r = String(r || '').trim().toLowerCase();
      r = r.replace(/[\s-]+/g, '_').replace(/_+/g, '_').replace(/^_+|_+$/g, '');
      return r;
    };

    try {
      // Call /api/users/me to get real role
      const res = await fetch('/api/users/me', {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ' + token
        }
      });

      if (res.status === 401 || res.status === 403) {
        sessionStorage.removeItem('token');
        localStorage.removeItem('token');
        window.location.href = '/';
        return;
      }

      const json = await res.json().catch(() => ({}));

      // Expected: { success:true, data:{ role:'admin' } }
      const apiRoleRaw = (json && json.success && json.data && json.data.role) ? json.data.role : '';
      const apiRole = normalizeRole(apiRoleRaw);

      // If API didn't return a role, treat as invalid session
      if (!apiRole) {
        window.location.href = '/';
        return;
      }

      // If no role param yet -> set it and reload (Blade will include correct dashboard)
      if (!currentRoleParam) {
        url.searchParams.set('role', apiRole);
        window.location.replace(url.toString());
        return;
      }

      // If role param exists but differs from actual -> correct it and reload
      if (normalizeRole(currentRoleParam) !== apiRole) {
        url.searchParams.set('role', apiRole);
        window.location.replace(url.toString());
        return;
      }

      // If role matches, do nothing (Blade already included correct dashboard)
    } catch (e) {
      window.location.href = '/';
    }
  });
</script>
@endsection
