const CONTACT_PROMPT_AFTER_USER_MESSAGES = 2;

const createMessage = (type, html) => {
    const article = document.createElement("article");
    article.className = `sales-chatbot__message sales-chatbot__message--${type}`;
    article.innerHTML = html;

    return article;
};

const escapeHtml = (value = "") =>
    value
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#39;");

const decodeEntities = (value = "") => {
    const textarea = document.createElement("textarea");
    textarea.innerHTML = value;

    return textarea.value;
};

const looksCorruptedText = (value = "") =>
    /(?:Ã.|Â.|Ä.|Æ.|â€¦|â€œ|â€|há»|giáº|liÃªn|trá»|khÃ³a|má»|Ã¡|Æ°)/u.test(value);

const repairMojibake = (value = "") => {
    if (!value || !looksCorruptedText(value)) {
        return value;
    }

    let repaired = value;

    for (let index = 0; index < 3; index += 1) {
        try {
            const nextValue = decodeURIComponent(escape(repaired));

            if (!nextValue || nextValue === repaired) {
                break;
            }

            repaired = nextValue;

            if (!looksCorruptedText(repaired)) {
                return repaired;
            }
        } catch (error) {
            break;
        }
    }

    return repaired;
};

const normalizeDisplayText = (value = "") => repairMojibake(decodeEntities(value));

const linkifyText = (value = "") =>
    escapeHtml(normalizeDisplayText(value))
        .replace(
            /(https?:\/\/[^\s<]+)/g,
            '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>'
        )
        .replaceAll("\n", "<br>");

const buildFallbackSummary = ({ name = "", teacher = "", duration = "", categories = [] }) => {
    const parts = [];

    if (name) {
        parts.push(`Khóa học ${name} đang có thông tin công khai trên website.`);
    }

    if (Array.isArray(categories) && categories.length) {
        parts.push(`Danh mục: ${categories.slice(0, 2).join(", ")}.`);
    }

    if (teacher) {
        parts.push(`Giảng viên: ${teacher}.`);
    }

    if (duration) {
        parts.push(`Thời lượng: ${duration}.`);
    }

    return parts.join(" ").trim();
};

const buildLeadPrompt = (hasProfile) => `
    <div class="sales-chatbot__lead-box">
        <strong>${hasProfile ? "Mình có thể gửi yêu cầu liên hệ cho quản lý ngay." : "Bạn cần mình nhờ quản lý liên hệ lại không?"}</strong>
        <p>${hasProfile ? "Tài khoản của bạn đã có sẵn thông tin liên hệ. Bạn chỉ cần xác nhận để mình gửi ngay cho quản lý, hoặc bỏ qua nếu chưa cần." : "Bạn có thể để lại tên, số điện thoại, email và nội dung cần hỗ trợ. Nếu chưa cần, bạn có thể bỏ qua."}</p>
        <div class="sales-chatbot__lead-actions">
            <button type="button" class="sales-chatbot__lead-btn" data-chatbot-lead-action="open">${hasProfile ? "Gửi cho quản lý" : "Để lại liên hệ"}</button>
            <button type="button" class="sales-chatbot__lead-btn sales-chatbot__lead-btn--ghost" data-chatbot-lead-action="skip">Bỏ qua</button>
        </div>
    </div>
`;

const buildLeadForm = () => `
    <div class="sales-chatbot__lead-box sales-chatbot__lead-box--form-wrap">
        <strong>Để lại thông tin liên hệ</strong>
        <form class="sales-chatbot__lead-form">
            <input class="sales-chatbot__lead-input" name="name" maxlength="225" placeholder="Họ tên" required>
            <input class="sales-chatbot__lead-input" name="phone" maxlength="20" placeholder="Số điện thoại" required>
            <input class="sales-chatbot__lead-input" name="email" maxlength="225" placeholder="Email (không bắt buộc)">
            <textarea class="sales-chatbot__lead-input sales-chatbot__lead-input--textarea" name="message" maxlength="500" placeholder="Bạn cần hỗ trợ gì?"></textarea>
            <p class="sales-chatbot__lead-error" hidden></p>
            <div class="sales-chatbot__lead-actions">
                <button type="submit" class="sales-chatbot__lead-btn">Gửi liên hệ</button>
                <button type="button" class="sales-chatbot__lead-btn sales-chatbot__lead-btn--ghost" data-chatbot-lead-action="skip">Bỏ qua</button>
            </div>
        </form>
    </div>
`;

