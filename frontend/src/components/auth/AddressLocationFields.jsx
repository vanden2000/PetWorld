"use client";

import { useCallback, useEffect, useId, useRef, useState } from "react";
import { getProvinces, getWards } from "@/lib/addressApi";

function AddressDropdown({ value, options, placeholder, loadingLabel, disabled, onChange }) {
  const [open, setOpen] = useState(false);
  const rootRef = useRef(null);
  const listId = useId();
  const selected = options.find((item) => String(item.value) === String(value));

  useEffect(() => {
    if (!open) return;
    const closeOutside = (event) => {
      if (!rootRef.current?.contains(event.target)) setOpen(false);
    };
    document.addEventListener("pointerdown", closeOutside);
    return () => document.removeEventListener("pointerdown", closeOutside);
  }, [open]);

  return <div className={`address-dropdown ${open ? "open" : ""}`} ref={rootRef}>
    <button type="button" className={value ? "has-value" : ""} disabled={disabled} aria-haspopup="listbox" aria-expanded={open} aria-controls={listId} onClick={() => setOpen((current) => !current)} onKeyDown={(event) => event.key === "Escape" && setOpen(false)}>
      <span>{selected?.label || (disabled && loadingLabel ? loadingLabel : placeholder)}</span><i aria-hidden="true" />
    </button>
    {open && <div className="address-dropdown-menu" id={listId} role="listbox" tabIndex="-1">{options.length === 0 ? <div className="address-dropdown-empty">Không có dữ liệu</div> : options.map((item) => <button type="button" role="option" aria-selected={String(item.value) === String(value)} className={String(item.value) === String(value) ? "selected" : ""} key={item.value} onClick={() => { onChange(String(item.value)); setOpen(false); }}>{item.label}</button>)}</div>}
  </div>;
}

// Địa chỉ 2 cấp: Tỉnh/Thành phố + Phường/Xã (theo địa giới sau sáp nhập, bỏ cấp Quận/Huyện).
export default function AddressLocationFields({ value, onChange }) {
  const initialValue = useRef(value).current;
  const hydrateInitialValue = useRef(true);
  const [provinces, setProvinces] = useState([]);
  const [wards, setWards] = useState([]);
  const [provinceCode, setProvinceCode] = useState("");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const loadProvinces = useCallback(() => {
    setLoading(true); setError("");
    getProvinces("v2")
      .then((items) => {
        setProvinces(items);
        const selected = hydrateInitialValue.current ? items.find((item) => item.name === initialValue.province) : null;
        hydrateInitialValue.current = false;
        setProvinceCode(selected ? String(selected.code) : "");
        if (!selected) { setWards([]); return; }
        return getWards(selected.code, "v2").then((wardItems) => setWards(wardItems));
      })
      .catch(() => setError("Không tải được dữ liệu tỉnh thành. Vui lòng thử lại."))
      .finally(() => setLoading(false));
  }, [initialValue.province]);

  useEffect(() => { Promise.resolve().then(loadProvinces); }, [loadProvinces]);

  const changeProvince = async (event) => {
    const code = event.target.value;
    const selected = provinces.find((item) => String(item.code) === code);
    setProvinceCode(code); setWards([]);
    onChange({ ...value, province: selected?.name || "", district: "", ward: "" });
    if (!code) return;
    try {
      setLoading(true);
      setWards(await getWards(code, "v2"));
    } catch { setError("Không tải được danh sách phường xã."); }
    finally { setLoading(false); }
  };

  const changeWard = (event) => onChange({ ...value, ward: event.target.value });

  return <>
    {error && <div className="address-api-error wide"><span>{error}</span><button type="button" onClick={loadProvinces}>Thử lại</button></div>}
    <div className="address-select-field"><span>Tỉnh/Thành phố</span><AddressDropdown value={provinceCode} options={provinces.map((item) => ({ value: item.code, label: item.name }))} placeholder="Chọn Tỉnh/Thành phố" loadingLabel="Đang tải..." disabled={loading && provinces.length === 0} onChange={(selectedValue) => changeProvince({ target: { value: selectedValue } })} /></div>
    <div className="address-select-field"><span>Phường/Xã</span><AddressDropdown value={value.ward || ""} options={wards.map((item) => ({ value: item.name, label: item.name }))} placeholder="Chọn Phường/Xã" loadingLabel="Đang tải..." disabled={!provinceCode || loading} onChange={(selectedValue) => changeWard({ target: { value: selectedValue } })} /></div>
  </>;
}
