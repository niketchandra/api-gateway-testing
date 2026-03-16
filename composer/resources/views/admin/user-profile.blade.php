@extends('app')

@section('title', 'User Profile - Admin - AtGlance')

@section('dashboard-content')
<div style="padding: 40px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h1 style="font-size:28px; color:#111827;">User Profile</h1>
        <a href="{{ route('admin.users') }}" style="text-decoration:none; color:#4f46e5;">← Back to Users</a>
    </div>

    @if(session('success'))
        <div style="padding:12px; border-radius:8px; background:#dcfce7; color:#166534; margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="padding:12px; border-radius:8px; background:#fee2e2; color:#991b1b; margin-bottom:16px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
        <a href="#edit-user-profile" style="background:#4f46e5; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600; text-decoration:none;">Open User Profile</a>
        <a href="{{ route('systems-registered') }}" style="background:#111827; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600; text-decoration:none;">Open Systems Registered</a>
        <a href="{{ route('configuration-backups') }}" style="background:#0ea5e9; color:white; border:none; border-radius:8px; padding:10px 14px; font-weight:600; text-decoration:none;">Open Configuration Backups</a>
    </div>

    <div id="edit-user-profile" style="background:white; border-radius:10px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.1); margin-top:20px;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:20px;">
            <div>
                <h2 style="font-size:22px; color:#111827; margin:0 0 8px 0;">Edit User Profile</h2>
                <p style="color:#6b7280; margin:0;">Update the user's details, account status, and access role from this page.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.update', ['user' => $user->id]) }}">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label for="username" style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Username</label>
                    <input id="username" type="text" name="username" value="{{ old('username', $user->name) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
                <div>
                    <label for="email" style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label for="first_name" style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">First Name</label>
                    <input id="first_name" type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
                <div>
                    <label for="last_name" style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Last Name</label>
                    <input id="last_name" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                <div>
                    <label for="status" style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Status</label>
                    <select id="status" name="status" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; background:white;">
                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div>
                    <label for="role" style="display:block; font-size:13px; color:#4b5563; margin-bottom:6px;">Role</label>
                    <select id="role" name="role" required style="width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px; background:white;">
                        <option value="user" {{ old('role', in_array((int) $user->rbac_id, [100, 101], true) ? 'admin' : 'user') === 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('role', in_array((int) $user->rbac_id, [100, 101], true) ? 'admin' : 'user') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end;">
                <button type="submit" style="background:#16a34a; color:white; border:none; border-radius:8px; padding:10px 16px; font-weight:600; cursor:pointer;">Save User Profile</button>
            </div>
        </form>
    </div>
</div>
@endsection
