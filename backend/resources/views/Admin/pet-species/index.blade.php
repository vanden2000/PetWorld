@extends('admin.layouts.app')

@section('title', 'Quản lý loài thú cưng')
@section('styles') @include('admin.pet-species._styles') @endsection

@section('content')
<div class="species-page">
  <header class="species-header">
    <div><div class="species-kicker"><i class="fa-solid fa-paw"></i> Phân loại sản phẩm</div><h1>Loài thú cưng</h1><p>Gán loài trực tiếp cho sản phẩm và chọn tối đa hai loài nổi bật tại trang chủ.</p></div>
    <a href="{{ route('admin.pet-species.create') }}" class="species-add"><i class="fa-solid fa-plus"></i> Thêm loài</a>
  </header>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  <div class="species-metrics"><div class="species-metric"><span>Tổng số loài</span><strong>{{ $petSpecies->count() }}</strong></div><div class="species-metric"><span>Đang hoạt động</span><strong>{{ $petSpecies->where('is_active', true)->count() }}</strong></div><div class="species-metric"><span>Nổi bật trang chủ</span><strong>{{ $petSpecies->where('show_on_home', true)->count() }}/2</strong></div></div>
  <div class="species-grid">
    @forelse($petSpecies as $species)
      <div class="species-card">
        <!-- Badge row -->
        <div class="species-card-badges">
          @if($species->show_on_home)
            <span class="species-badge species-badge--home"><i class="fa-solid fa-house"></i> Trang chủ</span>
          @endif
          <span class="species-badge {{ $species->is_active ? 'species-badge--active' : 'species-badge--hidden' }}">
            {{ $species->is_active ? 'Đang bật' : 'Đang ẩn' }}
          </span>
        </div>

        <!-- Card content -->
        <div class="species-card-body">
          <div class="species-card-avatar" style="background: {{ $species->background_color ?: '#FFF2E8' }}">
            @if($species->image)
              <img src="{{ asset('storage/'.$species->image) }}" alt="{{ $species->name }}">
            @else
              <i class="fa-solid fa-paw"></i>
            @endif
          </div>
          <h3 class="species-card-name">{{ $species->name }}</h3>
          <span class="species-card-slug">/{{ $species->slug }}</span>
        </div>

        <!-- Card stats -->
        <div class="species-card-stats">
          <div class="species-stat-item">
            <i class="fa-solid fa-box"></i>
            <div>
              <strong>{{ $species->products_count }}</strong>
              <span>Sản phẩm</span>
            </div>
          </div>
          <div class="species-stat-item">
            <i class="fa-solid fa-arrow-down-up-order"></i>
            <div>
              <strong>#{{ $species->sort_order }}</strong>
              <span>Thứ tự</span>
            </div>
          </div>
        </div>

        <!-- Card actions -->
        <div class="species-card-actions">
          <a href="{{ route('admin.pet-species.edit', $species) }}" class="species-card-btn-edit">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Chỉnh sửa</span>
          </a>
        </div>
      </div>
    @empty
      <div class="species-card-empty">
        <i class="fa-solid fa-paw"></i>
        <p style="margin: 0; font-weight: 600;">Chưa có loài thú cưng nào. Hãy thêm loài đầu tiên.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection
