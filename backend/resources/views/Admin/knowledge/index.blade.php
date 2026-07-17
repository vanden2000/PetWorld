@extends('admin.layouts.app')
@section('title', 'Kiến thức chatbot')
@section('content')
<div class="dashboard-header"><div class="header-title-block"><h1>Kiến thức chatbot</h1><p>Chỉ bài đã xuất bản mới được chatbot dùng để trả lời chính sách và FAQ.</p></div><div class="header-actions"><a href="{{ route('admin.knowledge.create') }}" class="categories-add-btn">Thêm bài kiến thức</a></div></div>
@if(session('success')) <div class="alert-panel alert-success-box">{{ session('success') }}</div> @endif
<form class="filters-card orders-filter-card" method="GET"><div class="filter-col orders-filter-search" style="flex:1"><label class="filter-label">Tìm bài</label><input class="filter-input" name="search" value="{{ $search }}" placeholder="Tiêu đề..."></div><div class="filter-col orders-filter-actions"><button class="btn-dark-slate">Lọc</button></div></form>
<div class="table-card"><div class="table-container"><table class="orders-table"><thead><tr><th>Tiêu đề</th><th>Nhóm</th><th>Trạng thái</th><th>Phiên bản</th><th>Cập nhật</th><th></th></tr></thead><tbody>@forelse($articles as $article)<tr><td><strong>{{ $article->title }}</strong></td><td>{{ $article->category }}</td><td>{{ $article->status }}</td><td>v{{ $article->version }}</td><td>{{ $article->updated_at?->format('d/m/Y H:i') }}</td><td><a href="{{ route('admin.knowledge.edit', $article) }}">Sửa</a></td></tr>@empty<tr><td colspan="6">Chưa có bài kiến thức nào.</td></tr>@endforelse</tbody></table></div></div>
{{ $articles->links('pagination::bootstrap-4') }}
@endsection
