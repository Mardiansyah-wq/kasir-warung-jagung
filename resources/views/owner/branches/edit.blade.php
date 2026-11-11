@extends('layouts.app')

@section('title', 'Ubah Cabang')

@section('content')
<div class="container py-6">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Ubah Cabang</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('owner.branches.update', $branch) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Cabang</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $branch->name) }}" required>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea class="form-control" id="address" name="address" rows="3">{{ old('address', $branch->address) }}</textarea>
                            @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $branch->phone) }}">
                            @error('phone')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-pencil me-1"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('owner.branches.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection