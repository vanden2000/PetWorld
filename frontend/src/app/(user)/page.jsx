import { getHomeData } from "@/lib/api";
import HomeView from "@/components/home/HomeView";

export default async function Homepage() {
  const data = await getHomeData();

  return <HomeView initialData={data} />;
}
