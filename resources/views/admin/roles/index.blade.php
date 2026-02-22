@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Master Data Jabatan (Roles)</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#createRoleModal">
                        <i class="ri-add-line me-1"></i> Tambah Jabatan
                    </button>
                </div>

                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Jabatan</th>
                                <th>Jumlah Pemegang Jabatan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                            @forelse ($roles as $index => $role)
                                <tr>
                                    <td>{{ $roles->firstItem() + $index }}</td>
                                    <td>
                                        <strong>{{ $role->name }}</strong>
                                        @if(in_array(strtolower($role->name), ['admin', 'dosen']))
                                            <span class="badge bg-label-danger ms-1" style="font-size: 0.65rem;">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.roles.show', $role->id) }}"
                                            class="badge rounded-pill bg-label-info">
                                            {{ $role->users_count }} Orang
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.roles.show', $role->id) }}"
                                            class="btn btn-sm btn-icon btn-text-info rounded-pill waves-effect"
                                            title="Lihat Dosen">
                                            <i class="ri-eye-line ri-20px"></i>
                                        </a>

                                        @if(!in_array(strtolower($role->name), ['admin', 'dosen']))
                                            <button type="button"
                                                class="btn btn-sm btn-icon btn-text-secondary rounded-pill waves-effect"
                                                data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id }}" title="Edit">
                                                <i class="ri-edit-2-line ri-20px"></i>
                                            </button>

                                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini? Dosen yang memegangnya akan kehilangan akses jabatan tersebut.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-icon btn-text-danger rounded-pill waves-effect"
                                                    title="Hapus">
                                                    <i class="ri-delete-bin-7-line ri-20px"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada data jabatan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer pb-0">
                    {{ $roles->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create Role -->
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Master Jabatan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="name" class="form-label">Nama Jabatan <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name') is-invalid @enderror" placeholder="Contoh: BPMI"
                                    required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modals Edit Role -->
    @foreach ($roles as $role)
        @if(!in_array(strtolower($role->name), ['admin', 'dosen']))
            <div class="modal fade" id="editRoleModal{{ $role->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Master Jabatan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="name{{ $role->id }}" class="form-label">Nama Jabatan <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="name{{ $role->id }}" name="name" class="form-control"
                                            value="{{ $role->name }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection