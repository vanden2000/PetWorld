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
  <div class="species-table-card"><table class="species-table"><thead><tr><th>Loài</th><th>Slug</th><th>Sản phẩm</th><th>Hiển thị</th><th>Thứ tự</th><th>Thao tác</th></tr></thead><tbody>
    @forelse($petSpecies as $species)<tr><td><div class="species-identity"><span class="species-avatar" style="background:{{ $species->background_color ?: '#f3f4f6' }}">@if($species->image)<img src="{{ asset('storage/'.$species->image) }}" alt="">@else<i class="fa-solid fa-paw"></i>@endif</span>{{ $species->name }}</div></td><td><span class="species-slug">{{ $species->slug }}</span></td><td>{{ $species->products_count }}</td><td>@if($species->show_on_home)<span class="species-badge species-badge--home"><i class="fa-solid fa-house"></i> Trang chủ</span>@endif <span class="species-badge {{ $species->is_active ? 'species-badge--active' : 'species-badge--hidden' }}">{{ $species->is_active ? 'Đang bật' : 'Đang ẩn' }}</span></td><td>{{ $species->sort_order }}</td><td><a href="{{ route('admin.pet-species.edit', $species) }}" class="species-edit"><i class="fa-solid fa-pen"></i> Sửa</a></td></tr>
    @empty<tr><td colspan="6" style="padding:42px;text-align:center;color:var(--text-muted)">Chưa có loài thú cưng. Hãy thêm loài đầu tiên.</td></tr>@endforelse
  </tbody></table></div>
</div>
@endsection
