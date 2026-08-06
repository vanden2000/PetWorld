"use client";

import { useEffect, useRef, useState } from "react";
import { getDistricts, getProvinces, getWards } from "@/lib/addressApi";

function normalize(text) {
  return String(text || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
}

function LocationSelect({ label, selected, options, disabled, onSelect, placeholder }) {
  const rootRef = useRef(null);
  const closeTimerRef = useRef(null);
  const [open, setOpen] = useState(false);
  const [closing, setClosing] = useState(false);
  const [query, setQuery] = useState("");
  const selectedItem = options.find((item) => String(item.id ?? item.code) === String(selected));
  const visibleOptions = options.filter((item) => normalize(item.name).includes(normalize(query)));

  const closeMenu = () => {
    if (!open) return;
    setOpen(false); setClosing(true);
    window.clearTimeout(closeTimerRef.current);
    closeTimerRef.current = window.setTimeout(() => setClosing(false), 180);
  };

  useEffect(() => {
    const close = (event) => { if (!rootRef.current?.contains(event.target)) closeMenu(); };
    document.addEventListener("pointerdown", close);
    return () => { document.removeEventListener("pointerdown", close); window.clearTimeout(closeTimerRef.current); };
  }, []);

  return <label className="address-select-field"><span>{label}</span><div className={`address-dropdown address-search-dropdown ${open ? "open" : ""} ${closing ? "closing" : ""}`} ref={rootRef}>
    <input value={open ? query : selectedItem?.name || ""} disabled={disabled} placeholder={disabled ? placeholder : `Tìm hoặc chọn ${label.toLowerCase()}...`} onFocus={() => { setQuery(""); setOpen(true); }} onChange={(event) => { setQuery(event.target.value); setOpen(true); }} onKeyDown={(event) => event.key === "Escape" && setOpen(false)} />
    <button type="button" className="address-dropdown-trigger" aria-label={`Mở danh sách ${label}`} disabled={disabled} onClick={() => { setQuery(""); setOpen((current) => !current); }}><i /></button>
    {open && <div className="address-dropdown-menu" role="listbox">{visibleOptions.length ? visibleOptions.map((item) => { const id = item.id ?? item.code; return <button type="button" role="option" aria-selected={String(id) === String(selected)} className={String(id) === String(selected) ? "selected" : ""} key={id} onMouseDown={(event) => event.preventDefault()} onClick={() => { onSelect(String(id)); setQuery(""); setOpen(false); }}>{item.name}</button>; }) : <div className="address-dropdown-empty">Không tìm thấy kết quả phù hợp.</div>}</div>}
  </div></label>;
}

export default function AddressLocationFields({ value, onChange }) {
  const [provinces, setProvinces] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [wards, setWards] = useState([]);
  const [provinceId, setProvinceId] = useState("");
  const [districtId, setDistrictId] = useState(value.ghn_district_id ? String(value.ghn_district_id) : "");
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getProvinces().then((items) => {
      setProvinces(items);
      const province = items.find((item) => item.name === value.province);
      if (!province) return;
      setProvinceId(String(province.id));
      return getDistricts(province.id).then((list) => { setDistricts(list); return value.ghn_district_id ? getWards(value.ghn_district_id).then(setWards) : null; });
    }).finally(() => setLoading(false));
  }, []);

  const changeProvince = async (id) => {
    const selected = provinces.find((item) => String(item.id) === id);
    setProvinceId(id); setDistrictId(""); setDistricts([]); setWards([]);
    onChange({ ...value, province: selected?.name || "", district: "", ward: "", ghn_district_id: "", ghn_ward_code: "" });
    if (id) { setLoading(true); try { setDistricts(await getDistricts(id)); } finally { setLoading(false); } }
  };
  const changeDistrict = async (id) => {
    const selected = districts.find((item) => String(item.id) === id);
    setDistrictId(id); setWards([]);
    onChange({ ...value, district: selected?.name || "", ward: "", ghn_district_id: selected?.id || "", ghn_ward_code: "" });
    if (id) { setLoading(true); try { setWards(await getWards(id)); } finally { setLoading(false); } }
  };
  const changeWard = (code) => { const selected = wards.find((item) => String(item.code) === code); onChange({ ...value, ward: selected?.name || "", ghn_ward_code: selected?.code || "" }); };
  return <>
    <LocationSelect label="Tỉnh/Thành phố" selected={provinceId} options={provinces} disabled={loading && !provinces.length} onSelect={changeProvince} placeholder="Chọn Tỉnh/Thành phố" />
    <LocationSelect label="Quận/Huyện" selected={districtId} options={districts} disabled={!provinceId || loading} onSelect={changeDistrict} placeholder="Chọn Quận/Huyện" />
    <LocationSelect label="Phường/Xã" selected={value.ghn_ward_code || ""} options={wards} disabled={!districtId || loading} onSelect={changeWard} placeholder="Chọn Phường/Xã" />
  </>;
}
