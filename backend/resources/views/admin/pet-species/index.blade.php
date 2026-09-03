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

  <div class="species-filters-grid">
    <!-- Search -->
    <div class="species-filter-col">
      <label class="species-filter-label">Tìm kiếm loài</label>
      <div class="filter-search-wrapper" style="position: relative;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 0.9rem;"></i>
        <input type="text" id="speciesSearch" placeholder="Tên hoặc slug..." autocomplete="off" style="width: 100%; height: 38px; padding: 0 16px 0 40px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.88rem; outline: none; transition: var(--transition-default); background-color: #fff;">
      </div>
    </div>

    <!-- Status -->
    <div class="species-filter-col">
      <label class="species-filter-label">Trạng thái</label>
      <div class="custom-admin-select-container">
        <div class="custom-admin-select-trigger">
          <span>Tất cả</span>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <input type="hidden" class="filter-input-val" id="filterStatus" value="">
        <div class="custom-admin-select-options">
          <div class="custom-admin-select-option selected" data-value="">Tất cả</div>
          <div class="custom-admin-select-option" data-value="active">Đang hoạt động</div>
          <div class="custom-admin-select-option" data-value="hidden">Đang ẩn</div>
        </div>
      </div>
    </div>

    <!-- Products count -->
    <div class="species-filter-col">
      <label class="species-filter-label">Sản phẩm</label>
      <div class="custom-admin-select-container">
        <div class="custom-admin-select-trigger">
          <span>Tất cả</span>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <input type="hidden" class="filter-input-val" id="filterProduct" value="">
        <div class="custom-admin-select-options">
          <div class="custom-admin-select-option selected" data-value="">Tất cả</div>
          <div class="custom-admin-select-option" data-value="has_products">Đã gán sản phẩm</div>
          <div class="custom-admin-select-option" data-value="no_products">Chưa gán sản phẩm</div>
        </div>
      </div>
    </div>

    <!-- Home display -->
    <div class="species-filter-col">
      <label class="species-filter-label">Hiển thị trang chủ</label>
      <div class="custom-admin-select-container">
        <div class="custom-admin-select-trigger">
          <span>Tất cả</span>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
        <input type="hidden" class="filter-input-val" id="filterHome" value="">
        <div class="custom-admin-select-options">
          <div class="custom-admin-select-option selected" data-value="">Tất cả</div>
          <div class="custom-admin-select-option" data-value="home">Nổi bật trang chủ</div>
          <div class="custom-admin-select-option" data-value="not_home">Không nổi bật</div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="species-filter-col">
      <button type="button" class="btn-reset-filters" id="btnResetFilters">
        <i class="fa-solid fa-rotate-left"></i>
        <span>Xóa lọc</span>
      </button>
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
                <span class="species-products-count" data-count="{{ $species->products_count }}">{{ $species->products_count }}</span>
              </td>
              <td style="text-align: center;">
                <div style="display: inline-flex; gap: 8px; justify-content: center;" data-active="{{ $species->is_active ? '1' : '0' }}" data-home="{{ $species->show_on_home ? '1' : '0' }}">
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
    <!-- Table Footer status bar -->
    <div class="species-table-footer" style="border-top: 1px solid var(--border-color); padding: 14px 20px; background: #fafbfc; display: flex; justify-content: flex-end; align-items: center; border-radius: 0 0 14px 14px;">
        <div style="font-size: 0.82rem; color: var(--text-muted); font-weight: 500;">
            Hiển thị <strong style="color: var(--text-main); font-weight: 700;" id="visibleCount">{{ $petSpecies->count() }}</strong> trên <strong style="color: var(--text-main); font-weight: 700;">{{ $petSpecies->count() }}</strong> loài
        </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('speciesSearch');
      const filterStatus = document.getElementById('filterStatus');
      const filterProduct = document.getElementById('filterProduct');
      const filterHome = document.getElementById('filterHome');
      const visibleCountEl = document.getElementById('visibleCount');
      const rows = document.querySelectorAll('.species-table tbody tr');
      const dropdowns = document.querySelectorAll('.custom-admin-select-container');

      // Helper function to apply all filters together
      function applyFilters() {
          const q = searchInput ? searchInput.value.toLowerCase().trim() : '';
          const statusVal = filterStatus ? filterStatus.value : '';
          const productVal = filterProduct ? filterProduct.value : '';
          const homeVal = filterHome ? filterHome.value : '';
          
          let visibleCount = 0;
          
          rows.forEach(row => {
              if (row.id === 'species-empty-row') return;
              
              // 1. Search Query filter
              const nameText = row.querySelector('.species-name-text')?.textContent.toLowerCase() || '';
              const slugText = row.querySelector('.species-slug-text')?.textContent.toLowerCase() || '';
              const matchSearch = nameText.includes(q) || slugText.includes(q);
              
              // 2. Status filter
              const badgeContainer = row.querySelector('[data-active]');
              const isActive = badgeContainer ? badgeContainer.getAttribute('data-active') === '1' : false;
              let matchStatus = true;
              if (statusVal === 'active') matchStatus = isActive;
              else if (statusVal === 'hidden') matchStatus = !isActive;
              
              // 3. Products count filter
              const countBadge = row.querySelector('[data-count]');
              const count = countBadge ? parseInt(countBadge.getAttribute('data-count'), 10) : 0;
              let matchProduct = true;
              if (productVal === 'has_products') matchProduct = count > 0;
              else if (productVal === 'no_products') matchProduct = count === 0;
              
              // 4. Home display filter
              const isHome = badgeContainer ? badgeContainer.getAttribute('data-home') === '1' : false;
              let matchHome = true;
              if (homeVal === 'home') matchHome = isHome;
              else if (homeVal === 'not_home') matchHome = !isHome;
              
              if (matchSearch && matchStatus && matchProduct && matchHome) {
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
      }

      // Live search input listener
      if (searchInput) {
          searchInput.addEventListener('input', applyFilters);
      }

      // Dropdown toggle logic
      dropdowns.forEach(dropdown => {
          const trigger = dropdown.querySelector('.custom-admin-select-trigger');
          const triggerText = trigger.querySelector('span');
          const hiddenInput = dropdown.querySelector('.filter-input-val');
          const options = dropdown.querySelectorAll('.custom-admin-select-option');

          trigger.addEventListener('click', function(e) {
              e.stopPropagation();
              dropdowns.forEach(other => {
                  if (other !== dropdown) other.classList.remove('open');
              });
              dropdown.classList.toggle('open');
          });

          options.forEach(option => {
              option.addEventListener('click', function(e) {
                  e.stopPropagation();
                  const val = option.getAttribute('data-value');
                  const text = option.textContent;

                  hiddenInput.value = val;
                  triggerText.textContent = text;

                  options.forEach(opt => opt.classList.remove('selected'));
                  option.classList.add('selected');
                  dropdown.classList.remove('open');

                  // Apply filter on selection
                  applyFilters();
              });
          });
      });

      // Close dropdowns when clicking outside
      document.addEventListener('click', function() {
          dropdowns.forEach(dropdown => dropdown.classList.remove('open'));
      });

      // Reset filters button logic
      const btnResetFilters = document.getElementById('btnResetFilters');
      if (btnResetFilters) {
          btnResetFilters.addEventListener('click', function() {
              if (searchInput) searchInput.value = '';
              
              dropdowns.forEach(dropdown => {
                  const hiddenInput = dropdown.querySelector('.filter-input-val');
                  const triggerText = dropdown.querySelector('.custom-admin-select-trigger span');
                  const options = dropdown.querySelectorAll('.custom-admin-select-option');
                  const defaultOpt = options[0];
                  
                  if (hiddenInput) hiddenInput.value = '';
                  if (triggerText && defaultOpt) triggerText.textContent = defaultOpt.textContent;
                  
                  options.forEach(opt => opt.classList.remove('selected'));
                  if (defaultOpt) defaultOpt.classList.add('selected');
              });
              
              applyFilters();
          });
      }
  });
</script>
@endsection