@extends('app')

@section('title', 'System Services - AtGlance')

@section('dashboard-content')
<div style="padding: 40px;">
    <style>
        .service-page-card {
            background: var(--color-white);
            border: 1px solid var(--color-border);
            border-radius: 14px;
            box-shadow: var(--shadow-sm);
        }

        .service-card {
            background: linear-gradient(180deg, #ffffff 0%, #fafcff 100%);
            border: 1px solid var(--color-border);
            border-radius: 14px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: all var(--transition-base);
        }

        .service-card:hover {
            transform: translateY(-3px);
            border-color: var(--color-accent-blue);
            box-shadow: var(--shadow-lg);
        }

        .service-card-header {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            padding: 20px;
            color: white;
        }
    </style>
    <div style="margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 28px; font-weight: bold; color: #333; margin-bottom: 8px;">Services</h1>
            <p style="color: #666; font-size: 14px;">System #{{ $system->id }} - {{ $system->system_name ?? 'N/A' }}</p>
        </div>
        <a href="{{ route('systems-registered') }}" class="btn-save" style="padding: 10px 20px; border-radius: 10px; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Systems
        </a>
    </div>

    <div class="service-page-card" style="padding: 24px; margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
            <i class="fas fa-filter" style="color: #111827; font-size: 18px;"></i>
            <h3 style="font-size: 16px; font-weight: bold; color: #333; margin: 0;">Search & Filter Services</h3>
        </div>
        <form method="GET" action="{{ route('systems-registered.services', ['systemId' => $system->id]) }}">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="font-size: 12px; color: #666; font-weight: 600; display: block; margin-bottom: 6px;">Service Name</label>
                    <input type="text" name="service_name" value="{{ request('service_name') }}" placeholder="Search by service" style="width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#111827'" onblur="this.style.borderColor='#e0e0e0'">
                </div>
                <div>
                    <label style="font-size: 12px; color: #666; font-weight: 600; display: block; margin-bottom: 6px;">Status</label>
                    <select name="status" style="width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 14px; outline: none; background: white; cursor: pointer; transition: border-color 0.2s;" onfocus="this.style.borderColor='#111827'" onblur="this.style.borderColor='#e0e0e0'">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn-save" style="padding: 12px 24px; border-radius: 10px; font-size: 14px;">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="{{ route('systems-registered.services', ['systemId' => $system->id]) }}" class="btn-secondary" style="padding: 12px 24px; border-radius: 10px; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-redo"></i> Clear
                </a>
            </div>
        </form>
    </div>

    @if($services->isEmpty())
        <div style="background: white; padding: 60px; border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); text-align: center;">
            <i class="fas fa-cubes" style="font-size: 48px; color: #ddd; margin-bottom: 16px;"></i>
            <p style="color: #999; font-size: 16px;">No services found for this system</p>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
            @foreach($services as $service)
                @php $isActive = strtolower((string) $service->status) === 'active'; @endphp
                <div class="service-card">
                    <div class="service-card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-size: 11px; opacity: 0.85;">SERVICE ID</div>
                                <div style="font-size: 18px; font-weight: bold;">#{{ $service->service_id }}</div>
                            </div>
                            <div style="padding: 6px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; background: {{ $isActive ? '#166534' : '#7f1d1d' }}; border: 1px solid rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 0.4px;">
                                {{ ucfirst($service->status) }}
                            </div>
                        </div>
                    </div>

                    <div style="padding: 24px;">
                        <div style="margin-bottom: 12px;">
                            <div style="font-size: 11px; color: #999; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Service Name</div>
                            <div style="font-size: 16px; color: #333; font-weight: 700;">{{ $service->service_name }}</div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px;">
                            <div style="background: #f3f4f6; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 10px; color: #777; font-weight: 600;">CONFIG FILES</div>
                                <div style="font-size: 16px; color: #333; font-weight: 700;">{{ $service->config_count ?? 0 }}</div>
                            </div>
                            <div style="background: #f3f4f6; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 10px; color: #777; font-weight: 600;">LATEST VERSION</div>
                                <div style="font-size: 16px; color: #333; font-weight: 700;">{{ $service->latest_version ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <button onclick="window.location.href='{{ route('configuration-backups.service-versions', ['serviceId' => $service->service_id]) }}'" class="btn-save" style="width: 100%; padding: 12px; border-radius: 10px; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fas fa-folder-open"></i> View Configuration Files & Versions
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
