import { redirect } from "next/navigation";

export default function LegacyWishlistPage() {
  redirect("/account/wishlist");
}
