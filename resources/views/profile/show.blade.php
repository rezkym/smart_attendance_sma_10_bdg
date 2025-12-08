@extends('layouts.layoutMaster')

@section('title', 'Profile')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">User /</span> Profile
</h4>

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Profile Details</h5>
      <!-- Account -->
      <div class="card-body">
        <div class="d-flex align-items-start align-items-sm-center gap-4">
          <img src="{{ asset('assets/img/avatars/1.png') }}" alt="user-avatar" class="d-block rounded" height="100" width="100" id="uploadedAvatar" />
          <div class="button-wrapper">
            <h4 class="mb-1">{{ auth()->user()->name }}</h4>
            <p class="text-muted mb-0">{{ auth()->user()->email }}</p>
            <div class="mt-2">
                @foreach(auth()->user()->getRoleNames() as $role)
                    <span class="badge bg-label-primary">{{ $role }}</span>
                @endforeach
            </div>
          </div>
        </div>
      </div>
      <hr class="my-0" />
      <div class="card-body">
          <div class="row">
            <div class="mb-3 col-md-6">
              <label for="name" class="form-label">Name</label>
              <input class="form-control" type="text" id="name" name="name" value="{{ auth()->user()->name }}" readonly />
            </div>
            <div class="mb-3 col-md-6">
              <label for="email" class="form-label">E-mail</label>
              <input class="form-control" type="text" id="email" name="email" value="{{ auth()->user()->email }}" readonly />
            </div>
          </div>
      </div>
      <!-- /Account -->
    </div>
  </div>
</div>
@endsection
