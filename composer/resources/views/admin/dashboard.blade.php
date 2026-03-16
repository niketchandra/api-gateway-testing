@extends('app')

@section('title', 'Dashboard - AtGlance')

@section('dashboard-content')
<div style="padding: 40px;">
    <div style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); color: white; padding: 32px; border-radius: 10px; margin-bottom: 24px;">
        <h1 style="font-size: 28px; margin-bottom: 8px;">Dashboard</h1>
        <p style="opacity: 0.9;">Manage users, platform configuration, and global settings.</p>
    </div>

    <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:16px; margin-bottom:24px;">
        <div style="background:white; border-radius:10px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-left:4px solid #4f46e5;">
            <div style="font-size:12px; color:#6b7280; text-transform:uppercase;">Users</div>
            <div style="font-size:28px; font-weight:700; color:#111827;">{{ $totalUsers }}</div>
        </div>
        <div style="background:white; border-radius:10px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-left:4px solid #7c3aed;">
            <div style="font-size:12px; color:#6b7280; text-transform:uppercase;">Systems</div>
            <div style="font-size:28px; font-weight:700; color:#111827;">{{ $totalSystems }}</div>
        </div>
        <div style="background:white; border-radius:10px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-left:4px solid #0ea5e9;">
            <div style="font-size:12px; color:#6b7280; text-transform:uppercase;">Services</div>
            <div style="font-size:28px; font-weight:700; color:#111827;">{{ $totalServices }}</div>
        </div>
        <div style="background:white; border-radius:10px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-left:4px solid #16a34a;">
            <div style="font-size:12px; color:#6b7280; text-transform:uppercase;">Configuration Files</div>
            <div style="font-size:28px; font-weight:700; color:#111827;">{{ $totalConfigFiles }}</div>
        </div>
    </div>

    <div style="display:flex; gap:12px; margin-bottom:24px;">
        <a href="{{ route('admin.users') }}" style="text-decoration:none; background:#4f46e5; color:white; padding:10px 14px; border-radius:8px; font-weight:600;">Manage Users</a>
        <a href="{{ route('admin.settings') }}" style="text-decoration:none; background:#111827; color:white; padding:10px 14px; border-radius:8px; font-weight:600;">Site Setting</a>
    </div>
</div>
@endsection
