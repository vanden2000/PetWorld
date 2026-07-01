const API_ROOT = "https://provinces.open-api.vn/api";

async function request(path) {
  const response = await fetch(`${API_ROOT}${path}`, { headers: { Accept: "application/json" } });
  if (!response.ok) throw new Error(`Province API returned ${response.status}`);
  return response.json();
}

export const DEFAULT_ADDRESS_VERSION = process.env.NEXT_PUBLIC_ADDRESS_API_VERSION === "v1" ? "v1" : "v2";

export function getProvinces(version = DEFAULT_ADDRESS_VERSION) {
  return request(`/${version}/p/`);
}

export async function getDistricts(provinceCode) {
  if (!provinceCode) return [];
  const province = await request(`/v1/p/${provinceCode}?depth=2`);
  return province.districts || [];
}

export async function getWards(parentCode, version = DEFAULT_ADDRESS_VERSION) {
  if (!parentCode) return [];
  if (version === "v2") return request(`/v2/w/?province=${parentCode}`);
  const district = await request(`/v1/d/${parentCode}?depth=2`);
  return district.wards || [];
}
