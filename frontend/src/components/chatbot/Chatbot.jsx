"use client";

import { useEffect, useRef, useState } from "react";

const QUICK_QUESTIONS = [
  "Tư vấn thức ăn",
  "Kiểm tra đơn hàng",
  "Chính sách đổi trả",
];

export default function Chatbot() {
  const [isOpen, setIsOpen] = useState(false);
  const [message, setMessage] = useState("");
  const [messages, setMessages] = useState([
    {
      id: "welcome",
      sender: "bot",
      text: "Xin chào! PetWorld có thể giúp gì cho bạn hôm nay?",
    },
  ]);
  const inputRef = useRef(null);

  useEffect(() => {
    if (isOpen) inputRef.current?.focus();

    // Cho phép đóng nhanh hộp chat bằng phím Escape.
    const handleEscape = (event) => {
      if (event.key === "Escape") setIsOpen(false);
    };

    document.addEventListener("keydown", handleEscape);
    return () => document.removeEventListener("keydown", handleEscape);
  }, [isOpen]);

  const sendMessage = (text) => {
    const content = text.trim();
    if (!content) return;

    // Đây là phản hồi tạm thời ở frontend; có thể thay bằng API chatbot sau này.
    setMessages((current) => [
      ...current,
      { id: `user-${Date.now()}`, sender: "user", text: content },
      {
        id: `bot-${Date.now()}`,
        sender: "bot",
        text: "Mình đã ghi nhận câu hỏi. Nhân viên PetWorld sẽ hỗ trợ bạn sớm nhất nhé!",
      },
    ]);
    setMessage("");
  };

  const handleSubmit = (event) => {
    event.preventDefault();
    sendMessage(message);
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
          <button
            type="button"
            className="chatbot-close"
            onClick={() => setIsOpen(false)}
            aria-label="Đóng hộp chat"
          >
            <span aria-hidden="true" />
          </button>
        </header>

        <div className="chatbot-messages" aria-live="polite">
          {messages.map((item) => (
            <p className={`chatbot-message ${item.sender}`} key={item.id}>
              {item.text}
            </p>
          ))}
        </div>

        <div className="chatbot-quick-list" aria-label="Câu hỏi gợi ý">
          {QUICK_QUESTIONS.map((question) => (
            <button type="button" key={question} onClick={() => sendMessage(question)}>
              {question}
            </button>
          ))}
        </div>

        <form className="chatbot-form" onSubmit={handleSubmit}>
          <label htmlFor="chatbot-message" className="sr-only">Nhập nội dung cần hỗ trợ</label>
          <input
            ref={inputRef}
            id="chatbot-message"
            type="text"
            value={message}
            onChange={(event) => setMessage(event.target.value)}
            placeholder="Nhập câu hỏi..."
            autoComplete="off"
          />
          <button type="submit" disabled={!message.trim()} aria-label="Gửi tin nhắn">
            Gửi
          </button>
        </form>
      </section>

      <button
        type="button"
        className="chatbot-toggle"
        onClick={() => setIsOpen((current) => !current)}
        aria-expanded={isOpen}
        aria-controls="petworld-chatbot-panel"
        aria-label={isOpen ? "Đóng hộp chat" : "Mở hộp chat hỗ trợ"}
      >
        <span className="chatbot-bubble-icon" aria-hidden="true" />
        {!isOpen && <span className="chatbot-notification" aria-hidden="true">1</span>}
      </button>
    </div>
  );
}
