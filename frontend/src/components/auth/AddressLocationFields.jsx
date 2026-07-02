"use client";

import { useCallback, useEffect, useId, useRef, useState } from "react";
import { DEFAULT_ADDRESS_VERSION, getDistricts, getProvinces, getWards } from "@/lib/addressApi";

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

export default function AddressLocationFields({ value, onChange }) {
  const initialValue = useRef(value).current;
  const hydrateInitialValue = useRef(true);
  const [version, setVersion] = useState(DEFAULT_ADDRESS_VERSION);
  const [provinces, setProvinces] = useState([]);
  const [districts, setDistricts] = useState([]);
  const [wards, setWards] = useState([]);
  const [provinceCode, setProvinceCode] = useState("");
  const [districtCode, setDistrictCode] = useState("");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const loadProvinces = useCallback(() => {
    setLoading(true); setError("");
    getProvinces(version)
      .then((items) => {
        setProvinces(items);
        const selected = hydrateInitialValue.current ? items.find((item) => item.name === initialValue.province) : null;
        hydrateInitialValue.current = false;
        setProvinceCode(selected ? String(selected.code) : "");
        if (!selected) { setDistricts([]); setWards([]); return; }
        if (version === "v2") {
          return getWards(selected.code, "v2").then((wardItems) => setWards(wardItems));
        }
        return getDistricts(selected.code).then((districtItems) => {
          setDistricts(districtItems);
          const selectedDistrict = districtItems.find((item) => item.name === initialValue.district);
          setDistrictCode(selectedDistrict ? String(selectedDistrict.code) : "");
          if (selectedDistrict) return getWards(selectedDistrict.code, "v1").then((wardItems) => setWards(wardItems));
        });
      })
      .catch(() => setError("Không tải được dữ liệu tỉnh thành. Vui lòng thử lại."))
      .finally(() => setLoading(false));
  }, [initialValue.district, initialValue.province, version]);

  useEffect(() => { Promise.resolve().then(loadProvinces); }, [loadProvinces]);

  const changeVersion = (nextVersion) => {
    hydrateInitialValue.current = false;
    setVersion(nextVersion); setProvinceCode(""); setDistrictCode(""); setProvinces([]); setDistricts([]); setWards([]);
    onChange({ ...value, province: "", district: nextVersion === "v2" ? "Không áp dụng" : "", ward: "" });
  };

  const changeProvince = async (event) => {
    const code = event.target.value;
    const selected = provinces.find((item) => String(item.code) === code);
    setProvinceCode(code); setDistrictCode(""); setDistricts([]); setWards([]);
    onChange({ ...value, province: selected?.name || "", district: version === "v2" ? "Không áp dụng" : "", ward: "" });
    if (!code) return;
    try {
      setLoading(true);
      if (version === "v2") setWards(await getWards(code, "v2"));
      else setDistricts(await getDistricts(code));
    } catch { setError("Không tải được đơn vị hành chính trực thuộc."); }
    finally { setLoading(false); }
  };

  const changeDistrict = async (event) => {
    const code = event.target.value;
    const selected = districts.find((item) => String(item.code) === code);
    setDistrictCode(code); setWards([]);
    onChange({ ...value, district: selected?.name || "", ward: "" });
    if (!code) return;
    try { setLoading(true); setWards(await getWards(code, "v1")); }
    catch { setError("Không tải được danh sách phường xã."); }
    finally { setLoading(false); }
  };

  const changeWard = (event) => onChange({ ...value, ward: event.target.value });

  return <>
    <div className="address-version-switch wide" role="group" aria-label="Phiên bản địa giới hành chính"><button type="button" className={version === "v2" ? "active" : ""} onClick={() => changeVersion("v2")}>Sau sáp nhập</button><button type="button" className={version === "v1" ? "active" : ""} onClick={() => changeVersion("v1")}>Trước sáp nhập</button></div>
    {error && <div className="address-api-error wide"><span>{error}</span><button type="button" onClick={loadProvinces}>Thử lại</button></div>}
    <div className={`address-select-field ${version === "v2" ? "" : "wide"}`}><span>Tỉnh/Thành phố</span><AddressDropdown value={provinceCode} options={provinces.map((item) => ({ value: item.code, label: item.name }))} placeholder="Chọn Tỉnh/Thành phố" loadingLabel="Đang tải..." disabled={loading && provinces.length === 0} onChange={(selectedValue) => changeProvince({ target: { value: selectedValue } })} /></div>
    {version === "v1" && <div className="address-select-field"><span>Quận/Huyện</span><AddressDropdown value={districtCode} options={districts.map((item) => ({ value: item.code, label: item.name }))} placeholder="Chọn Quận/Huyện" loadingLabel="Đang tải..." disabled={!provinceCode || loading} onChange={(selectedValue) => changeDistrict({ target: { value: selectedValue } })} /></div>}
    <div className="address-select-field"><span>Phường/Xã</span><AddressDropdown value={value.ward || ""} options={wards.map((item) => ({ value: item.name, label: item.name }))} placeholder="Chọn Phường/Xã" loadingLabel="Đang tải..." disabled={!provinceCode || (version === "v1" && !districtCode) || loading} onChange={(selectedValue) => changeWard({ target: { value: selectedValue } })} /></div>
    <p className="address-version-note wide">{version === "v2" ? "Dữ liệu địa giới 2 cấp áp dụng sau tháng 7/2025." : "Dữ liệu địa giới 3 cấp trước tháng 7/2025."}</p>
  </>;
}