const formatBotAnswer = (payload) => {
    const blocks = [];

    if (payload.answer) {
        blocks.push(`<div class="sales-chatbot__bubble sales-chatbot__bubble--bot">${linkifyText(payload.answer)}</div>`);
    }

    if (Array.isArray(payload.courses) && payload.courses.length) {
        const items = payload.courses
            .map((course) => {
                const courseName = normalizeDisplayText(course.name || "");
                const teacherName = normalizeDisplayText(course.teacher || "");
                const durationText = normalizeDisplayText(course.duration || "");
                const categoryNames = Array.isArray(course.categories)
                    ? course.categories.map((category) => normalizeDisplayText(category || ""))
                    : [];
                const rawSummaryText = normalizeDisplayText(course.summary || "");
                const summaryText = rawSummaryText && !looksCorruptedText(rawSummaryText)
                    ? rawSummaryText
                    : buildFallbackSummary({
                          name: courseName,
                          teacher: teacherName,
                          duration: durationText,
                          categories: categoryNames,
                      });

                const price = course.sale_price
                    ? `<span class="sales-chatbot__meta-price"><s>${escapeHtml(normalizeDisplayText(course.price || ""))}</s> ${escapeHtml(normalizeDisplayText(course.sale_price || ""))}</span>`
                    : `<span class="sales-chatbot__meta-price">${escapeHtml(normalizeDisplayText(course.price || ""))}</span>`;

                const teacher = teacherName ? `<span>Giảng viên: ${escapeHtml(teacherName)}</span>` : "";
                const summary = summaryText ? `<p>${escapeHtml(summaryText)}</p>` : "";
                const categories = categoryNames.length
                    ? `<div class="sales-chatbot__card-tags">${categoryNames.map((category) => `<span>${escapeHtml(category)}</span>`).join("")}</div>`
                    : "";

                return `
                    <a class="sales-chatbot__card" href="${escapeHtml(course.url || "")}">
                        ${categories}
                        <strong>${escapeHtml(courseName)}</strong>
                        <div class="sales-chatbot__card-meta">
                            ${price}
                            <span>${escapeHtml(durationText)}</span>
                            <span>${escapeHtml(String(course.students_count || 0))} học viên</span>
                            ${teacher}
                        </div>
                        ${summary}
                        <span class="sales-chatbot__card-link">Xem chi tiết <i class="fa-solid fa-arrow-right"></i></span>
                    </a>
                `;
            })
            .join("");

        blocks.push(`<div class="sales-chatbot__cards">${items}</div>`);
    }

    if (Array.isArray(payload.coupons) && payload.coupons.length) {
        const items = payload.coupons
            .map((coupon) => {
                const appliesTo = coupon.applies_to_all
                    ? "Áp dụng: nhiều khóa học phù hợp"
                    : `Áp dụng: ${(coupon.course_names || []).map((name) => escapeHtml(normalizeDisplayText(name))).join(", ")}`;

                const totalCondition = coupon.total_condition
                    ? `<span>Đơn tối thiểu: ${escapeHtml(normalizeDisplayText(coupon.total_condition))}</span>`
                    : "";

                const endDate = coupon.end_date
                    ? `<span>Hết hạn: ${escapeHtml(normalizeDisplayText(coupon.end_date))}</span>`
                    : "<span>Không giới hạn ngày kết thúc</span>";

                return `
                    <div class="sales-chatbot__coupon">
                        <strong>${escapeHtml(normalizeDisplayText(coupon.code || ""))}</strong>
                        <div class="sales-chatbot__coupon-meta">
                            <span>Giảm: ${escapeHtml(normalizeDisplayText(coupon.discount_text || ""))}</span>
                            ${totalCondition}
                            ${endDate}
                        </div>
                        <p>${appliesTo}</p>
                    </div>
                `;
            })
            .join("");

        blocks.push(`<div class="sales-chatbot__coupons">${items}</div>`);
    }

    return blocks.join("");
};

