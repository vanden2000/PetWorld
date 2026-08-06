import { API_BASE_URL } from "@/lib/api";
import { authHeaders } from "@/lib/auth";

async function request(path) {
  const response = await fetch(`${API_BASE_URL}/api/shipping/ghn/${path}`, { headers: { Accept: "application/json", ...authHeaders() } });
  if (!response.ok) throw new Error(`Province API returned ${response.status}`);
  return response.json();
}

export async function getProvinces() {
  return (await request("provinces"))?.data?.provinces ?? [];
}

export async function getDistricts(provinceId) {
  if (!provinceId) return [];
  return (await request(`districts?province_id=${provinceId}`))?.data?.districts ?? [];
}

export async function getWards(districtId) {
  if (!districtId) return [];
  return (await request(`wards?district_id=${districtId}`))?.data?.wards ?? [];
}
