@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light"><a href="{{ route('admin.roles.index') }}">Master Jabatan</a> /</span>
                    Daftar Anggota: {{ $role->name }}
                </h4>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Dosen/User yang menjabat sebagai <strong>{{ $role->name }}</strong>
                    </h5>
                </div>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIDN / Username</th>
                                <th>Nama Lengkap</th>
                                <th>Email</th>
                                <th class="text-center">Aksi (Lepas Jabatan)</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($users as $index => $user)
                                <tr>
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td>{{ $user->username ?? '-' }}</td>
                                    <td>
                                        <strong>{{ $user->name }}</strong>
                                    </td>
                                    <td>
                                        {{ $user->email }}
                                    </td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.users.assign-role', $user->id) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin melepas jabatan {{ $role->name }} dari {{ $user->name }}?');">
                                            @csrf
                                            <!-- Re-sync the user roles WITHOUT the current role to simulate detaching -->
                                            @foreach($user->roles->pluck('name') as $currentRole)
                                                @if($currentRole !== $role->name)
                                                    <input type="hidden" name="roles[]" value="{{ $currentRole }}">
                                                @endif
                                            @endforeach

                                            <!-- Dummy input in case they have 0 roles left so validation doesn't fail if we want to allow empty roles -->
                                            <!-- Since 'roles' is required array in UserController, we should send an empty array if needed, but HTML forms don't send empty arrays well. -->
                                            <!-- A small hidden input trick: -->
                                            @if($user->roles->count() == 1)
                                                <input type="hidden" name="roles[]" value="">
                                            @endif

                                            <button type="submit" class="btn btn-sm btn-danger waves-effect"
                                                title="Lepas Jabatan">
                                                <i class="ri-user-unfollow-line me-1"></i> Lepas
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada dosen yang menduduki jabatan
                                        ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer pb-0">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection