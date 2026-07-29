<style>
  .species-page { max-width: 1180px; margin: 0 auto; }
  .species-header { display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:26px; }
  .species-kicker { display:flex; align-items:center; gap:8px; color:var(--primary); font-size:.78rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
  .species-header h1 { margin:8px 0 6px; color:var(--text-main); font-size:1.8rem; letter-spacing:-.03em; }
  .species-header p { margin:0; color:var(--text-muted); max-width:620px; line-height:1.55; }
  .species-add, .species-save { display:inline-flex; align-items:center; justify-content:center; gap:9px; border:0; border-radius:10px; padding:11px 16px; color:#fff; background:var(--primary); font-weight:700; text-decoration:none; cursor:pointer; transition:transform .18s ease, background .18s ease; white-space:nowrap; }
  .species-add:hover, .species-save:hover { color:#fff; background:var(--primary-hover); transform:translateY(-1px); }
  .species-metrics { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:14px; margin-bottom:20px; }
  .species-metric {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px 24px;
      border: 1px solid var(--border-color);
      border-radius: 16px;
      background: var(--surface-color);
      box-shadow: var(--shadow-subtle);
      position: relative;
      overflow: hidden;
      transition: all 0.25s ease;
  }
  .species-metric::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      bottom: 0;
      width: 4px;
      background: var(--primary);
      border-radius: 4px 0 0 4px;
  }
  .species-metric:nth-child(2)::before {
      background: #16734a;
  }
  .species-metric:nth-child(3)::before {
      background: #a34b13;
  }
  .species-metric:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-medium);
  }
  .species-metric-info {
      display: flex;
      flex-direction: column;
  }
  .species-metric span {
      display: block;
      color: var(--text-muted);
      font-size: .8rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
  }
  .species-metric strong {
      display: block;
      color: var(--text-main);
      font-size: 1.6rem;
      line-height: 1.15;
      margin-top: 6px;
      font-weight: 800;
  }
  .species-metric-icon {
      font-size: 1.8rem;
      color: var(--primary);
      opacity: 0.15;
      transition: all 0.2s ease;
  }
  .species-metric:hover .species-metric-icon {
      opacity: 0.35;
      transform: scale(1.1);
  }
  /* Table styles */
  .species-table-card {
      background: var(--surface-color);
      border: 1px solid var(--border-color);
      border-radius: 14px;
      overflow: hidden;
      box-shadow: var(--shadow-subtle);
  }
  .table-container {
      width: 100%;
      overflow-x: auto;
  }
  .species-table {
      width: 100%;
      border-collapse: collapse;
  }
  .species-table th {
      padding: 16px 20px;
      color: var(--text-muted);
      background: #fafbfc;
      text-align: left;
      font-size: .72rem;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      border-bottom: 1px solid var(--border-color);
  }
  .species-table td {
      padding: 16px 20px;
      border-bottom: 1px solid var(--border-color);
      color: var(--text-main);
      vertical-align: middle;
  }
  .species-table tr:last-child td {
      border-bottom: 0;
  }
  .species-table tr:hover td {
      background: #fffdfb;
  }

  /* Species identity column */
  .species-identity {
      display: flex;
      align-items: center;
      gap: 12px;
  }
  .species-avatar {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: grid;
      place-items: center;
      overflow: hidden;
      color: #7a4f35;
      font-size: 1.1rem;
      flex: 0 0 auto;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
  }
  .species-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
  }
  .species-name-text {
      font-weight: 700;
      color: var(--text-main);
      font-size: 0.92rem;
  }

  /* Slug style */
  .species-slug-text {
      color: var(--text-muted);
      font: 600 .78rem ui-monospace, SFMono-Regular, Menlo, monospace;
      background: var(--bg-color);
      padding: 4px 8px;
      border-radius: 6px;
      border: 1px solid var(--border-color);
  }

  /* Products count and order badges */
  .species-products-count {
      font-weight: 700;
      color: var(--text-main);
      font-size: 0.9rem;
      background: #fff5ee;
      color: var(--primary);
      padding: 3px 8px;
      border-radius: 999px;
  }

  .species-sort-order {
      font-weight: 700;
      color: var(--text-muted);
      font-size: 0.88rem;
  }

  /* Badges */
  .species-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border-radius: 999px;
      padding: 6px 10px;
      font-size: .76rem;
      font-weight: 700;
  }
  .species-badge--home { color:#a34b13; background:#fff0e6; }
  .species-badge--active { color:#16734a; background:#e9f8ef; }
  .species-badge--hidden { color:#667085; background:#f1f3f5; }

  /* Table action button */
  .species-table-action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      color: #e35e14;
      background-color: #fff3ec;
      border: 1px solid #ffd8c0;
      cursor: pointer;
      transition: all 0.2s ease;
      text-decoration: none;
  }
  .species-table-action-btn:hover {
      background-color: var(--primary);
      color: #ffffff;
      border-color: var(--primary);
      box-shadow: 0 4px 10px rgba(255, 120, 45, 0.15);
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
  /* Premium Upload Area */
  .species-upload-dropzone {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 16px 20px;
      border: 1px dashed #e4c6b2;
      border-radius: 12px;
      background: #fffaf6;
      cursor: pointer;
      transition: all 0.2s ease;
  }
  .species-upload-dropzone:hover {
      border-color: var(--primary);
      background: #fffcf9;
  }
  .species-upload-preview {
      width: 56px;
      height: 56px;
      overflow: hidden;
      display: grid;
      place-items: center;
      border-radius: 12px;
      color: #9a6849;
      background: #f6e7db;
      flex: 0 0 auto;
      font-size: 1.35rem;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.03);
      transition: all 0.2s ease;
  }
  .species-upload-preview img {
      width: 100%;
      height: 100%;
      object-fit: cover;
  }
  .species-upload-text {
      display: flex;
      flex-direction: column;
      line-height: 1.4;
      text-align: left;
  }
  .species-upload-text strong {
      font-size: 0.88rem;
      color: var(--text-main);
      font-weight: 700;
  }
  .species-upload-text span {
      font-size: 0.76rem;
      color: var(--text-muted);
  }

  /* Premium Color Input Block */
  .species-color-wrapper {
      display: flex;
      align-items: center;
      gap: 12px;
  }
  .species-color-bubble {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      border: 2px solid #ffffff;
      box-shadow: 0 0 0 1px var(--border-color), 0 4px 8px rgba(0,0,0,0.06);
      cursor: pointer;
      transition: all 0.2s ease;
      flex: 0 0 auto;
  }
  .species-color-bubble:hover {
      transform: scale(1.08);
      box-shadow: 0 0 0 1px var(--primary), 0 6px 12px rgba(0,0,0,0.1);
  }
  .color-hex-input {
      font-family: ui-monospace, SFMono-Regular, Menlo, monospace !important;
      font-weight: 600 !important;
      color: var(--text-main) !important;
      text-align: center;
  }

  .species-help {
      margin: 7px 0 0;
      color: var(--text-muted);
      font-size: .78rem;
      line-height: 1.45;
  }
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