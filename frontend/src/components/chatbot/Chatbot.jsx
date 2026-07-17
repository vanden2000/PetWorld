"use client";

import Link from "next/link";
import { useEffect, useRef, useState } from "react";
import { API_BASE_URL } from "@/lib/api";
import { formatPrice, resolveProductImage, useImageFallback } from "@/lib/format";
import AddToCartButton from "@/components/product/AddToCartButton";

const QUICK_QUESTIONS = [
  "Tìm thức ăn cho mèo",
  "Tìm đồ chơi cho chó",
  "Theo dõi đơn hàng của tôi",
];
const VISITOR_ID_KEY = "petworld_chat_visitor_id";
const CONVERSATION_ID_KEY = "petworld_chat_conversation_id";

function getVisitorId() {
  const existingId = localStorage.getItem(VISITOR_ID_KEY);
  if (existingId) return existingId;

  const visitorId = crypto.randomUUID();
  localStorage.setItem(VISITOR_ID_KEY, visitorId);
  return visitorId;
}

function formatSuggestionPrice(price = {}) {
  if (price.min === price.max || price.max === undefined || price.max === null) {
    return formatPrice(price.min);
  }

  return `${formatPrice(price.min)} – ${formatPrice(price.max)}`;
}

function cleanChatText(text) {
  // AI đôi khi trả Markdown nhưng hộp chat này hiển thị văn bản thuần.
  return String(text || "")
    .replace(/\*{1,3}/g, "")
    .replace(/_{2}/g, "")
    .replace(/^#{1,6}\s*/gm, "")
    .trim();
}

export default function Chatbot() {
  const [isOpen, setIsOpen] = useState(false);
  const [message, setMessage] = useState("");
  const [isSending, setIsSending] = useState(false);
  const [comparisonSelection, setComparisonSelection] = useState([]);
  const [messages, setMessages] = useState([
    {
      id: "welcome",
      sender: "bot",
      text: "Xin chào! PetWorld có thể giúp gì cho bạn hôm nay?",
    },
  ]);
  const inputRef = useRef(null);
  const messagesEndRef = useRef(null);

  useEffect(() => {
    if (isOpen) inputRef.current?.focus();

    const handleEscape = (event) => {
      if (event.key === "Escape") setIsOpen(false);
    };

    document.addEventListener("keydown", handleEscape);
    return () => document.removeEventListener("keydown", handleEscape);
  }, [isOpen]);

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth", block: "end" });
  }, [messages, isSending]);

  const sendMessage = async (text) => {
    const content = text.trim();
    if (!content || isSending) return;

    const userMessage = { id: `user-${Date.now()}`, sender: "user", text: content };
    setMessages((current) => [...current, userMessage]);
    setMessage("");
    setIsSending(true);

    try {
      const token = localStorage.getItem("petworld_token");
      const conversationId = localStorage.getItem(CONVERSATION_ID_KEY);
      const response = await fetch(`${API_BASE_URL}/api/chat`, {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
        body: JSON.stringify({
          message: content,
          visitor_id: getVisitorId(),
          ...(conversationId ? { conversation_id: conversationId } : {}),
        }),
      });
      const payload = await response.json().catch(() => ({}));

      if (!response.ok) {
        throw new Error(payload.message || "PetWorld chưa thể phản hồi. Vui lòng thử lại sau.");
      }

      const data = payload.data;
      if (!data?.message || !data?.conversation_id) {
        throw new Error("PetWorld chưa nhận được phản hồi phù hợp. Vui lòng thử lại sau.");
      }

      localStorage.setItem(CONVERSATION_ID_KEY, data.conversation_id);
      setMessages((current) => [
        ...current,
        {
          id: `bot-${Date.now()}`,
          sender: "bot",
          text: data.message,
          suggestions: Array.isArray(data.suggestions) ? data.suggestions : [],
          orders: Array.isArray(data.orders) ? data.orders : [],
          sources: Array.isArray(data.sources) ? data.sources : [],
        },
      ]);
    } catch (error) {
      setMessages((current) => [
        ...current,
        {
          id: `error-${Date.now()}`,
          sender: "bot error",
          text: error instanceof Error ? error.message : "Có lỗi xảy ra. Vui lòng thử lại sau.",
        },
      ]);
    } finally {
      setIsSending(false);
    }
  };

  const handleSubmit = (event) => {
    event.preventDefault();
    sendMessage(message);
  };

  const selectForComparison = (product) => {
    if (isSending) return;

    if (comparisonSelection.some((selected) => selected.id === product.id)) {
      setComparisonSelection((current) => current.filter((selected) => selected.id !== product.id));
      return;
    }

    if (comparisonSelection.length === 1) {
      const [first] = comparisonSelection;
      setComparisonSelection([]);
      sendMessage(`So sánh ${first.name} và ${product.name}`);
      return;
    }

    setComparisonSelection([product]);
  };

  return (
    <div className={`chatbot ${isOpen ? "open" : ""}`}>
      <section
        id="petworld-chatbot-panel"
        className="chatbot-panel"
        role="dialog"
        aria-modal="false"
        aria-label="Hỗ trợ trực tuyến PetWorld"
        aria-hidden={!isOpen}
      >
        <header className="chatbot-header">
          <div className="chatbot-agent">
            <span className="chatbot-agent-mark" aria-hidden="true">P</span>
            <div>
              <strong>PetWorld hỗ trợ</strong>
              <span><i aria-hidden="true" /> Đang trực tuyến</span>
            </div>
          </div>
          <button type="button" className="chatbot-close" onClick={() => setIsOpen(false)} aria-label="Đóng hộp chat">
            <span aria-hidden="true" />
          </button>
        </header>

        <div className="chatbot-messages" aria-live="polite">
          {messages.map((item) => (
            <div className="chatbot-message-group" key={item.id}>
              <p className={`chatbot-message ${item.sender}`}>{cleanChatText(item.text)}</p>
              {item.suggestions?.length > 0 && (
                <div className="chatbot-suggestions" aria-label="Sản phẩm gợi ý">
                  {item.suggestions.map((product) => (
                    <div className="chatbot-product-wrap" key={product.id}>
                      <Link className="chatbot-product" href={product.url || `/shop/${product.slug}`}>
                        <img src={resolveProductImage(product.image)} alt={product.image_alt || product.name} onError={useImageFallback} />
                        <span>
                          <strong>{product.name}</strong>
                          <small>{formatSuggestionPrice(product.price)}</small>
                          {product.match_reasons?.[0] && <em className="chatbot-product-match">{product.match_reasons[0]}</em>}
                          <em>{product.stock_quantity > 0 ? `Còn ${product.stock_quantity} sản phẩm` : "Tạm hết hàng"}</em>
                        </span>
                      </Link>
                      <div className="chatbot-product-actions">
                        <button
                          type="button"
                          className={`chatbot-compare-button ${comparisonSelection.some((selected) => selected.id === product.id) ? "selected" : ""}`}
                          onClick={() => selectForComparison(product)}
                          disabled={isSending}
                        >
                          {comparisonSelection.some((selected) => selected.id === product.id) ? "Đã chọn" : "So sánh"}
                        </button>
                        <AddToCartButton product={product} />
                      </div>
                    </div>
                  ))}
                </div>
              )}
              {item.orders?.length > 0 && (
                <div className="chatbot-orders" aria-label="Đơn hàng của bạn">
                  {item.orders.map((order) => (
                    <Link className="chatbot-order" href={order.url} key={order.id}>
                      <strong>#{order.code}</strong>
                      <span>{order.status} · {formatPrice(order.total_amount)}</span>
                    </Link>
                  ))}
                </div>
              )}
              {item.sources?.length > 0 && (
                <div className="chatbot-sources" aria-label="Nguồn thông tin">
                  {item.sources.map((source) => (
                    <span className="chatbot-source" key={source.id}>Nguồn: {source.title}</span>
                  ))}
                </div>
              )}
            </div>
          ))}
          {isSending && <p className="chatbot-message bot chatbot-typing">PetWorld đang trả lời…</p>}
          <div ref={messagesEndRef} />
        </div>

        <div className="chatbot-quick-list" aria-label="Câu hỏi gợi ý">
          {QUICK_QUESTIONS.map((question) => (
            <button type="button" key={question} onClick={() => sendMessage(question)} disabled={isSending}>{question}</button>
          ))}
        </div>

        <form className="chatbot-form" onSubmit={handleSubmit}>
          <label htmlFor="chatbot-message" className="sr-only">Nhập nội dung cần hỗ trợ</label>
          <input ref={inputRef} id="chatbot-message" type="text" value={message} onChange={(event) => setMessage(event.target.value)} placeholder="Nhập câu hỏi..." autoComplete="off" maxLength="1000" disabled={isSending} />
          <button type="submit" disabled={!message.trim() || isSending} aria-label="Gửi tin nhắn">Gửi</button>
        </form>
      </section>

      <button type="button" className="chatbot-toggle" onClick={() => setIsOpen((current) => !current)} aria-expanded={isOpen} aria-controls="petworld-chatbot-panel" aria-label={isOpen ? "Đóng hộp chat" : "Mở hộp chat hỗ trợ"}>
        <span className="chatbot-bubble-icon" aria-hidden="true" />
        {!isOpen && <span className="chatbot-notification" aria-hidden="true">1</span>}
      </button>
    </div>
  );
}
