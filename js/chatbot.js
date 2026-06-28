// Chatbot functionality
let conversationHistory = [];

// DOM elements
const chatMessages = document.getElementById("chatMessages");
const chatForm = document.getElementById("chatForm");
const userMessageInput = document.getElementById("userMessage");
const sendBtn = document.getElementById("sendBtn");
const sendBtnText = document.getElementById("sendBtnText");
const sendBtnLoader = document.getElementById("sendBtnLoader");
const clearBtn = document.getElementById("clearBtn");

// Initialize
document.addEventListener("DOMContentLoaded", function () {
  // Load chat history from localStorage
  loadChatHistory();

  // Form submit handler
  chatForm.addEventListener("submit", handleSubmit);

  // Clear chat handler
  clearBtn.addEventListener("click", clearChat);

  // Auto-resize textarea
  userMessageInput.addEventListener("input", function () {
    this.style.height = "auto";
    this.style.height = this.scrollHeight + "px";
  });

  // Enter to send, Shift+Enter for new line
  userMessageInput.addEventListener("keydown", function (e) {
    if (e.key === "Enter" && !e.shiftKey) {
      e.preventDefault();
      chatForm.dispatchEvent(new Event("submit"));
    }
  });
});

// Handle form submission
async function handleSubmit(e) {
  e.preventDefault();

  const message = userMessageInput.value.trim();
  if (!message) return;

  // Add user message to chat
  addMessage(message, "user");

  // Clear input
  userMessageInput.value = "";
  userMessageInput.style.height = "auto";

  // Disable send button
  setSendButtonState(true);

  // Show typing indicator
  showTypingIndicator();

  try {
    // Send to API
    const response = await fetch("api/chatbot_api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        message: message,
        history: conversationHistory,
      }),
    });

    const data = await response.json();

    // Remove typing indicator
    removeTypingIndicator();

    if (data.success) {
      // Add bot response
      addMessage(data.response, "bot");

      // Update conversation history
      conversationHistory.push({
        user: message,
        bot: data.response,
      });

      // Save to localStorage
      saveChatHistory();
    } else {
      // Show error message
      addMessage(
        `Sorry, I encountered an error: ${data.error}\n\n${
          data.suggestion || "Please try again."
        }`,
        "bot",
        true
      );
    }
  } catch (error) {
    removeTypingIndicator();
    addMessage(
      "Sorry, I couldn't connect to the AI assistant. Please make sure Ollama is running and try again.",
      "bot",
      true
    );
    console.error("Chat error:", error);
  } finally {
    setSendButtonState(false);
  }
}

// Add message to chat
function addMessage(text, sender, isError = false) {
  const messageDiv = document.createElement("div");
  messageDiv.className = `message ${sender}-message ${
    isError ? "error-message" : ""
  }`;

  const avatar = document.createElement("div");
  avatar.className = "message-avatar";
  avatar.textContent = sender === "user" ? "👤" : "🤖";

  const content = document.createElement("div");
  content.className = "message-content";

  // Format the message (convert newlines to paragraphs)
  const paragraphs = text.split("\n").filter((p) => p.trim());
  paragraphs.forEach((paragraph) => {
    const p = document.createElement("p");
    p.textContent = paragraph;
    content.appendChild(p);
  });

  messageDiv.appendChild(avatar);
  messageDiv.appendChild(content);

  chatMessages.appendChild(messageDiv);

  // Scroll to bottom
  scrollToBottom();
}

// Show typing indicator
function showTypingIndicator() {
  const typingDiv = document.createElement("div");
  typingDiv.className = "message bot-message typing-indicator";
  typingDiv.id = "typingIndicator";

  const avatar = document.createElement("div");
  avatar.className = "message-avatar";
  avatar.textContent = "🤖";

  const content = document.createElement("div");
  content.className = "message-content";
  content.innerHTML =
    '<div class="typing-dots"><span></span><span></span><span></span></div>';

  typingDiv.appendChild(avatar);
  typingDiv.appendChild(content);

  chatMessages.appendChild(typingDiv);
  scrollToBottom();
}

// Remove typing indicator
function removeTypingIndicator() {
  const indicator = document.getElementById("typingIndicator");
  if (indicator) {
    indicator.remove();
  }
}

// Set send button state
function setSendButtonState(disabled) {
  sendBtn.disabled = disabled;
  if (disabled) {
    sendBtnText.style.display = "none";
    sendBtnLoader.style.display = "inline";
  } else {
    sendBtnText.style.display = "inline";
    sendBtnLoader.style.display = "none";
  }
}

// Scroll to bottom
function scrollToBottom() {
  chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Clear chat
function clearChat() {
  if (confirm("Are you sure you want to clear the chat history?")) {
    // Keep only the welcome message
    const welcomeMessage = chatMessages.querySelector(".bot-message");
    chatMessages.innerHTML = "";
    if (welcomeMessage) {
      chatMessages.appendChild(welcomeMessage);
    }

    // Clear history
    conversationHistory = [];
    localStorage.removeItem("chatHistory");
  }
}

// Send suggestion
function sendSuggestion(text) {
  userMessageInput.value = text;
  userMessageInput.focus();
  chatForm.dispatchEvent(new Event("submit"));
}

// Save chat history to localStorage
function saveChatHistory() {
  try {
    localStorage.setItem("chatHistory", JSON.stringify(conversationHistory));
  } catch (e) {
    console.error("Failed to save chat history:", e);
  }
}

// Load chat history from localStorage
function loadChatHistory() {
  try {
    const saved = localStorage.getItem("chatHistory");
    if (saved) {
      conversationHistory = JSON.parse(saved);

      // Restore messages to UI
      conversationHistory.forEach((item) => {
        addMessage(item.user, "user");
        addMessage(item.bot, "bot");
      });
    }
  } catch (e) {
    console.error("Failed to load chat history:", e);
  }
}

// Auto-scroll on new messages
const observer = new MutationObserver(scrollToBottom);
observer.observe(chatMessages, { childList: true });
