<style>
  .species-page { max-width: 1180px; margin: 0 auto; }
  .species-header { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:26px; }
  .species-kicker { display:flex; align-items:center; gap:8px; color:var(--primary); font-size:.78rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
  .species-header h1 { margin:8px 0 6px; color:var(--text-main); font-size:1.8rem; letter-spacing:-.03em; }
  .species-header p { margin:0; color:var(--text-muted); max-width:620px; line-height:1.55; }
  .species-add, .species-save { display:inline-flex; align-items:center; justify-content:center; gap:9px; border:0; border-radius:10px; padding:11px 16px; color:#fff; background:var(--primary); font-weight:700; text-decoration:none; cursor:pointer; transition:transform .18s ease, background .18s ease; white-space:nowrap; }
  .species-add:hover, .species-save:hover { color:#fff; background:var(--primary-hover); transform:translateY(-1px); }
  .species-metrics { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:14px; margin-bottom:20px; }
  .species-metric { padding:17px 18px; border:1px solid var(--border-color); border-radius:13px; background:var(--surface-color); }
  .species-metric span { display:block; color:var(--text-muted); font-size:.8rem; font-weight:600; }
  .species-metric strong { display:block; color:var(--text-main); font-size:1.55rem; line-height:1.15; margin-top:6px; }
  .species-table-card { background:var(--surface-color); border:1px solid var(--border-color); border-radius:14px; overflow:hidden; }
  .species-table { width:100%; border-collapse:collapse; }
  .species-table th { padding:14px 18px; color:var(--text-muted); background:#fafbfc; text-align:left; font-size:.72rem; letter-spacing:.06em; text-transform:uppercase; }
  .species-table td { padding:14px 18px; border-top:1px solid var(--border-color); color:var(--text-main); vertical-align:middle; }
  .species-table tr:hover td { background:#fffdfb; }

  /* Alignments and column widths for Species Table */
  .species-table th:nth-child(1), .species-table td:nth-child(1) {
      text-align: left;
  }
  .species-table th:nth-child(2), .species-table td:nth-child(2) {
      text-align: left;
      width: 140px;
  }
  .species-table th:nth-child(3), .species-table td:nth-child(3) {
      text-align: center;
      width: 120px;
  }
  .species-table th:nth-child(4), .species-table td:nth-child(4) {
      text-align: center;
      width: 240px;
  }
  .species-table th:nth-child(5), .species-table td:nth-child(5) {
      text-align: center;
      width: 100px;
  }
  .species-table th:nth-child(6), .species-table td:nth-child(6) {
      text-align: right;
      width: 120px;
      padding-right: 24px;
  }
  .species-identity { display:flex; align-items:center; gap:12px; font-weight:700; }
  .species-avatar { width:42px; height:42px; border-radius:12px; display:grid; place-items:center; overflow:hidden; color:#7a4f35; flex:0 0 auto; }
  .species-avatar img { width:100%; height:100%; object-fit:cover; }
  .species-slug { color:var(--text-muted); font:600 .78rem ui-monospace, SFMono-Regular, Menlo, monospace; }
  .species-badge { display:inline-flex; align-items:center; gap:6px; border-radius:999px; padding:6px 9px; font-size:.76rem; font-weight:700; }
  .species-badge--home { color:#a34b13; background:#fff0e6; }
  .species-badge--active { color:#16734a; background:#e9f8ef; }
  .species-badge--hidden { color:#667085; background:#f1f3f5; }

  /* Card body */
  .species-card-body {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      margin-bottom: 24px;
  }

  .species-card-avatar {
      width: 68px;
      height: 68px;
      border-radius: 18px;
      display: grid;
      place-items: center;
      font-size: 1.8rem;
      color: #7a4f35;
      margin-bottom: 16px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
      transition: transform 0.2s ease;
  }

  .species-card:hover .species-card-avatar {
      transform: scale(1.05);
  }

  .species-card-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
  }

  .species-card-name {
      font-size: 1.2rem;
      font-weight: 800;
      color: var(--text-main);
      margin: 0 0 6px 0;
  }

  .species-card-slug {
      font-size: 0.8rem;
      color: var(--text-muted);
      font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
      font-weight: 600;
  }

  /* Card stats */
  .species-card-stats {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      background: var(--bg-color);
      border-radius: 12px;
      padding: 12px;
      margin-bottom: 24px;
      border: 1px solid var(--border-color);
  }

  .species-stat-item {
      display: flex;
      align-items: center;
      gap: 10px;
  }

  .species-stat-item i {
      font-size: 1.1rem;
      color: var(--primary);
      background: rgba(255, 120, 45, 0.08);
      width: 32px;
      height: 32px;
      border-radius: 8px;
      display: grid;
      place-items: center;
  }

  .species-stat-item div {
      display: flex;
      flex-direction: column;
      line-height: 1.25;
  }

  .species-stat-item strong {
      font-size: 0.9rem;
      font-weight: 800;
      color: var(--text-main);
  }

  .species-stat-item span {
      font-size: 0.72rem;
      color: var(--text-muted);
      font-weight: 600;
  }

  /* Card actions */
  .species-card-actions {
      margin-top: auto;
  }

  .species-card-btn-edit {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      padding: 10px 16px;
      background: #fff7f2;
      border: 1px solid #ffd9c1;
      border-radius: 10px;
      color: var(--primary);
      font-weight: 700;
      font-size: 0.88rem;
      text-decoration: none;
      transition: all 0.2s ease;
  }

  .species-card-btn-edit:hover {
      background: var(--primary);
      color: #ffffff;
      border-color: var(--primary);
      box-shadow: 0 4px 12px rgba(255, 120, 45, 0.16);
  }

  .species-card-empty {
      grid-column: 1 / -1;
      background: var(--surface-color);
      border: 1px dashed var(--border-color);
      border-radius: 16px;
      padding: 48px;
      text-align: center;
      color: var(--text-muted);
  }

  .species-card-empty i {
      font-size: 3rem;
      color: var(--border-color);
      margin-bottom: 16px;
  }
  .species-form { display:grid; grid-template-columns:minmax(0, 1fr) 310px; gap:20px; }
  .species-panel { padding:24px; background:var(--surface-color); border:1px solid var(--border-color); border-radius:14px; }
  .species-panel-title { display:flex; align-items:center; gap:9px; margin:0 0 20px; color:var(--text-main); font-size:1rem; }
  .species-panel-title i { color:var(--primary); }
  .species-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
  .species-field { margin-bottom:17px; }
  .species-field:last-child { margin-bottom:0; }
  .species-field label { display:block; margin-bottom:7px; color:var(--text-main); font-size:.86rem; font-weight:700; }
  .species-field input[type="text"], .species-field input[type="number"] { width:100%; min-height:42px; box-sizing:border-box; border:1px solid var(--border-color); border-radius:9px; padding:10px 11px; color:var(--text-main); background:#fff; }
  .species-field input:focus { outline:0; border-color:var(--primary); box-shadow:0 0 0 3px rgba(255,120,45,.12); }
  .species-upload { display:flex; align-items:center; gap:13px; padding:12px; border:1px dashed #e4c6b2; border-radius:11px; background:#fffaf6; }
  .species-upload-preview { width:54px; height:54px; overflow:hidden; display:grid; place-items:center; border-radius:10px; color:#9a6849; background:#f6e7db; flex:0 0 auto; }
  .species-upload-preview img { width:100%; height:100%; object-fit:cover; }
  .species-file { width:100%; color:var(--text-muted); font-size:.82rem; }
  .species-color-row { display:flex; align-items:center; gap:10px; }
  .species-color { width:46px; height:40px; padding:2px; border:1px solid var(--border-color); border-radius:9px; cursor:pointer; }
  .species-help { margin:7px 0 0; color:var(--text-muted); font-size:.78rem; line-height:1.45; }
  .species-switch { display:flex; gap:11px; padding:13px 0; border-top:1px solid var(--border-color); cursor:pointer; }
  .species-switch:first-of-type { border-top:0; padding-top:0; }
  .species-switch input { margin-top:3px; accent-color:var(--primary); }
  .species-switch strong, .species-switch span { display:block; }
  .species-switch strong { color:var(--text-main); font-size:.88rem; }
  .species-switch span { color:var(--text-muted); font-size:.78rem; margin-top:3px; line-height:1.4; }
  .species-form-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
  .species-cancel { display:inline-flex; align-items:center; justify-content:center; padding:11px 16px; border:1px solid var(--border-color); border-radius:10px; color:var(--text-main); background:var(--surface-color); font-weight:700; text-decoration:none; }
  .species-error { margin:8px 0 0; color:#c9362a; font-size:.82rem; }
  @media (max-width: 820px) { .species-header { align-items:stretch; flex-direction:column; } .species-add { align-self:flex-start; } .species-form { grid-template-columns:1fr; } }
  @media (max-width: 640px) { .species-page { margin:0; } .species-metrics, .species-form-grid { grid-template-columns:1fr; } .species-table-card { overflow-x:auto; } .species-table { min-width:720px; } .species-panel { padding:18px; } }
</style>