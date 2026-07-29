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
  <div class="species-metrics">
    <div class="species-metric">
      <div class="species-metric-info">
        <span>Tổng số loài</span>
        <strong>{{ $petSpecies->count() }}</strong>
      </div>
      <div class="species-metric-icon"><i class="fa-solid fa-paw"></i></div>
    </div>
    <div class="species-metric">
      <div class="species-metric-info">
        <span>Đang hoạt động</span>
        <strong>{{ $petSpecies->where('is_active', true)->count() }}</strong>
      </div>
      <div class="species-metric-icon" style="color: #16734a;"><i class="fa-solid fa-circle-check"></i></div>
    </div>
    <div class="species-metric">
      <div class="species-metric-info">
        <span>Nổi bật trang chủ</span>
        <strong>{{ $petSpecies->where('show_on_home', true)->count() }}/2</strong>
      </div>
      <div class="species-metric-icon" style="color: #a34b13;"><i class="fa-solid fa-house"></i></div>
    </div>
  </div>

  <div class="table-actions-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 16px;">
      <div class="filter-search-wrapper" style="position: relative; flex: 1; max-width: 360px;">
          <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.9rem;"></i>
          <input type="text" id="speciesSearch" placeholder="Tìm kiếm loài thú cưng..." autocomplete="off" style="width: 100%; height: 40px; padding: 0 16px 0 40px; border: 1px solid var(--border-color); border-radius: 10px; font-size: 0.88rem; outline: none; transition: var(--transition-default); background-color: #fff;">
      </div>
      <div style="font-size: 0.85rem; color: var(--text-muted);">
          Hiển thị <strong style="color: var(--text-main); font-weight: 700;" id="visibleCount">{{ $petSpecies->count() }}</strong> / <strong>{{ $petSpecies->count() }}</strong> loài
      </div>
  </div>
  <div class="species-table-card">
    <div class="table-container">
      <table class="species-table">
        <thead>
          <tr>
            <th>Loài</th>
            <th>Slug</th>
            <th style="text-align: center;">Sản phẩm</th>
            <th style="text-align: center;">Hiển thị</th>
            <th style="text-align: center;">Thứ tự</th>
            <th style="text-align: center; width: 100px;">Thao tác</th>
          </tr>
        </thead>
        <tbody>
          @forelse($petSpecies as $species)
            <tr>
              <td>
                <div class="species-identity">
                  <span class="species-avatar" style="background: {{ $species->background_color ?: '#FFF2E8' }}">
                    @if($species->image)
                      <img src="{{ asset('storage/'.$species->image) }}" alt="{{ $species->name }}">
                    @else
                      <i class="fa-solid fa-paw"></i>
                    @endif
                  </span>
                  <span class="species-name-text">{{ $species->name }}</span>
                </div>
              </td>
              <td>
                <span class="species-slug-text">{{ $species->slug }}</span>
              </td>
              <td style="text-align: center;">
                <span class="species-products-count">{{ $species->products_count }}</span>
              </td>
              <td style="text-align: center;">
                <div style="display: inline-flex; gap: 8px; justify-content: center;">
                  @if($species->show_on_home)
                    <span class="species-badge species-badge--home"><i class="fa-solid fa-house"></i> Trang chủ</span>
                  @endif
                  <span class="species-badge {{ $species->is_active ? 'species-badge--active' : 'species-badge--hidden' }}">
                    {{ $species->is_active ? 'Đang bật' : 'Đang ẩn' }}
                  </span>
                </div>
              </td>
              <td style="text-align: center;">
                <span class="species-sort-order">#{{ $species->sort_order }}</span>
              </td>
              <td style="text-align: center;">
                <a href="{{ route('admin.pet-species.edit', $species) }}" class="species-table-action-btn" title="Chỉnh sửa">
                  <i class="fa-solid fa-pen"></i>
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="padding: 42px; text-align: center; color: var(--text-muted);">
                Chưa có loài thú cưng. Hãy thêm loài đầu tiên.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('speciesSearch');
      if (searchInput) {
          // Select only standard rows, not headers or the empty indicator
          const rows = document.querySelectorAll('.species-table tbody tr');
          const visibleCountEl = document.getElementById('visibleCount');
          
          searchInput.addEventListener('input', function() {
              const q = searchInput.value.toLowerCase().trim();
              let visibleCount = 0;
              
              rows.forEach(row => {
                  // Skip if it's the dynamic empty row
                  if (row.id === 'species-empty-row') return;
                  
                  const nameEl = row.querySelector('.species-name-text');
                  const slugEl = row.querySelector('.species-slug-text');
                  const nameText = nameEl ? nameEl.textContent.toLowerCase() : '';
                  const slugText = slugEl ? slugEl.textContent.toLowerCase() : '';
                  
                  if (nameText.includes(q) || slugText.includes(q)) {
                      row.style.display = '';
                      visibleCount++;
                  } else {
                      row.style.display = 'none';
                  }
              });
              
              if (visibleCountEl) {
                  visibleCountEl.textContent = visibleCount;
              }
              
              // Show empty row if no results
              let emptyRow = document.getElementById('species-empty-row');
              if (visibleCount === 0 && rows.length > 0) {
                  if (!emptyRow) {
                      emptyRow = document.createElement('tr');
                      emptyRow.id = 'species-empty-row';
                      emptyRow.innerHTML = `<td colspan="6" style="padding: 42px; text-align: center; color: var(--text-muted);">Không tìm thấy loài thú cưng phù hợp.</td>`;
                      document.querySelector('.species-table tbody').appendChild(emptyRow);
                  }
                  emptyRow.style.display = '';
              } else if (emptyRow) {
                  emptyRow.style.display = 'none';
              }
          });
      }
  });
</script>
@endsection