document.querySelectorAll("[data-sales-chatbot]").forEach((root) => {
    const config = JSON.parse(root.dataset.salesChatbot || "{}");
    const toggle = root.querySelector(".sales-chatbot__toggle");
    const panel = root.querySelector(".sales-chatbot__panel");
    const closeButton = root.querySelector(".sales-chatbot__close");
    const hero = root.querySelector(".sales-chatbot__hero");
    const messages = root.querySelector(".sales-chatbot__messages");
    const starters = root.querySelector(".sales-chatbot__starters");
    const form = root.querySelector(".sales-chatbot__form");
    const input = root.querySelector(".sales-chatbot__input");
    const starterButtons = root.querySelectorAll(".sales-chatbot__starter");
    const ttlMs = Math.max(1, Number(config.messageTtlMinutes || 10)) * 60 * 1000;
    const panelStateKey = `${config.storageKey}:panel-open`;
    const leadStateKey = `${config.storageKey}:lead-state`;
    const transitionValue = window.getComputedStyle(root).getPropertyValue("--sales-chatbot-transition-ms").trim();
    const closeAnimationMs = Number.parseInt(transitionValue, 10) || 220;
    let isLoading = false;
    let historyExpiryTimer = null;
    let closeTimer = null;

    const student = config.student || null;
    const hasSavedStudentLead = Boolean(student?.name && student?.phone);

    const scrollToBottom = () => {
        messages.scrollTop = messages.scrollHeight;
    };

    const getLeadState = () => localStorage.getItem(leadStateKey) || "pending";
    const setLeadState = (value) => localStorage.setItem(leadStateKey, value);
    const countUserMessages = () => messages.querySelectorAll(".sales-chatbot__message--user").length;

    const clearHistory = () => {
        localStorage.removeItem(config.storageKey);
        if (historyExpiryTimer) {
            window.clearTimeout(historyExpiryTimer);
            historyExpiryTimer = null;
        }
    };

    const persistPanelState = (isOpen) => {
        if (isOpen) {
            sessionStorage.setItem(panelStateKey, "open");
            return;
        }

        sessionStorage.removeItem(panelStateKey);
    };

    const scheduleHistoryExpiry = (expiresAt) => {
        if (historyExpiryTimer) {
            window.clearTimeout(historyExpiryTimer);
        }

        const remainingMs = expiresAt - Date.now();

        if (remainingMs <= 0) {
            clearHistory();
            messages.innerHTML = "";
            return false;
        }

        historyExpiryTimer = window.setTimeout(() => {
            clearHistory();
            messages.innerHTML = "";
            updateHeroVisibility();
            updateStarterVisibility();
            restoreHistory();
        }, remainingMs);

        return true;
    };

    const saveHistory = () => {
        const expiresAt = Date.now() + ttlMs;
        localStorage.setItem(config.storageKey, JSON.stringify({ html: messages.innerHTML, expiresAt }));
        scheduleHistoryExpiry(expiresAt);
    };

    const updateStarterVisibility = () => {
        if (!starters) {
            return;
        }

        const hasUserMessage = messages.querySelector(".sales-chatbot__message--user");
        const totalMessages = messages.querySelectorAll(".sales-chatbot__message").length;
        const shouldHide = Boolean(hasUserMessage) || totalMessages > 1;
        starters.classList.toggle("is-hidden", shouldHide);
    };

    const updateHeroVisibility = () => {
        if (!hero) {
            return;
        }

        const totalMessages = messages.querySelectorAll(".sales-chatbot__message").length;
        hero.classList.toggle("is-hidden", totalMessages > 1);
    };

    const pushMessage = (type, html, persist = true) => {
        messages.append(createMessage(type, html));
        updateHeroVisibility();
        updateStarterVisibility();
        scrollToBottom();

        if (persist) {
            saveHistory();
        }
    };

    const submitLeadPayload = async (payload) => {
        const response = await fetch(config.leadEndpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                "X-CSRF-TOKEN": config.csrfToken,
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify(payload),
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || "Không thể lưu thông tin liên hệ lúc này.");
        }

        return data;
    };

    const renderLeadPromptIfNeeded = () => {
        if (getLeadState() !== "pending") {
            return;
        }

        if (countUserMessages() < CONTACT_PROMPT_AFTER_USER_MESSAGES) {
            return;
        }

        if (messages.querySelector("[data-chatbot-lead-action], .sales-chatbot__lead-form")) {
            return;
        }

        pushMessage("bot", buildLeadPrompt(hasSavedStudentLead));
        setLeadState("prompted");
    };

    const restoreHistory = () => {
        const history = localStorage.getItem(config.storageKey);

        if (history) {
            try {
                const parsedHistory = JSON.parse(history);

                if (parsedHistory && typeof parsedHistory.html === "string" && typeof parsedHistory.expiresAt === "number" && scheduleHistoryExpiry(parsedHistory.expiresAt)) {
                    messages.innerHTML = parsedHistory.html;
                    updateHeroVisibility();
                    updateStarterVisibility();
                    scrollToBottom();
                    return;
                }
            } catch (error) {
                clearHistory();
            }
        }

        updateHeroVisibility();
        updateStarterVisibility();
    };

    const setExpandedState = (expanded) => {
        toggle.setAttribute("aria-expanded", String(expanded));
        toggle.setAttribute("aria-label", normalizeDisplayText(expanded ? config.closeLabel : config.openLabel));
        panel.setAttribute("aria-hidden", String(!expanded));
    };

    const applyPanelState = (state) => {
        const isOpen = state === "open";
        const isClosing = state === "closing";
        root.classList.toggle("is-open", isOpen);
        root.classList.toggle("is-closing", isClosing);
        setExpandedState(isOpen || isClosing);
    };

    const isPanelVisible = () => root.classList.contains("is-open") || root.classList.contains("is-closing");

    const openPanel = () => {
        if (closeTimer) {
            window.clearTimeout(closeTimer);
            closeTimer = null;
        }

        panel.hidden = false;
        requestAnimationFrame(() => {
            applyPanelState("open");
            persistPanelState(true);
            input.focus();
            scrollToBottom();
        });
    };

    const closePanel = () => {
        if (!isPanelVisible()) {
            return;
        }

        if (closeTimer) {
            window.clearTimeout(closeTimer);
        }

        applyPanelState("closing");
        persistPanelState(false);
        closeTimer = window.setTimeout(() => {
            closeTimer = null;
            if (!root.classList.contains("is-open")) {
                applyPanelState("closed");
                panel.hidden = true;
            }
        }, closeAnimationMs);
    };

    const togglePanel = () => {
        if (isPanelVisible()) {
            closePanel();
            return;
        }

        openPanel();
    };

    const openLeadForm = async () => {
        const existingPrompt = messages.querySelector("[data-chatbot-lead-action]")?.closest(".sales-chatbot__message");
        if (existingPrompt) {
            existingPrompt.remove();
        }

        if (hasSavedStudentLead) {
            try {
                const data = await submitLeadPayload({
                    name: student.name,
                    phone: student.phone,
                    email: student.email || "",
                    message: "Người dùng đăng nhập đã xác nhận cần được quản lý liên hệ từ chatbot.",
                });
                pushMessage("bot", `<div class="sales-chatbot__bubble sales-chatbot__bubble--bot">${linkifyText(data.message || "Đã gửi yêu cầu liên hệ cho quản lý.")}</div>`);
                setLeadState("submitted");
            } catch (error) {
                pushMessage("bot", `<div class="sales-chatbot__bubble sales-chatbot__bubble--bot">${linkifyText(error.message)}</div>`);
                setLeadState("pending");
            }
            return;
        }

        if (!messages.querySelector(".sales-chatbot__lead-form")) {
            pushMessage("bot", buildLeadForm());
        }

        setLeadState("form-open");
        saveHistory();
    };

    const skipLeadPrompt = () => {
        messages.querySelectorAll("[data-chatbot-lead-action]").forEach((button) => {
            button.closest(".sales-chatbot__message")?.remove();
        });
        setLeadState("skipped");
        pushMessage(
            "bot",
            '<div class="sales-chatbot__bubble sales-chatbot__bubble--bot">Khi nào cần hỗ trợ thêm cứ nhắn mình nhé.</div>'
        );
    };

    const submitLeadForm = async (formEl) => {
        const errorEl = formEl.querySelector(".sales-chatbot__lead-error");
        const submitButton = formEl.querySelector('button[type="submit"]');
        const payload = {
            name: formEl.elements.name.value.trim(),
            phone: formEl.elements.phone.value.trim(),
            email: formEl.elements.email.value.trim(),
            message: formEl.elements.message.value.trim(),
        };

        if (!payload.name || !payload.phone) {
            errorEl.hidden = false;
            errorEl.textContent = "Vui lòng nhập họ tên và số điện thoại.";
            return;
        }

        submitButton.disabled = true;
        errorEl.hidden = true;
        errorEl.textContent = "";

        try {
            const data = await submitLeadPayload(payload);
            const wrapper = formEl.closest(".sales-chatbot__message");
            wrapper.innerHTML = `<div class="sales-chatbot__bubble sales-chatbot__bubble--bot">${linkifyText(data.message || "Đã lưu thông tin liên hệ.")}</div>`;
            setLeadState("submitted");
            saveHistory();
            scrollToBottom();
        } catch (error) {
            submitButton.disabled = false;
            errorEl.hidden = false;
            errorEl.textContent = error.message;
        }
    };

    const sendMessage = async (message) => {
        const trimmed = message.trim();

        if (!trimmed || isLoading) {
            return;
        }

        isLoading = true;
        pushMessage("user", `<div class="sales-chatbot__bubble sales-chatbot__bubble--user">${escapeHtml(trimmed)}</div>`);

        const loadingNode = createMessage("bot", `<div class="sales-chatbot__bubble sales-chatbot__bubble--bot sales-chatbot__typing">${escapeHtml(normalizeDisplayText(config.typing || ""))}</div>`);
        messages.append(loadingNode);
        scrollToBottom();

        try {
            const response = await fetch(config.endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": config.csrfToken,
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: JSON.stringify({ message: trimmed }),
            });

            const payload = await response.json();
            loadingNode.remove();

            if (!response.ok) {
                throw new Error(payload.message || "Không thể gửi câu hỏi lúc này.");
            }

            pushMessage("bot", formatBotAnswer(payload));
            renderLeadPromptIfNeeded();
        } catch (error) {
            loadingNode.remove();
            pushMessage("bot", `<div class="sales-chatbot__bubble sales-chatbot__bubble--bot">Mình chưa phản hồi được lúc này. Bạn thử lại sau hoặc mở <a href="${escapeHtml(config.supportUrl)}" target="_blank" rel="noopener noreferrer">trang hỗ trợ</a> nhé.</div>`);
            renderLeadPromptIfNeeded();
        } finally {
            isLoading = false;
        }
    };

    toggle.addEventListener("click", togglePanel);
    closeButton?.addEventListener("click", closePanel);

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && isPanelVisible()) {
            closePanel();
        }
    });

    document.addEventListener("click", (event) => {
        if (!isPanelVisible()) {
            return;
        }

        if (event.target.closest("[data-theme-toggle]")) {
            return;
        }

        const eventPath = typeof event.composedPath === "function" ? event.composedPath() : [];
        if (eventPath.includes(root)) {
            return;
        }

        if (!root.contains(event.target)) {
            closePanel();
        }
    });

    form.addEventListener("submit", (event) => {
        event.preventDefault();
        const message = input.value;
        input.value = "";
        input.style.height = "";
        sendMessage(message);
    });

    input.addEventListener("input", () => {
        input.style.height = "auto";
        input.style.height = `${Math.min(input.scrollHeight, 140)}px`;
    });

    input.addEventListener("keydown", (event) => {
        if (event.key === "Enter" && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    messages.addEventListener("click", (event) => {
        const chip = event.target.closest(".sales-chatbot__chip");
        if (chip) {
            event.stopPropagation();
            sendMessage(chip.textContent || "");
            return;
        }

        const leadActionButton = event.target.closest("[data-chatbot-lead-action]");
        if (!leadActionButton) {
            return;
        }

        event.stopPropagation();

        if (leadActionButton.dataset.chatbotLeadAction === "open") {
            openLeadForm();
            return;
        }

        skipLeadPrompt();
    });

    messages.addEventListener("submit", (event) => {
        const leadForm = event.target.closest(".sales-chatbot__lead-form");
        if (!leadForm) {
            return;
        }

        event.preventDefault();
        submitLeadForm(leadForm);
    });

    starterButtons.forEach((button) => {
        button.addEventListener("click", () => {
            openPanel();
            sendMessage(button.textContent || "");
        });
    });

    restoreHistory();
    applyPanelState("closed");
    if (sessionStorage.getItem(panelStateKey) === "open") {
        openPanel();
    }
    updateHeroVisibility();
    updateStarterVisibility();
});
