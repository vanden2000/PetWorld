@csrf
@if(isset($method)) @method($method) @endif
<div class="species-form">
  <section class="species-panel"><h2 class="species-panel-title"><i class="fa-solid fa-paw"></i> Thông tin loài</h2>
    <div class="species-form-grid"><div class="species-field"><label for="name">Tên loài *</label><input id="name" name="name" type="text" required value="{{ old('name', $petSpecies->name) }}" placeholder="Ví dụ: Chó">@error('name')<p class="species-error">{{ $message }}</p>@enderror</div><div class="species-field"><label for="slug">Slug *</label><input id="slug" name="slug" type="text" required value="{{ old('slug', $petSpecies->slug) }}" placeholder="dog">@error('slug')<p class="species-error">{{ $message }}</p>@enderror</div></div>
    <div class="species-field">
      <label for="image">Ảnh đại diện</label>
      <div class="species-upload-dropzone" onclick="document.getElementById('image').click();">
        <span class="species-upload-preview" id="imagePreview" style="background:{{ $petSpecies->background_color ?: '#FFF2E8' }}">
          @if($petSpecies->image)
            <img src="{{ asset('storage/'.$petSpecies->image) }}" alt="Ảnh hiện tại" id="previewImg">
          @else
            <i class="fa-solid fa-cloud-arrow-up" id="previewIcon" style="color: #9a6849;"></i>
          @endif
        </span>
        <div class="species-upload-text">
          <strong id="uploadTextTitle">Chọn ảnh đại diện...</strong>
          <span>Kéo thả hoặc click để tải lên PNG, JPG, WEBP (Tối đa 5MB)</span>
        </div>
        <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/webp" class="species-file" style="display: none;">
      </div>
      <p class="species-help">Nếu chưa có ảnh đại diện, hệ thống sẽ sử dụng màu nền và biểu tượng chân thú mặc định.</p>
      @error('image')<p class="species-error">{{ $message }}</p>@enderror
    </div>
    <div class="species-form-grid">
      <div class="species-field">
        <label for="background_color">Màu nền card</label>
        <div class="species-color-wrapper">
          <div class="species-color-bubble" id="colorBubble" style="background: {{ old('background_color', $petSpecies->background_color ?: '#FFF2E8') }}" onclick="document.getElementById('background_color').click();" title="Chọn màu"></div>
          <input type="text" id="colorHex" class="form-control color-hex-input" value="{{ old('background_color', $petSpecies->background_color ?: '#FFF2E8') }}" style="max-width: 100px; text-transform: uppercase;" placeholder="#FFFFFF">
          <input id="background_color" name="background_color" type="color" class="species-color-hidden" value="{{ old('background_color', $petSpecies->background_color ?: '#FFF2E8') }}" style="opacity: 0; width: 0; height: 0; position: absolute; z-index: -1;">
          <span class="species-help" style="margin:0; white-space: nowrap;">Màu nền dự phòng.</span>
        </div>
        @error('background_color')<p class="species-error">{{ $message }}</p>@enderror
      </div>
      <div class="species-field">
        <label for="sort_order">Thứ tự hiển thị *</label>
        <input id="sort_order" name="sort_order" type="number" min="0" required value="{{ old('sort_order', $petSpecies->sort_order ?? 0) }}">
        @error('sort_order')<p class="species-error">{{ $message }}</p>@enderror
      </div>
    </div>
  </section>
  <aside class="species-panel">
    <h2 class="species-panel-title"><i class="fa-solid fa-sliders"></i> Hiển thị</h2>
    <input type="hidden" name="is_active" value="0">
    <label class="species-switch">
      <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $petSpecies->exists ? $petSpecies->is_active : true))>
      <span><strong>Đang hoạt động</strong><span>Cho phép gán loài này khi tạo hoặc sửa sản phẩm.</span></span>
    </label>
    <input type="hidden" name="show_on_home" value="0">
    <label class="species-switch">
      <input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $petSpecies->show_on_home))>
      <span><strong>Hiển thị tại trang chủ</strong><span>Chỉ có tối đa 2 loài đang hoạt động được chọn.</span></span>
    </label>
    @error('show_on_home')<p class="species-error">{{ $message }}</p>@enderror

    <div class="species-form-actions" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color); display: flex; gap: 10px; justify-content: center;">
      <a href="{{ route('admin.pet-species') }}" class="species-cancel">Hủy</a>
      <button type="submit" class="species-save"><i class="fa-solid fa-floppy-disk"></i> Lưu loài</button>
    </div>
  </aside>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
      const fileInput = document.getElementById('image');
      const previewImg = document.getElementById('previewImg');
      const imagePreview = document.getElementById('imagePreview');
      const uploadTextTitle = document.getElementById('uploadTextTitle');
      
      if (fileInput) {
          fileInput.addEventListener('change', function() {
              const file = this.files[0];
              if (file) {
                  const reader = new FileReader();
                  reader.onload = function(e) {
                      if (previewImg) {
                          previewImg.src = e.target.result;
                      } else {
                          const newImg = document.createElement('img');
                          newImg.id = 'previewImg';
                          newImg.src = e.target.result;
                          newImg.alt = 'Ảnh mới';
                          imagePreview.innerHTML = '';
                          imagePreview.appendChild(newImg);
                      }
                      if (uploadTextTitle) {
                          uploadTextTitle.textContent = file.name;
                      }
                  }
                  reader.readAsDataURL(file);
              }
          });
      }
      
      const colorPicker = document.getElementById('background_color');
      const colorHex = document.getElementById('colorHex');
      const colorBubble = document.getElementById('colorBubble');
      
      if (colorPicker && colorHex && colorBubble) {
          colorPicker.addEventListener('input', function() {
              const val = colorPicker.value;
              colorHex.value = val;
              colorBubble.style.backgroundColor = val;
              if (imagePreview && !imagePreview.querySelector('img')) {
                  imagePreview.style.backgroundColor = val;
              }
          });
          
          colorHex.addEventListener('input', function() {
              const val = colorHex.value;
              if (/^#[0-9A-F]{6}$/i.test(val)) {
                  colorPicker.value = val;
                  colorBubble.style.backgroundColor = val;
                  if (imagePreview && !imagePreview.querySelector('img')) {
                      imagePreview.style.backgroundColor = val;
                  }
              }
          });
      }
  });
</script>
