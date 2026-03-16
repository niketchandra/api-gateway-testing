@extends('app')

@section('title', 'User Profile - Admin - AtGlance')

@section('dashboard-content')
<div style="padding: 40px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="font-size:28px; color:#111827;">User Profile</h1>
        <a href="{{ route('admin.users') }}" style="text-decoration:none; color:#4f46e5;">← Back to Users</a>
    </div>

    <div style="background:white; border-radius:10px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.1); margin-bottom:20px;">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div>
                <div style="font-size:12px; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">User ID</div>
                <div style="font-size:16px; color:#111827; font-weight:700;">{{ $user->id }}</div>
            </div>
            <div>
                <div style="font-size:12px; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Status</div>
                @php $isActive = strtolower((string) $user->status) === 'active'; @endphp
                <span style="display:inline-block; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:600; background:{{ $isActive ? '#dcfce7' : '#fee2e2' }}; color:{{ $isActive ? '#166534' : '#991b1b' }};">{{ ucfirst($user->status ?? 'unknown') }}</span>
            </div>
            <div>
                <div style="font-size:12px; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Name</div>
                <div style="font-size:16px; color:#111827; font-weight:700;">{{ $user->name }}</div>
            </div>
            <div>
                <div style="font-size:12px; color:#6b7280; text-transform:uppercase; margin-bottom:6px;">Email</div>
                <div style="font-size:16px; color:#111827; font-weight:700;">{{ $user->email }}</div>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:20px;">
        <div style="background:white; border-radius:10px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-left:4px solid #7c3aed;">
            <div style="font-size:12px; color:#6b7280; text-transform:uppercase;">Systems Registered</div>
            <div style="font-size:24px; font-weight:700; color:#111827;">{{ $stats['systems'] }}</div>
        </div>
        <div style="background:white; border-radius:10px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-left:4px solid #0ea5e9;">
            <div style="font-size:12px; color:#6b7280; text-transform:uppercase;">Services</div>
            <div style="font-size:24px; font-weight:700; color:#111827;">{{ $stats['services'] }}</div>
        </div>
        <div style="background:white; border-radius:10px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,0.1); border-left:4px solid #16a34a;">
            <div style="font-size:12px; color:#6b7280; text-transform:uppercase;">Configs</div>
            <div style="font-size:24px; font-weight:700; color:#111827;">{{ $stats['configurations'] }}</div>
        </div>
    </div>

    <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <a href="{{ route('admin.users.show', ['user' => $user->id]) }}" style="background:#4f46e5; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600; text-decoration:none;">Open User Dashboard</a>
        <a href="{{ route('systems-registered') }}" style="background:#111827; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600; text-decoration:none;">Open Systems Registered</a>
        <a href="{{ route('configuration-backups') }}" style="background:#0ea5e9; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600; text-decoration:none;">Open Configuration Backups</a>
        <a href="{{ route('settings') }}" style="background:#16a34a; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600; text-decoration:none;">Open PAT/Settings</a>
    </div>
</div>
@endsection
