import { notFound } from "next/navigation";
import { getKnowledgeDetail } from "@/lib/api";
import KnowledgeArticleView from "@/components/knowledge/KnowledgeArticleView";

const SLUG = "dieu-khoan-su-dung";

export async function generateMetadata() {
  const data = await getKnowledgeDetail(SLUG);
  const article = data?.article;
  if (!article) return { title: "Trang không tồn tại | PetWorld" };
  return {
    title: `${article.title} - PetWorld`,
    description: article.summary || "Điều khoản sử dụng PetWorld.",
  };
}

export default async function TermsPage() {
  const data = await getKnowledgeDetail(SLUG);
  if (!data?.article) {
    notFound();
  }
  return <KnowledgeArticleView article={data.article} related={data.related ?? []} />;
}