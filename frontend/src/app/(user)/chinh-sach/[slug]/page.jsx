import { notFound } from "next/navigation";
import { getKnowledgeDetail } from "@/lib/api";
import KnowledgeArticleView from "@/components/knowledge/KnowledgeArticleView";

const SITE_URL = (process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000").replace(/\/$/, "");

function absoluteUrl(path = "/") {
  return new URL(path, `${SITE_URL}/`).toString();
}

export async function generateMetadata({ params }) {
  const { slug } = await params;
  const data = await getKnowledgeDetail(slug);
  const article = data?.article;

  if (!article) return { title: "Trang không tồn tại | PetWorld" };

  const title = `${article.title} - PetWorld`;
  const description = article.summary || "Chính sách & hướng dẫn từ PetWorld.";
  const canonical = absoluteUrl(article.url || `/chinh-sach/${article.slug}`);

  return {
    title,
    description,
    alternates: { canonical },
    openGraph: {
      type: "article",
      url: canonical,
      title,
      description,
    },
  };
}

export default async function KnowledgeDetailPage({ params }) {
  const { slug } = await params;
  const data = await getKnowledgeDetail(slug);

  if (!data?.article) {
    notFound();
  }

  return <KnowledgeArticleView article={data.article} related={data.related ?? []} />;
}