@extends('admin.layouts.app')
@section('title', $article->exists ? 'Sửa kiến thức chatbot' : 'Thêm kiến thức chatbot')
@section('content')
<div class="dashboard-header"><div class="header-title-block"><h1>{{ $article->exists ? 'Sửa bài kiến thức' : 'Thêm bài kiến thức' }}</h1><p>Nội dung xuất bản sẽ là nguồn trả lời chính sách của chatbot.</p></div></div>
@if(session('success')) <div class="alert-panel alert-success-box">{{ session('success') }}</div> @endif
<form method="POST" action="{{ $article->exists ? route('admin.knowledge.update', $article) : route('admin.knowledge.store') }}" class="form-card" style="max-width:900px">@csrf @if($article->exists) @method('PUT') @endif
<div class="form-control-group"><label class="form-field-label">Tiêu đề</label><input class="input-text-field" name="title" value="{{ old('title', $article->title) }}" required>@error('title')<span class="error-text">{{ $message }}</span>@enderror</div>
<div class="form-control-row"><div class="form-control-group"><label class="form-field-label">Nhóm</label><select class="input-select-field" name="category" required>@foreach(['shipping'=>'Giao hàng','payment'=>'Thanh toán','returns'=>'Đổi trả','voucher'=>'Voucher','contact'=>'Liên hệ'] as $value=>$label)<option value="{{ $value }}" @selected(old('category',$article->category)===$value)>{{ $label }}</option>@endforeach</select></div><div class="form-control-group"><label class="form-field-label">Trạng thái</label><select class="input-select-field" name="status" required>@foreach(['draft'=>'Nháp','published'=>'Xuất bản','archived'=>'Lưu trữ'] as $value=>$label)<option value="{{ $value }}" @selected(old('status',$article->status ?: 'draft')===$value)>{{ $label }}</option>@endforeach</select></div></div>
<div class="form-control-group"><label class="form-field-label">Nội dung đã kiểm duyệt</label><textarea class="input-text-field" name="content" rows="16" required>{{ old('content', $article->content) }}</textarea>@error('content')<span class="error-text">{{ $message }}</span>@enderror</div>
<button class="btn-dark-slate" type="submit">Lưu bài kiến thức</button> <a href="{{ route('admin.knowledge') }}" class="btn-clear-filters">Quay lại</a>
</form>
@endsection
